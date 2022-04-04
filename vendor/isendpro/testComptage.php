<?php
include("keyid.php");
require_once("php-client/SwaggerClient-php/autoload.php");
$api_instance = new Swagger\Client\Api\ComptageApi();
$comptagerequest = new \Swagger\Client\Model\ComptageRequest(); // \Swagger\Client\Model\ComptageRequest | sms request
$comptagerequest["keyid"]=$keyid;
$comptagerequest["num"]="0671820318";
$comptagerequest["sms"]="Ceci est un test avec un envoi unique à €! (comptage)";
$comptagerequest["emetteur"]="iSendPro";
try {
    echo $comptagerequest;
    $result = $api_instance->comptage($comptagerequest);
    echo $result;
    $nb_sms=$result["etat"]["etat"][0]["nb_sms"];
    $nb_caractere=$result["etat"]["etat"][0]["nb_caractere"];
    echo "iSendPro estime que votre message fait $nb_caractere caractères et necessitera $nb_sms sms pour être envoyé";
} catch (Exception $e) {
    echo 'Exception when calling ComptageApi->comptage: ', $e->getMessage(), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}
?>
