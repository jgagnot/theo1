<?php
require_once('php-client/SwaggerClient-php/autoload.php');
include("keyid.php");
$api_instance = new Swagger\Client\Api\RepertoireApi();
$repertoirecreaterequest = new \Swagger\Client\Model\REPERTOIREcreaterequest(); // \Swagger\Client\Model\REPERTOIREcreaterequest | Creation repertoire
$repertoirecreaterequest["keyid"]=$keyid;
$repertoirecreaterequest["repertoire_edit"]="create";
$repertoirecreaterequest["repertoire_nom"]="Prospects interessants";
$id_nouveau_repertoire="";
try {
    $result = $api_instance->repertoireCrea($repertoirecreaterequest);
    echo $result;
    $id_nouveau_repertoire=$result["etat"]["etat"][0]["repertoire_id"];
} catch (Exception $e) {
    echo 'Exception when calling RepertoireApi->repertoireCrea: ', $e->getMessage(), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}
echo "nouveaurepertoire=".$id_nouveau_repertoire;
$api_instance = new Swagger\Client\Api\RepertoireApi();
$repertoiremodifrequest = new \Swagger\Client\Model\REPERTOIREmodifrequest(); // \Swagger\Client\Model\REPERTOIREmodifrequest | Requete de creation repertoire

$repertoiremodifrequest["keyid"]=$keyid;
$repertoiremodifrequest["repertoire_edit"]="add";
$repertoiremodifrequest["repertoire_id"]=$id_nouveau_repertoire;
$repertoiremodifrequest["num"]=["0612345600","061200345678","0600123456"];
try {
    $result = $api_instance->repertoire($repertoiremodifrequest);
    echo $result;
} catch (Exception $e) {
    echo 'Exception when calling RepertoireApi->repertoireCrea: ', $e->getMessage(), PHP_EOL;
    $reponse_erreur=$e->getResponseBody();
    echo json_encode($reponse_erreur);
}
?>

