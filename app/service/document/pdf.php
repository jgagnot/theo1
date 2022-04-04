<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 26/09/2018
 * Time: 17:56
 */

namespace service\document;

use core\imi;

require('lib/fpdf/fpdf.php');
require('lib/fpdf/makefont/makefont.php');
define('EURO', utf8_encode(chr(128)));

class pdf extends \FPDF
{
    private $colonnes;
    private $format;

    public function makefont()
    {
        MakeFont('/Users/jgagnot/Downloads/Blinker/Blinker-Black.ttf', 'cp1252');
    }

    public function Footer()
    {
        $this->AliasNbPages();
        $this->SetY(-10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' / {nb}', 0, 0, 'C');
    }

    public function createInvoice($f3, $orderId)
    {
        $imi = new imi();

        $order = $imi->fetchOneById('shoppingOrder', intval($orderId));
        $user = $imi->fetchOneById('user', intval($order['userId']));
        $userAddress = $imi->fetchOneById('userAdress', intval($order['userAdressId']));
        $payment = $imi->fetchOneByKeysEqual('payment', array('userId' => intval($user['id']), 'paymentOriginId' => intval($orderId)));

        $orderLines = $imi->fetchAllByKeysEqual('shoppingOrderLine', array('orderId' => intval($orderId)));

        if ($this->createOrderPdf($f3, $order, $orderLines, $user, $userAddress, $payment) === true) {
            $this->addOrderVat($f3, $orderLines);
        }

        $this->Output();
    }

    public function createPurchase($f3, $orderId)
    {
        $imi = new imi();

        $order = $imi->fetchOneById('shoppingOrder', intval($orderId));
        $user = $imi->fetchOneById('user', intval($order['userId']));
        $userAddress = $imi->fetchOneById('userAdress', intval($order['userAdressId']));
        $payment = $imi->fetchOneByKeysEqual('payment', array('userId' => intval($user['id']), 'paymentOriginId' => intval($orderId)));
        $orderLines = $imi->fetchAllByKeysEqual('shoppingOrderLine', array('orderId' => intval($orderId)));

        if ($this->createPurchasePdf($f3, $order, $orderLines, $user, $userAddress, $payment) === true) {
            $this->addOrderVat($f3, $orderLines);
        }

        $this->Output();
    }

    private function createOrderPdf($f3, $order, $orderLines, $user, $userAddress, $payment, $offset = 0)
    {
        $imi = new imi();


        $this->AddPage();

        $this->addCompany($f3);
        $this->addUser($f3, $user);
        $this->addAddress($f3, $userAddress, 130, $this->GetY() + 8);
        $this->addOrderInfo($f3, $payment, $order);

        $this->SetFont('Arial', '', 10);
        $cols = array("RÉFÉRENCE" => 53,
            "DÉSIGNATION" => 43,
            "QUANTITÉ" => 22,
            "P.U. HT" => 26,
            "MONTANT HT" => 30,
            "TVA" => 16);
        $this->addCols($cols);
        $cols = array("RÉFÉRÉNCE" => "L",
            "DÉSIGNATION" => "L",
            "QUANTITÉ" => "C",
            "P.U. HT" => "R",
            "MONTANT HT" => "R",
            "TVA" => "C");
        $this->addLineFormat($cols);
        $this->addLineFormat($cols);

        $size = 109;
        $orderSize = 0;

        for ($key = 0; $offset < count($orderLines); $key++) {
            $reference = $imi->fetchOneById('shoppingReferencePerItem', intval($orderLines[$offset]['referenceId']));
            $price = $imi->fetchOneById('shoppingPricePerReference', intval($orderLines[$offset]['priceId']));

            $line = array("RÉFÉRENCE" => utf8_decode($reference['reference']),
                "DÉSIGNATION" => utf8_decode($reference['name']),
                "QUANTITÉ" => utf8_decode($orderLines[$offset]['quantity']),
                "P.U. HT" => utf8_decode(number_format(($price['discountPrice'] / (1 + $price['vat'] / 100) / 100), 2, ',', ' ') . ' ' . EURO),
                "MONTANT HT" => utf8_decode(number_format($orderLines[$offset]['quantity'] * ($price['discountPrice'] / (1 + $price['vat'] / 100) / 100), 2, ',', ' ') . ' ' . EURO),
                "TVA" => utf8_decode($price['vat'] . '%'));
            $lineNumber = $this->getLineNumber($f3, $line);
            $orderSize += ($lineNumber * 4) + 2;

            if ($orderSize > ($this->h - 50 - 100 - 4)) {
                return $this->createOrderPdf($f3, $order, $orderLines, $user, $userAddress, $payment, $offset);
            } else {
                $size += $this->addLine($size, $line);
                $size += 2;
                $offset++;
            }
        }
        return true;
    }

