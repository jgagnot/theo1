<?php
require_once("php-client/SwaggerClient-php/autoload.php");
$api_instance = new Swagger\Client\Api\CreditApi();
include("keyid.php");
$credit = "2"; // string | Type de reponse demandée, 1 pour euro et 2 pour messages

try {
    $result = $api_instance->getCredit($keyid, $credit);
    echo "L'api me retourne le JSON suivant:".$result."\n";
    echo "Je peux encore envoyer ".$result["etat"]["quantite"]." SMS en France métropolitaine";
} catch (Exception $e) {
    echo 'Exception when calling CreditApi->getCredit: ', $e->getMessage(), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}

?>

