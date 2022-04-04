<?php
/**
 * Created by PhpStorm.
 * User: nicolasdelourme
 * Date: 18/01/2018
 * Time: 17:52
 */

namespace service\payment;

use core\imi;
use service\shopping\shoppingManager;
use PayPal\Api\Agreement;
use PayPal\Api\Amount;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Plan;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PayPal\Api\Sale;
use PayPal\Common\PayPalModel;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class paypalManager extends imi
{

    private $_paypalClientId='AY3pJIuAX53OZy95jlWSE1eGVMoCUw-h8Qh_6lRh9jMf5rQQRQDL_cKtJzAssd6kWe7kBElPvUDV_QAG';
    private $_paypalSecret='EKfQsuyJfwbh6tpCuWLk4CVjUzudUm4O--kMx2cVPrXKcw3gMhRMuliG-woq-cIAKbk76S45DtqQRPtW';

    function paymentTimetable ($f3,$paymentTimetable_array) {

        if (count($paymentTimetable_array)==1) {
            $meanChargeType='PaypalPayment';
        } else {
            $meanChargeType='PaypalAgreement';
        }

        foreach ($paymentTimetable_array as $key=>$value) {

            //on insère le payment en base de données
            $paymentId = $this->insertByArray('payment', array(
                'userId' => $this->getF3("SESSION.userId"),
                'paymentOriginType' => 'SHOP',
                'paymentOriginId' => $this->getF3("SESSION.orderId"),
                'amount' => $value['amount'],
                'currency' => $value['currency'],
                'execution' => $value['date']->format('Y-m-d H:i:s'),
                'status' => 'PEND',
                'meanChargeType' => $meanChargeType,
                'meanChargeId' => NULL
            ), []);
        }


        //si un seul paiment, on utilise la méthode Payment (expressCheckout)
        //sinon, on utilise la méthode createBillingPlan
        if (count($paymentTimetable_array)==1) {
            $paymentPaypal=new paypalManager();
            $paymentPaypalPayment=$paymentPaypal->expressCheckout($f3,$paymentId);
        } else {
            $paypalBillingplanId=$this->createBillingPlanFromPaymentTimetable($f3,$paymentTimetable_array);
            if ($this->activeBillingPlan($f3,$paypalBillingplanId)) {
            //echo '<br>paymentPaypal.php 74 '.$paypalBillingplanId;
                $this->agreementBillingPlan($f3,$paypalBillingplanId,$paymentTimetable_array);
            }

        }
    }




    /***************************************************/
    /*                                                 */
    /*         PAYMENTS API (expresseCheckout)         */
    /*                                                 */
    /***************************************************/


    private function expressCheckout ($f3,$paymentId) {

        require 'vendor/autoload.php';

        $expressCheckoutControlKey=$this->randomString();

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));
        $apiContext->setConfig(array('mode'=>'live'));

        $shoppingManager=new shoppingManager();
        $basket_array=$shoppingManager->basketReceipt();

        $redirectUrls=new RedirectUrls();
        $redirectUrls   ->setReturnUrl($f3->get('path').'paypal/payment/return')
                        ->setCancelUrl($f3->get('path').'paypal/payment/cancel');

        $payer=new Payer();
        $payer->setPaymentMethod('paypal');

        $details=new Details();
        $details    ->setSubtotal((str_replace(',','.',round(($basket_array['receipt']['untaxedPrice']*0.01),2))))
                    ->setTax(str_replace(',','.',round(($basket_array['receipt']['tax']*0.01),2)))
                    ->setShipping(0)
                    ->setShippingDiscount(0);

        $itemList=$this->generateItemList($f3);

        $amount=new Amount();
        $amount ->setTotal(str_replace(',','.',round(($basket_array['receipt']['total']*0.01),2)))
                ->setCurrency('EUR')
                ->setDetails($details);

        $transaction=new Transaction();
        $transaction    ->setAmount($amount)
                        ->setItemList($itemList)
                        ->setDescription($f3->get('projectName'))
                        ->setInvoiceNumber($paymentId)
                        ->setCustom($this->getF3("SESSION.orderId").$expressCheckoutControlKey);

        $payment=new Payment();
        $payment->setIntent('sale');
        $payment->setRedirectUrls($redirectUrls);
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);

        $payment->create($apiContext);


        try {
            //on garde en session le custom de la transaction pour contrôle au retour de Paypal
            $this->setF3("SESSION.paypalPaymentCustom",$this->getF3("SESSION.orderId").$expressCheckoutControlKey);
            $twig = $GLOBALS['twig'];
            echo $twig->render('payment/paypalExpressCheckout.html.twig', array('f3'=>$f3, 'approvalLink'=>$payment->getApprovalLink()));

        } catch (PayPalConnectionException $e) {
            $this->sessionClear('paypalPaymentCustom');
            echo 'erreur : '.$e->getMessage();
            echo '<br>code : '.$e->getCode();
            echo '<br>data : '.$e->getData();
        }

    }


    public function executePayment($f3,$paypalPaymentId,$payerId) {

        require 'vendor/autoload.php';

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        $payment=\PayPal\Api\Payment::get($paypalPaymentId,$apiContext);

        $execution=(new PaymentExecution())
            ->setPayerId($payerId)
            ->setTransactions($payment->getTransactions());

        try {
            $payment->execute($execution, $apiContext);
            if ($payment->getTransactions()[0]->getCustom()==$this->getF3("SESSION.paypalPaymentCustom")) {

                $createdTime= new \datetime($payment->getCreateTime(), new \DateTimeZone('UTC'));
                //on change le fuseau horaire de l'objet DateTime (en heure UTC par défaut)
                $createdTime->setTimezone(new \DateTimeZone('Europe/Paris'));

                //on insere le paypalPayment

                $id=$this->insertByArray('paymentPaypalPayment',array(
                    'amount'=>$payment->getTransactions()[0]->getAmount()->getTotal(),
                    'currency'=>$payment->getTransactions()[0]->getAmount()->getCurrency(),
                    'created'=>$createdTime->format('Y-m-d H:i:s'),
                    'ressourceType'=>'sale',
                    'paypalPaymentId'=>$payment->getId(),
                    'paymentState'=>$payment->getState(),
                    'ressourceState'=>$payment->getTransactions()[0]->getRelatedResources()[0]->getSale()->getState(),
                    'ressourceId'=>$payment->getTransactions()[0]->getRelatedResources()[0]->getSale()->getId()
                ),[]);

                if ($payment->getState() === 'approved'){
                    //on update payment
                    $this->updateByArrayById('payment',array(
                        'status'=>'PAID',
                        'meanChargeId'=>$id
                    ),$payment->getTransactions()[0]->getInvoiceNumber());
                }
                else {
                    $this->updateByArrayById('payment',array(
                        'status'=>'FAIL',
                        'meanChargeId'=>$id
                    ),$payment->getTransactions()[0]->getInvoiceNumber());
                }



echo 'payment OK<br>';
echo '<br>getId '.$payment->getId();
echo '<br>getState '.$payment->getState();
echo '<br>getCreateTime '.$payment->getCreateTime();
echo '<br>getPayerId '.$payment->getPayer()->getPayerInfo()->getPayerId();
echo '<br>getTotal '.$payment->getTransactions()[0]->getAmount()->getTotal();
echo '<br>getCurrency '.$payment->getTransactions()[0]->getAmount()->getCurrency();

                return true;
            } else {
                echo '<br>paymentPaypal.php 160';
                return false;
            }
        } catch (PayPalConnectionException $e) {
            echo 'erreur : '.$e->getMessage();
            echo '<br>code : '.$e->getCode();
            echo '<br>data : '.$e->getData();
            return $e;
        }
    }


    public function getPayment ($f3,$id) {

        require 'vendor/autoload.php';

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        try {
            $payment=\PayPal\Api\Payment::get($id,$apiContext);
           // print_r(json_decode($payment));echo'<br><br>';
            if ($payment->getTransactions()[0]->getCustom()==$this->getF3("SESSION.paypalPaymentCustom")) {
                return $payment;
            } else {
                return false;
            }
        } catch (PayPalConnectionException $e) {
            echo 'erreur : '.$e->getMessage();
        }

    }


    private function generateItemList ($f3)
    {
        //cette méthode instancie et retourne, depuis l'objet $basket de f3, l'objet $item (liste des items)

        $basket = new \Basket();
        $basket_array = $basket->find();

        $itemListString='"items": [';

        require 'vendor/autoload.php';

        $itemList=new ItemList();

        foreach ($basket_array as $key => $value) {

            $description=$this->fetchOneById('shoppingItem',$value['itemId'])['description'];

            $price=$value['price']*0.01;
            $vat=$value['vat']*0.01;

            $untaxedPrice=round($price/(1+$vat*0.01),2);
            $tax=$price-$untaxedPrice;
            $untaxedPrice=str_replace(',','.',$untaxedPrice);
            $tax=str_replace(',','.',$tax);

            $item=new Item();
            $item   ->setQuantity($value['quantity'])
                    ->setName($value['name'])
                    ->setPrice($untaxedPrice)
                    ->setCurrency(strtoupper($value['currency']))
                    ->setDescription(substr($description,0,200))
                    ->setTax($tax);
            $itemList->addItem($item);
        }

        return $itemList;

    }



    /***************************************************/
    /*                                                 */
    /*                BILLING PLANS API                */
    /*                                                 */
    /***************************************************/


    public function getBillingPlan ($f3,$id) {

        require 'vendor/autoload.php';

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        try {
            $plan = Plan::get($id, $apiContext);
        //    print_r($plan);
            echo '<br> Frequency : '.$plan->getPaymentDefinitions()[0]->getType();
            echo '<br> Frequency : '.$plan->getPaymentDefinitions()[0]->getFrequency();
            echo '<br> Frequency : '.$plan->getPaymentDefinitions()[0]->getFrequencyInterval();
            echo '<br> Frequency : '.$plan->getPaymentDefinitions()[0]->getCycles();
        } catch (PayPalConnectionException $e) {
            echo 'erreur : '.$e->getMessage();
        }

    }


    private function activeBillingPlan ($f3,$id) {

        require 'vendor/autoload.php';

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        $plan = new Plan();
        $plan->setId($this->fetchOneById('paymentPaypalBillingplan',$id)['paypalId']);

        try {
            $patch = new Patch();
            $value = new PayPalModel('{
	            "state":"ACTIVE"
	     }');
            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);
            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);
            $plan->update($patchRequest, $apiContext);
            $plan = Plan::get($plan->getId(), $apiContext);
            return true;

        } catch (PayPalConnectionException $e) {
            return $e->getMessage();
        }

    }


    private function createBillingPlanFromPaymentTimetable($f3,$paymentTimetable_array) {

        //cette méthode calcule un plan depuis TimeTable et retourne son id

        require 'vendor/autoload.php';

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        //on regarde si le montant entre deux paiements est toujours le même
        if ($paymentTimetable_array[0]['amount']==$paymentTimetable_array[1]['amount']) {
            $isEqualAmount=true;
        } else {
            $isEqualAmount=false;
        }

        //on regarde si l'intervalle entre deux paiements est toujours le même
        if ($paymentTimetable_array[0]['interval']==$paymentTimetable_array[1]['interval']) {
            $isEqualFrequecyInterval=true;
        } else {
            $isEqualFrequecyInterval=false;
        }

        //on définit les intervalles entre deux paiements…

        //… du TRIAL
        if (substr($paymentTimetable_array[0]['interval'],0,3)=='FIX') {
            $trialInterval='Month';
            $trialFrequencyInterval=1;
        } else {
            if (substr($paymentTimetable_array[0]['interval'],(strlen($paymentTimetable_array[0]['interval'])-1),1)=='D') $trialInterval='Day';
            if (substr($paymentTimetable_array[0]['interval'],(strlen($paymentTimetable_array[0]['interval'])-1),1)=='M') $trialInterval='Month';
            if (substr($paymentTimetable_array[0]['interval'],(strlen($paymentTimetable_array[0]['interval'])-1),1)=='Y') $trialInterval='Year';
            $trialFrequencyInterval=substr($paymentTimetable_array[0]['interval'],1,(strlen($paymentTimetable_array[0]['interval'])-2));
        }
        //… du REGULAR
        if (substr($paymentTimetable_array[1]['interval'],0,3)=='FIX') {
            $regularInterval='Month';
            $regularFrequencyInterval=1;
        } else {
            if (substr($paymentTimetable_array[0]['interval'],(strlen($paymentTimetable_array[0]['interval'])-1),1)=='D') $regularInterval='Day';
            if (substr($paymentTimetable_array[0]['interval'],(strlen($paymentTimetable_array[0]['interval'])-1),1)=='M') $regularInterval='Month';
            if (substr($paymentTimetable_array[0]['interval'],(strlen($paymentTimetable_array[0]['interval'])-1),1)=='Y') $regularInterval='Year';
            $regularFrequencyInterval=substr($paymentTimetable_array[0]['interval'],1,(strlen($paymentTimetable_array[0]['interval'])-2));
        }

        $planName='';
        //on instancie les paymentDefinition du plan
        //pour plus d'information sur cette partie de la méthode voir : https://paper.dropbox.com/doc/PayPal-API-9NhdcUztHdevifDPpNDsW
        if ($isEqualAmount) {
            if ($isEqualFrequecyInterval) {
                $paymentDefinitionName = 'R' . count($paymentTimetable_array) . '_' . substr($regularInterval, 0, 1) . $regularFrequencyInterval . '_' . $paymentTimetable_array[0]['amount'];
                $planName .= $paymentDefinitionName;
                $paymentDefinition[0] = new PaymentDefinition();
                $paymentDefinition[0]->setName($paymentDefinitionName)
                    ->setType('REGULAR')
                    ->setFrequency($regularInterval)
                    ->setFrequencyInterval($regularFrequencyInterval)
                    ->setCycles(count($paymentTimetable_array))
                    ->setAmount(new Currency(array('value' => str_replace(',','.',round(($paymentTimetable_array[0]['amount']*0.01),2)), 'currency' => strtoupper($paymentTimetable_array[0]['currency']))));
            }
            else{
                $paymentDefinitionName= 'T1'.'_'.substr($trialInterval, 0, 1).$trialFrequencyInterval.'_'.$paymentTimetable_array[0]['amount'].'-R'.(count($paymentTimetable_array) - 1).'_'.substr($regularInterval, 0, 1).$regularFrequencyInterval.'_'.$paymentTimetable_array[0]['amount'];
                $definitionName_array = explode('-', $paymentDefinitionName);
                $planName .= $paymentDefinitionName;
                $paymentDefinition[0] = new PaymentDefinition();
                $paymentDefinition[0]->setName($definitionName_array[0])
                    ->setType('TRIAL')
                    ->setFrequency($trialInterval)
                    ->setFrequencyInterval($trialFrequencyInterval)
                    ->setCycles(1)
                    ->setAmount(new Currency(array('value' => str_replace(',','.',round(($paymentTimetable_array[0]['amount']*0.01),2)), 'currency' => strtoupper($paymentTimetable_array[0]['currency']))));
                $paymentDefinition[1] = new PaymentDefinition();
                $paymentDefinition[1] ->setName($definitionName_array[1])
                    ->setType('REGULAR')
                    ->setFrequency($regularInterval)
                    ->setFrequencyInterval($regularFrequencyInterval)
                    ->setCycles(count($paymentTimetable_array) - 1)
                    ->setAmount(new Currency(array('value' => str_replace(',','.',round(($paymentTimetable_array[0]['amount']*0.01),2)), 'currency' => strtoupper($paymentTimetable_array[0]['currency']))));
            }
        }
        else{
                $paymentDefinitionName= 'T1'.'_'.substr($trialInterval, 0, 1).$trialFrequencyInterval.'_'.$paymentTimetable_array[0]['amount'].'-R'.(count($paymentTimetable_array) - 1).'_'.substr($regularInterval, 0, 1).$regularFrequencyInterval.'_'.$paymentTimetable_array[1]['amount'];
                $definitionName_array = explode('-', $paymentDefinitionName);
                $planName .= $paymentDefinitionName;
                $paymentDefinition[0] = new PaymentDefinition();
                $paymentDefinition[0]->setName($definitionName_array[0])
                    ->setType('TRIAL')
                    ->setFrequency($trialInterval)
                    ->setFrequencyInterval($trialFrequencyInterval)
                    ->setCycles(1)
                    ->setAmount(new Currency(array('value' => str_replace(',','.',round(($paymentTimetable_array[0]['amount']*0.01),2)), 'currency' => strtoupper($paymentTimetable_array[0]['currency']))));
                $paymentDefinition[1] = new PaymentDefinition();
                $paymentDefinition[1] ->setName($definitionName_array[1])
                    ->setType('REGULAR')
                    ->setFrequency($regularInterval)
                    ->setFrequencyInterval($regularFrequencyInterval)
                    ->setCycles(count($paymentTimetable_array) - 1)
                    ->setAmount(new Currency(array('value' => str_replace(',','.',round(($paymentTimetable_array[1]['amount']*0.01),2)), 'currency' => strtoupper($paymentTimetable_array[0]['currency']))));
        }

        $merchantPreferences=new MerchantPreferences();
        $merchantPreferences
            ->setReturnUrl($f3->get('path')."paypal/agreement/return/success")
            ->setCancelUrl($f3->get('path')."paypal/agreement/return/cancel")
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new Currency(array('value' => 1, 'currency' => strtoupper($paymentTimetable_array[0]['currency']))));

        $plan = new Plan();

        //on vérifie que le plan n'existe pas déjà en base
        if ($this->existsValue('paymentPaypalBillingplan',array('name'=>$planName))) {
            return $this->fetchOneByKeysEqual('paymentPaypalBillingplan',array('name'=>$planName))['id'];
        }

        $plan   ->setName($planName)
            ->setDescription($planName)
            ->setType('fixed')
            ->setPaymentDefinitions($paymentDefinition)
            ->setMerchantPreferences($merchantPreferences);

        try {
            $output = $plan->create($apiContext);
            $id=$this->insertByArray('paymentPaypalBillingplan',array(
                    'paypalId'=>$output->getId(),
                    'name'=>$output->getName(),
                    'type'=>$output->getType())
                ,[]);
//echo '<br><br>paymentPaypal.php 422 '.$output->getId();
            return $id;
        } catch (PayPalConnectionException $e) {
            return $e->getMessage();
        }
    }

    public function getAgreementDetails($f3, $agreementId){
        /*
         * pour plus d'informations concernant la structure des datas et les champs modifiables:
         * https://developer.paypal.com/docs/api/payments.billing-plans/v1/#billing-plans_create
         *
         */
        $apiContext= new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        try {
            $agreement = Agreement::get($agreementId, $apiContext);
            var_dump($agreement);
            return (1);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return (0);
        }

    }


    private function agreementBillingPlan ($f3,$billingplanId,$paymentTimetable_array) {

        require 'vendor/autoload.php';

        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        //on créée la date de départ de l'agreement à now+10Minutes (la date devant être ultérieure à l'agreement)
        $startDate=new \datetime('now', new \DateTimeZone('Z'));
        $startDate = $startDate->add(new \DateInterval('PT10M'));

        $planName=$this->fetchOneById('paymentPaypalBillingplan',$billingplanId)['paypalId'];
       // echo '<br>paymentPaypal.php 342 '.$planName;

        $agreement = new \PayPal\Api\Agreement();
        $agreement  ->setName($planName)
            ->setDescription($this->agreementDescription($f3,'fixed',$paymentTimetable_array))
            ->setStartDate($startDate->format('Y-m-d\TH:i:sT'));

        $plan = new Plan();
        $plan->setId($planName);

        $agreement->setPlan($plan);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $agreement->setPayer($payer);

        $order = $this->fetchOneById('shoppingOrder', $_SESSION['orderId']);
        $adress = $this->fetchOneById('userAdress', $order['userAdressId']);

        $shippingAddress=new ShippingAddress();
        $shippingAddress    ->setCity($adress['city'])
                            ->setLine1($adress['line1'])
                            ->setLine2($adress['line2'])
                            ->setPostalCode($adress['zipcode'])
                            ->setCountryCode($adress['country']);
        $agreement->setShippingAddress($shippingAddress);
        $agreement->setOverrideMerchantPreferences($plan->getMerchantPreferences());
        try {
            $agreement = $agreement->create($apiContext);
            $approvalUrl = $agreement->getApprovalLink();

            $tokenPosition=strpos($approvalUrl,'token=')+strlen('token=');
            $token=substr($approvalUrl,$tokenPosition,strlen($approvalUrl)-$tokenPosition);

            $id=$this->insertByArray('paymentPaypalAgreement', array('token' => $token,'paypalBillingplanId'=>$billingplanId, 'paypalAgreementId' => $agreement->get), []);

            //on met à jour le champ meanChargeId de la table payment avec l'id du PaypalAgreement généré
            $payment_array=$this->fetchAllByKeysEqual('payment',array('paymentOriginType'=>'SHOP','paymentOriginId'=>$this->getF3("SESSION.orderId")));
            foreach ($payment_array as $key=>$value) {
                $this->updateByArrayById('payment',array('meanChargeId'=>$id),$value['id']);
            }
            $f3->reroute($approvalUrl);

        } catch (PayPalConnectionException $e) {
            echo $e->getCode();
            echo $e->getData();
            die($e);
        }

    }


    public function returnAgreement($f3,$success,$token,$return) {
        $apiContext=new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        $agreement = new Agreement();
        if (isset($success) && $success === true) {
            try {
                $exec = $agreement->execute($token, $apiContext);
                $paymentPaypalAgreementId=$this->fetchOneByKeysEqual('paymentPaypalAgreement',array('token'=>$token))['id'];
                $this->updateByArrayById('paymentPaypalAgreement',array('success'=>$success, 'paypalAgreementId'=>$exec->getId()),$paymentPaypalAgreementId);

                $payment_array=$this->fetchAllByKeysEqual('payment',array('meanChargeId'=>$paymentPaypalAgreementId));
            } catch (PayPalConnectionException $e) {
                echo($e->getMessage());
            }
        }
        //cette méthode traite le retour d'un agreement de Paypal

        if (!$success) {
            foreach ($payment_array as $key=>$value) {
                $this->updateByArrayById('payment',array('status'=>'CANC'),$value['id']);
            }
        }

        $shoppingManager=new shoppingManager();
        $shoppingManager->validation($f3,-1,1,$success);

    }

    private function agreementDescription ($f3,$type,$paymentTimetable_array) {

        //cette méthode retourne une chaine de description de l'agrément afin que le payeur prenne connaissance de con engagement sur la page d'identification Paypal

        $description='';
        foreach ($paymentTimetable_array as $key=>$value) {
            if ($value['currency']=='EUR') $currency='€';
            $description.=number_format(($value['amount']*0.01),2).' '.$currency;
            setlocale(LC_ALL, 'fr_FR');
            $description.=' le '.lcfirst(strftime(' %e %B',$value['date']->getTimestamp())).', ';
        }

        //on supprime la dernière virgule et on ajoute un point
        $description=substr($description,0,strlen($description)-2);
        $description.='.';

//echo '<br><br>paymentPaypal.php 490 '.$description;

        return $description;

    }

    public function getAgreementTransaction ($f3, $agreementId){
        $apiContext = new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

        $params = array('start_date' => date('Y-m-d', strtotime('-15 years')), 'end_date' => date('Y-m-d', strtotime('+5 years')));
        try {
            $result = Agreement::searchTransactions($agreementId, $params, $apiContext);
            var_dump($result);
        } catch (PayPalConnectionException $e) {

            echo $e->getMessage();
            exit(1);
        }

    }

    public function getSaleDetails($f3, $saleId){
        try {
            $apiContext = new ApiContext(new OAuthTokenCredential($this->_paypalClientId,$this->_paypalSecret));

            $sale = Sale::get($saleId, $apiContext);
            var_dump($sale);
        } catch (Exception $e) {

            var_dump($e->getMessage());
            exit(1);
        }
    }
}