    private function createPurchasePdf($f3, $order, $orderLines, $user, $userAddress, $payment, $offset = 0)
    {
        $imi = new imi();


        $this->AddPage();
        $this->addCompany($f3);
        $this->addUser($f3, $user);
        $this->addAddress($f3, $userAddress, 130, $this->GetY() + 8);
        $this->addPurchaseInfo($f3, $order);

        $this->SetFont('Arial', '', 10);
        $cols = array("RÉFÉRENCE" => 53,
            "DÉSIGNATION" => 43,
            "QUANTITÉ" => 22,
            "P.U. HT" => 26,
            "MONTANT HT" => 30,
            "TVA" => 16);
        $this->addCols($cols);
        $cols = array("RÉFÉRÉNCE" => "L",
            "DÉSIGNATION" => "L",
            "QUANTITÉ" => "C",
            "P.U. HT" => "R",
            "MONTANT HT" => "R",
            "TVA" => "C");
        $this->addLineFormat($cols);
        $this->addLineFormat($cols);

        $size = 109;
        $orderSize = 0;

        for ($key = 0; $offset < count($orderLines); $key++) {
            $reference = $imi->fetchOneById('shoppingReferencePerItem', intval($orderLines[$offset]['referenceId']));
            $price = $imi->fetchOneById('shoppingPricePerReference', intval($orderLines[$offset]['priceId']));

            $line = array("RÉFÉRENCE" => utf8_decode($reference['reference']),
                "DÉSIGNATION" => utf8_decode($reference['name']),
                "QUANTITÉ" => utf8_decode($orderLines[$offset]['quantity']),
                "P.U. HT" => utf8_decode(number_format(($price['discountPrice'] / (1 + $price['vat'] / 100) / 100), 2, ',', ' ') . ' ' . EURO),
                "MONTANT HT" => utf8_decode(number_format($orderLines[$offset]['quantity'] * ($price['discountPrice'] / (1 + $price['vat'] / 100) / 100), 2, ',', ' ') . ' ' . EURO),
                "TVA" => utf8_decode($price['vat'] . '%'));
            $lineNumber = $this->getLineNumber($f3, $line);
            $orderSize += ($lineNumber * 4) + 2;

            if ($orderSize > ($this->h - 50 - 100 - 4)) {
                return $this->createOrderPdf($f3, $order, $orderLines, $user, $userAddress, $payment, $offset);
            } else {
                $size += $this->addLine($size, $line);
                $size += 2;
                $offset++;
            }
        }
        return true;
    }

