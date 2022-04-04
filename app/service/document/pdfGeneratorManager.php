<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 10/07/2019
 * Time: 09:21
 */


namespace service\document;
use core\imi;


class pdfGeneratorManager extends imi{

    public function getTemplates($f3){
        $setup = $this->isValidSetup($f3, 'templates');

        if ($setup[0] !== 'success')
            return $setup;

        $header = [
            'X-Auth-Key: '.$setup[1]['key'],
            'X-Auth-Workspace: '.$setup[1]['workspace'],
            'X-Auth-Signature: '.$setup[1]['signature'],
            'Accept: '. 'application/json',
            'Content-Type: '.'application/json; charset=utf-8'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://us1.pdfgeneratorapi.com/api/v3/'.$setup[1]['resource']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);
        if (!isset($result['response']))
            return array('error', 'unexpected request error');

        $templateList = $this->fetchAll('pdfTemplate');
        foreach($result['response'] as $template){
            if (in_array(array('templateId' => $template['id']), $templateList) === false ){
                $this->insertByArray('pdfTemplate', array('templateId' => $template['id'], 'name' => $template['name']), []);
            }
        }
        foreach($templateList as $template){
            if (in_array(array('id' => $template['templateId']), $result['response']) === false ){
                $this->deleteById('pdfTemplate', intval($template['id']));
            }
        }
        return array('success', $result['response']);
    }

    public function getTemplateByTemplateId($f3, $templateId){
        $setup = $this->isValidSetup($f3, 'templates/'.$templateId);
        if ($setup[0] !== 'success')
            return $setup;

        $header = [
            'X-Auth-Key: '.$setup[1]['key'],
            'X-Auth-Workspace: '.$setup[1]['workspace'],
            'X-Auth-Signature: '.$setup[1]['signature'],
            'Accept: '. 'application/json',
            'Content-Type: '.'application/json; charset=utf-8'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://us1.pdfgeneratorapi.com/api/v3/'.$setup[1]['resource']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);

        if (!isset($result['response']))
            return array('error', 'unexpected request error');

        return array('success', $result['response']);

    }

    public function createEncodedDocument($f3, $templateId, $data){
        $setup = $this->isValidSetup($f3, 'templates/'.$templateId.'/output');
        if ($setup[0] !== 'success')
            return $setup;

        $header = [
            'X-Auth-Key: '.$setup[1]['key'],
            'X-Auth-Workspace: '.$setup[1]['workspace'],
            'X-Auth-Signature: '.$setup[1]['signature'],
            'Accept: '. 'application/json',
            'Content-Type: '.'application/json; charset=utf-8'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://us1.pdfgeneratorapi.com/api/v3/'.$setup[1]['resource']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);

        if (!isset($result['response']))
            return array('error', 'unexpected request error');
        $documentId = $this->insertByArray('pdfDocument', array('name' => $result['meta']['name'], 'encodedDocument' => $result['response']),[]);
        if (intval($documentId) <= 0)
            return array('error', 'pdf save error');

        return array('success', $documentId);
    }

    public function createUrlDocument($f3, $templateId, $data, $name=null){
        $setup = $this->isValidSetup($f3, 'templates/'.$templateId.'/output');
        if ($setup[0] !== 'success')
            return $setup;

        $header = [
            'X-Auth-Key: '.$setup[1]['key'],
            'X-Auth-Workspace: '.$setup[1]['workspace'],
            'X-Auth-Signature: '.$setup[1]['signature'],
            'Accept: '. 'application/json',
            'Content-Type: '.'application/json; charset=utf-8'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://us1.pdfgeneratorapi.com/api/v3/'.$setup[1]['resource'].'?output=url');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);

        if (!isset($result['response']))
            return array('error', 'unexpected request error');
        $documentId = $this->insertByArray('pdfDocument', array('name' => $result['meta']['name'], 'url' => $result['response']),[]);
        if (intval($documentId) <= 0)
            return array('error', 'pdf save error');

        return array('success', $documentId);
    }

    private function isValidSetup($f3, $resource){
        if ($f3->get('pdfGeneratorKey') == null)
            return array('error', 'missing pdfGenerator api key');
        if ($f3->get('pdfGeneratorSecret') == null)
            return array('error', 'missing pdfGenerator secret key');
        if ($f3->get('pdfGeneratorWorkspace') == null)
            return array('error', 'missing pdfGenerator workspace');

        $data = array('key'=> $f3->get('pdfGeneratorKey'), 'resource'=> $resource, 'workspace'=>$f3->get('pdfGeneratorWorkspace'));
        ksort($data);

        return array('success',
            array(
                'key' => $f3->get('pdfGeneratorKey'),
                'workspace' =>  $f3->get('pdfGeneratorWorkspace'),
                'resource'=> $resource,
                'signature' =>  hash_hmac('sha256', implode('', $data), $f3->get('pdfGeneratorSecret'))
            )
        );
    }

    public function displayEncodedPdf($f3, $documentId){
        $pdf_array = $this->fetchOneById('pdfDocument', intval($documentId));

        $string = base64_decode($pdf_array['encodedDocument']);
        header('Content-Type: application/pdf');
        echo $string;
    }
}