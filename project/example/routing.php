<?php
/**
 * Created by PhpStorm.
 * User: nicolasdelourme
 * Date: 21/03/2018
 * Time: 10:50
 */

use controller\developmentController;
use core\imi;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding, Algorithm, Keyid, Headers, Timestamp");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");

/*
 * syntaxe pour créer une url:
 *
 * $f3->route( (String) $url, (methode)$function );
 *
 * $url : cet argument se compose de la façon suivante: GET ou POST , puis la l'url exemple : 'GET /accueil' ou 'POST /execForm').
 * $function: methode qui va executer ton code php et appeler la vue si besoin.
 *
 */

$f3->route('GET /', function($f3) {
        // on charge ici le twig.
        $twig = $GLOBALS['twig'];

        // cette méthode appelle la vue.
        // pour du html classique, faire echo '[chemin de la vue]';
        echo $twig->render('home.html.twig',array('f3'=>$f3));
    }
);


/******************* PAGES D'ERREUR *************************/

$f3->set('ONERROR', function($f3) {
    if ($f3->get('ERROR.code')==404) {
        $twig = $GLOBALS['twig'];
        echo $twig->render('404.html.twig');
    }
});