    private function addOrderVat($f3, $orderLines)
    {
        $imi = new imi();
        $vat_array = [];

        $this->initOrderVat();

        foreach ($orderLines as $key => $line) {
            $price = $imi->fetchOneById('shoppingPricePerReference', intval($line['priceId']));

            if (isset($vat_array[$price['vat']])) {
                $vat_array[$price['vat']]['base'] += $line['quantity'] * ($price['HTDiscount']);
                $vat_array[$price['vat']]['vat'] += $line['quantity'] * ($price['discountPrice'] - $price['HTDiscount']);
            } else {
                $vat_array[$price['vat']]['base'] = $line['quantity'] * ($price['HTDiscount']);
                $vat_array[$price['vat']]['vat'] = $line['quantity'] * ($price['discountPrice'] - $price['HTDiscount']);
            }

        }
        $this->SetFont('Arial', '', 8);

        $id = 1;
        $y = 261;
        $totalHt = 0;
        $totalVat = 0;

        foreach ($vat_array as $rate => $base) {
            $ht = floor($base['base']);
            $vat = floor($base['vat']);
            $totalVat += $vat;
            $totalHt += $ht;


            $this->SetXY($this->w - 120 - 10 + 1, $y);
            $this->Cell(5, 4, utf8_decode($id));
            $this->SetXY($this->w - 120 - 10 + 7, $y);
            $this->Cell(19, 4, utf8_decode(number_format($ht / 100, 2, ',', ' ') . ' ' . EURO), '', '', 'R');

            $this->SetXY($this->w - 120 - 10 + 64, $y);
            $this->Cell(10, 4, utf8_decode($rate . ' %'), '', '', '');
            $this->SetXY($this->w - 120 - 10 + 43, $y);
            $this->Cell(19, 4, utf8_decode(number_format($vat / 100, 2, ',', ' ') . ' ' . EURO), '', '', 'R');
            $y += 4;
            $id++;
        }

        $this->SetXY($this->w - 120 - 10 + 93 + 8, $y - 3);
        $this->Cell(17, 4, utf8_decode(number_format($totalHt / 100, 2, ',', ' ') . ' ' . EURO), '', '', 'R');
        $this->SetXY($this->w - 120 - 10 + 93 + 8, $y + 2);
        $this->Cell(17, 4, utf8_decode(number_format($totalVat / 100, 2, ',', ' ') . ' ' . EURO), '', '', 'R');
        $this->SetXY($this->w - 120 - 10 + 93 + 8, $y + 7);
        $this->Cell(17, 4, utf8_decode(number_format(($totalVat + $totalHt) / 100, 2, ',', ' ') . ' ' . EURO), '', '', 'R');


    }

