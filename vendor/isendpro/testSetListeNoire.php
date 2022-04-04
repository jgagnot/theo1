<?php
require_once("php-client/SwaggerClient-php/autoload.php");
$api_instance = new Swagger\Client\Api\SetListeNoireApi();
include("keyid.php");
$setliste_noire='1'; // string |
$num = "0600123456"; // string | numéro de mobile à insérer en liste noire

try {
    $result = $api_instance->setListeNoire($keyid, $setliste_noire,$num);
    echo $result;
} catch (Exception $e) {
    echo 'Exception when calling SetListeNoireApi->setListeNoire: ', $e->getMessage(), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}
?>

