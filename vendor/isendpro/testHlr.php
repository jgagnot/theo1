<?php
require_once('php-client/SwaggerClient-php/autoload.php');
$api_instance = new Swagger\Client\Api\HlrApi();
$hlrrequest = new \Swagger\Client\Model\HLRrequest(); // \Swagger\Client\Model\HLRrequest | Clee de compte sms low cost
$hlrrequest["get_hlr"]="1";
$hlrrequest["num"]=["0671820318"];
include("keyid.php");
$hlrrequest["keyid"]=$keyid;

try {
    $result = $api_instance->getHlr($hlrrequest);
    echo $result;
} catch (Exception $e) {
    echo 'Exception when calling HlrApi->getHlr: ', $e->getMessage(), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}
?>