    private function initOrderVat()
    {
        $this->SetFont("Arial", "B", 8);
        $r1 = $this->w - 120 - 10;
        $r2 = $r1 + 120;
        $y1 = $this->h - 40;
        $y2 = $y1 + 20;
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $y1 + 4, $r2, $y1 + 4);
        $this->Line($r1 + 5, $y1 + 4, $r1 + 5, $y2); // avant BASES HT
        $this->Line($r1 + 27, $y1, $r1 + 27, $y2);  // avant REMISE
        $this->Line($r1 + 43, $y1, $r1 + 43, $y2);  // avant MT TVA
        $this->Line($r1 + 63, $y1, $r1 + 63, $y2);  // avant % TVA
        $this->Line($r1 + 75, $y1, $r1 + 75, $y2);  // avant PORT
        $this->Line($r1 + 91, $y1, $r1 + 91, $y2);  // avant TOTAUX
        $this->SetXY($r1 + 9, $y1);
        $this->Cell(10, 4, "BASES HT");
        $this->SetX($r1 + 29);
        $this->Cell(10, 4, "REMISE");
        $this->SetX($r1 + 48);
        $this->Cell(10, 4, "MT TVA");
        $this->SetX($r1 + 63);
        $this->Cell(10, 4, "% TVA");
        $this->SetX($r1 + 78);
        $this->Cell(10, 4, "PORT");
        $this->SetX($r1 + 100);
        $this->Cell(10, 4, "TOTAUX");
        $this->SetFont("Arial", "B", 6);
        $this->SetXY($r1 + 93, $y2 - 13);
        $this->Cell(6, 0, "H.T.   :");
        $this->SetXY($r1 + 93, $y2 - 8);
        $this->Cell(6, 0, "T.V.A. :");
        $this->SetXY($r1 + 93, $y2 - 3);
        $this->Cell(6, 0, "TOTAL :");
    }

    private function addCompany($f3, $x1 = 10, $y1 = 33)
    {
        $companyName = utf8_decode($f3->get('companyName'));
        $companyAdress = utf8_decode($f3->get('companyAdress')) . PHP_EOL
            . utf8_decode($f3->get('companyZipcode')) . ' ' . utf8_decode($f3->get('companyCity')) . PHP_EOL .
            utf8_decode($f3->get('companyCountry')) . PHP_EOL .
            'R.C.S. ' . utf8_decode($f3->get('companyRCS')) . PHP_EOL .
            'Capital : ' . utf8_decode($f3->get('companyCapital') . ' ' . EURO);
        $this->Image('project/' . $f3->get('project') . '/' . $f3->get('companyLogo'), 10, 5, 70, 20);
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $length = $this->GetStringWidth($companyName);

        $this->Cell($length, 2, $companyName);
        $this->SetXY($x1, $y1 + 4);
        $this->SetFont('Arial', '', 10);

        $length = $this->GetStringWidth($companyAdress);

        $this->MultiCell($length, 4, $companyAdress);
    }

    private function addUser($f3, $user, $x1 = 130, $y1 = 33)
    {
        $text = utf8_decode(ucfirst($user['firstname'])) . ' ' . utf8_decode(ucfirst($user['lastname']));

        $this->SetFont('Arial', 'B', 14);
        $length = $this->GetStringWidth($text);
        $this->SetXY($x1, $y1);

        $this->Cell($length, 4, $text);
    }

    private function addAddress($f3, $address, $x1, $y1)
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($x1, $y1);

        $line = utf8_decode($address['line1']);
        if (strlen($address['line2']) > 0)
            $line .= ' ' . utf8_decode($address['line2']);
        $this->Cell($this->GetStringWidth($line), 2, $line);

        $this->SetXY($x1, $y1 + 4);
        $text = utf8_decode($address['zipcode'] . ' ' . strtoupper($address['city']) . PHP_EOL . strtoupper($address['country']));

        $this->MultiCell($this->GetStringWidth($text), 4, $text);
    }

    private function addOrderType($f3, $order)
    {
        $r1 = $this->w - 80;
        $r2 = $r1 + 68;
        $y1 = 6;
        $y2 = $y1 + 2;

        if ($order['type'] === 'bill')
            $text = utf8_decode("FACTURE EN " . EURO . " N° : " . $order['name']);
        else {
            $text = utf8_decode("PROFORMA");
        }
        $szfont = 12;
        $loop = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($text);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $text, 0, 0, "C");


        $this->SetXY(130, $y1 + 12);
        $this->SetFont("Arial", "", 12);
        $text = utf8_decode(strtoupper($f3->get('companyCity')) . ', le ' . date('Y/m/d', strtotime($order['timestamp'])));
        $this->Cell(10, 4, $text);
        $this->SetXY(130, $y1 + 20);
        $this->SetFont("Arial", "", 10);
        $text = utf8_decode('COMMANDE  N° ' . $order['id']);
        $this->Cell(10, 4, $text);
    }

    private function addOrderInfo($f3, $payment, $order, $r1 = 10, $y1 = 80)
    {
        $this->addOrderType($f3, $order);

        if ($order['type'] === 'bill') {
            $r2 = $r1 + 60;
            $y2 = $y1 + 10;
            $mid = $y1 + (($y2 - $y1) / 2);
            $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
            $this->Line($r1, $mid, $r2, $mid);
            $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
            $this->SetFont("Arial", "B", 10);
            $this->Cell(10, 4, utf8_decode("MODE DE RÈGLEMENT"), 0, 0, "C");
            $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
            $this->SetFont("Arial", "", 10);
            $this->Cell(10, 5, utf8_decode($payment['meanChargeType']), 0, 0, "C");

            $r1 += 70;
            $r2 = $r1 + 40;
            $y2 = $y1 + 10;
            $mid = $y1 + (($y2 - $y1) / 2);
            $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
            $this->Line($r1, $mid, $r2, $mid);
            $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
            $this->SetFont("Arial", "B", 10);
            $this->Cell(10, 4, utf8_decode("DATE D'ÉCHÉANCE"), 0, 0, "C");
            $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
            $this->SetFont("Arial", "", 10);
            $this->Cell(10, 5, utf8_decode($order['dueDate']), 0, 0, "C");

            $this->SetFont("Arial", "B", 10);
            $r1 = $this->w - 80;
            $r2 = $r1 + 70;
            $y2 = $y1 + 10;
            $mid = $y1 + (($y2 - $y1) / 2);
            $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
            $this->Line($r1, $mid, $r2, $mid);
            $this->SetXY($r1 + 16, $y1 + 1);
            $this->Cell(40, 4, utf8_decode("TVA Intracommunautaire"), '', '', "C");
            $this->SetFont("Arial", "", 10);
            $this->SetXY($r1 + 16, $y1 + 5);
            $this->Cell(40, 5, $f3->get('companyVat'), '', '', "C");
        }
    }

    private function addPurchaseInfo($f3, $order, $r1 = 10, $y1 = 80)
    {
        $this->SetFont("Arial", "B", 10);
        $r1 = $this->w - 80;
        $r2 = $r1 + 70;
        $y2 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + 16, $y1 + 1);
        $this->Cell(40, 4, utf8_decode("TVA Intracommunautaire"), '', '', "C");
        $this->SetFont("Arial", "", 10);
        $this->SetXY($r1 + 16, $y1 + 5);
        $this->Cell(40, 5, $f3->get('companyVat'), '', '', "C");
        $r1 = $this->w - 80;
        $r2 = $r1 + 68;
        $y1 = 6;
        $y2 = $y1 + 2;

        $text = utf8_decode("BON DE COMMANDE N°: " . $order['id']);

        $szfont = 12;
        $loop = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($text);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $text, 0, 0, "C");
    }

    private function sizeOfText($texte, $largeur)
    {
        $index = 0;
        $nb_lines = 0;
        $loop = TRUE;
        while ($loop) {
            $pos = strpos($texte, "\n");
            if (!$pos) {
                $loop = FALSE;
                $ligne = $texte;
            } else {
                $ligne = substr($texte, $index, $pos);
                $texte = substr($texte, $pos + 1);
            }
            $length = floor($this->GetStringWidth($ligne));
            $res = 1 + floor($length / $largeur);
            $nb_lines += $res;
        }
        return $nb_lines;
    }

    private function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $this->k, ($h - $y1) * $this->k,
            $x2 * $this->k, ($h - $y2) * $this->k, $x3 * $this->k, ($h - $y3) * $this->k));
    }

    private function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function addCols($tab, $r1 = 10, $y1 = 100)
    {
        global $colonnes;

        $r2 = $this->w - ($r1 * 2);
        $y2 = $this->h - 50 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, utf8_decode($lib), 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addLineFormat($tab)
    {
        global $format, $colonnes;

        foreach ($colonnes as $lib => $pos) {
            if (isset($tab["$lib"]))
                $format[$lib] = $tab["$lib"];
        }
    }

    function getLineNumber($f3, $line)
    {
        global $colonnes, $format;

        $lineNumber = 0;
        foreach ($line as $key => $value) {
            $width = ceil($this->GetStringWidth($value));
            $height = floor($width / $colonnes[$key]);
            $height = ($width % $colonnes[$key] > 0) ? $height + 1 : $height;
            $lineNumber = ($lineNumber > $height) ? $lineNumber : $height;
        }
        return $lineNumber;
    }

    function addLine($ligne, $tab)
    {
        global $colonnes, $format;

        $ordonnee = 10;
        $maxSize = $ligne;

        reset($colonnes);

        foreach ($colonnes as $lib => $pos) {
            $longCell = $pos - 2;
            $text = $tab[$lib];

            $formText = $format[$lib];
            $this->SetXY($ordonnee, $ligne - 1);
            $this->MultiCell($longCell, 4, $text, 0, $formText);
            if ($maxSize < ($this->GetY()))
                $maxSize = $this->GetY();
            $ordonnee += $pos;
        }
        return ($maxSize - $ligne);
    }
}