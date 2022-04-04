<?php
require_once("php-client/SwaggerClient-php/autoload.php");
$api_instance = new Swagger\Client\Api\SmsApi();
$smsrequest = new \Swagger\Client\Model\SMSRequest(); // \Swagger\Client\Model\SMSRequest | sms request
$smsrequest["keyid"]='bd69eacf206e861a690fcd4e54264beb';

$array[]="0624023140";
$smsrequest["num"]=$array;
$smsrequest["sms"]=["Ceci est un test avec un envoi multiple Ceci est un test avec un envoi multiple Ceci est un test avec un envoi multiple Ceci est un test avec un envoi multiple Ceci est un test avec un envoi multiple Ceci est un test avec un envoi multiple Ceci est un test avec un envoi multiple ".date('H:i:s')]; // 1 message ou autant de message que de destinataires
$smsrequest["emetteur"]="airMalin";
$smsrequest["smslong"]=999;
try {
    $result = $api_instance->sendSmsMulti($smsrequest);
    echo $result;
    echo '<br>';
    print_r(json_decode($result, true));
} catch (Exception $e) {
    echo 'Exception when calling SmsApi->sendSms: ',print_r($e), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}
?>
