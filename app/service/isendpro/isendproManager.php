<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 14/01/2021
 * Time: 09:10
 */
namespace service\isendpro;
use Swagger\Client\Api\RepertoireApi;
use Swagger\Client\Api\SmsApi;
use Swagger\Client\ApiException;
include(__DIR__.'/../../../vendor/isendpro/php-client/SwaggerClient-php/autoload.php');


use core\imi;

class isendproManager extends imi
{
    public function createRepertory($f3, $groupId){
        $group = $this->fetchOneById('userGroup', intval($groupId));

        if (empty($group)){
            return array('error', 'The provided groupId does not exist in database');
        }

        $api_instance = new RepertoireApi();
        $repertoryRequest = new \Swagger\Client\Model\REPERTOIREcreaterequest();

        $repertoryRequest->setKeyid($f3->get('isendproKeyid'));
        $repertoryRequest->setRepertoireNom($group['name']);

        try {
            $result = json_decode($api_instance->repertoireCrea($repertoryRequest), true)['etat']['etat'];
            $this->updateByArrayById('userGroup', array('repertoryId' => $result[0]['repertoireId']), intval($groupId));
            return array ('success');

        }catch (ApiException $e){
            return array('error', $e->getMessage());
        }
    }

    public function registerRepertoryMember($f3, $userId, $groupId){
        $group = $this->fetchOneById('userGroup', intval($groupId));

        if (empty($group)){
            return array('error', 'The provided groupId does not exist in database');
        }
        $user = $this->fetchOneById('user', intval($userId));
        if (empty($user)){
            return array('error', 'The provided userId does not exist in database');
        }
        if(empty($user['phone'])){
            return array('error', 'no phone in database for this user');
        }

        $api_instance = new RepertoireApi();
        $repertoryRequest = new \Swagger\Client\Model\REPERTOIREmodifrequest();

        $repertoryRequest->setKeyid($f3->get('isendproKeyid'));
        $repertoryRequest->setNum(array($user['phone']));
        $repertoryRequest->setRepertoireId($group['repertoryId']);
        $repertoryRequest->setRepertoireEdit('add');
        try{
            $result = json_decode($api_instance->repertoire($repertoryRequest), true)['etat']['etat'];
            return array ('success');
        }catch(ApiException $e){
            return array('error', $e->getMessage());
        }
    }

    public function unregisterRepertoryMember($f3, $userId, $groupId){
        $group = $this->fetchOneById('userGroup', intval($groupId));

        if (empty($group)){
            return array('error', 'The provided groupId does not exist in database');
        }
        $user = $this->fetchOneById('user', intval($userId));
        if (empty($user)){
            return array('error', 'The provided userId does not exist in database');
        }
        if(empty($user['phone'])){
            return array('error', 'no phone in database for this user');
        }

        $api_instance = new RepertoireApi();
        $repertoryRequest = new \Swagger\Client\Model\REPERTOIREmodifrequest();

        $repertoryRequest->setKeyid($f3->get('isendproKeyid'));
        $repertoryRequest->setNum(array($user['phone']));
        $repertoryRequest->setRepertoireId($group['repertoryId']);
        $repertoryRequest->setRepertoireEdit('del');
        try{
            $result = json_decode($api_instance->repertoire($repertoryRequest), true)['etat']['etat'];
            return array ('success');
        }catch(ApiException $e){
            return array('error', $e->getMessage());
        }
    }

    public function sendMessageToGroup($f3, $message, $groupId){
        $group = $this->fetchOneById('userGroup', intval($groupId));

        if (empty($group)){
            return array('error', 'The provided groupId does not exist in database.');
        }
        if (empty($group['repertoryId'])){
            return array('error', 'No repertory has been created for this group.');
        }

        $data_array = array(
            'keyid' =>  $f3->get('isendproKeyid'),
            'repertoireId' =>'179857',
            'sms' => $message,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://apirest.isendpro.com/cgi-bin/smsmulti');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_array);
        $result = json_decode(curl_exec($ch), true)['etat']['etat'];
        if (!empty($result)){
            return array ('success', $result);
        }else{
            return array('error', 'unexpected_error');
        }
    }

    public function sendMessageToUser($f3, $message, $userId){
        $user = $this->fetchOneById('user', intval($userId));
        if (empty($user)){
            return array('error', 'The provided userId does not exist in database.');
        }
        if (empty($user['phone'])){
            return array('error', 'This user does not have any phone number.');
        }

        $api_instance = new SmsApi();
        $smsrequest = new \Swagger\Client\Model\SmsUniqueRequest(); // \Swagger\Client\Model\SMSRequest | sms request

        $smsrequest->setKeyid($f3->get('isendproKeyid'));
        $smsrequest->setNum($user['phone']);
        $smsrequest->setSms($message);
        $smsrequest->setSmslong(999);
        try {
            $result = $api_instance->sendSms($smsrequest);
            $result_array = json_decode($result, true)['etat']['etat'];
           return array('success');
        } catch (ApiException $e) {
            return (array('error', $e->getMessage()));
        }
    }

    public function webhookHandler($f3){
        $data = $_GET;

        $phone = $_GET['tel'];
        $status = $_GET['statut'];
        if ($status === 0){

        }else{

        }
    }
}