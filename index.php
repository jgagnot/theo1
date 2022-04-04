<?php
/*
    Ce micro-framework inclut :
    - Fatfree : https://fatfreeframework.com/3.6/home
    - Twig : https://twig.symfony.com/
    - Bootstrap 4.0 : https://getbootstrap.com/docs/4.0/getting-started/introduction/
    - Fontawesome Pro : https://fontawesome.com/
    - CodeIgniter Ion Auth (allégé) : http://benedmunds.com/ion_auth/
    - Stripe : https://stripe.com/
    - Paypal : https://developer.paypal.com/
    - Prism (coloration textuelle code) : http://prismjs.com/download.html
    - CustomFileInputs : https://tympanus.net/codrops/2015/09/15/styling-customizing-file-inputs-smart-way/
    - Intl-tel-input2 : https://github.com/jackocnr/intl-tel-input
    Vous devez personnaliser :
    - la rubrique "PERSONALIZE" ci-dessous (smtp)
    - le fichier setup.ini pour les variables globales
*/

// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');
require_once ('vendor/autoload.php');

/** @var \Base $f3 */

@$f3 = \Base::instance();

//Globals
$f3->config('project/setup.ini');
$f3->set('AUTOLOAD', 'app/; project/'.$f3->get('project').'/;');
$f3->set('DEBUG',3);

/***************************************************/
/*                                                 */
/*                   PERSONALIZE                   */
/*                                                 */
/***************************************************/

//BDD
$dbName=$f3->get('dbUser');
if (isset($dbName)) {
    try {
        $db=new DB\SQL( 'mysql:host='.$f3->get('dbHost').';dbname='.$f3->get('dbName').';charset=utf8',  $f3->get('dbUser'), $f3->get('dbPassword') );
        $f3->set('BDD',$db);
    } catch (PDOException $e) {
        $f3->error(404, 'Veuillez vérifier les informations de base de donnée');
        die;
    }
}

//Objet SMTP
$f3->set('smtp', new SMTP( 'auth.smtp.1and1.fr', 587, '', 'nepasrepondre@jeandeportal.com', 'kgfjEsACypn9JRE' ));
use core\imi;

//on injecte Twig
$twig = new Twig_Environment(new Twig_Loader_Filesystem('project/'.$f3->get('project').'/views/'),
    ['debug' => false, 'cache' => 'tmp/cache/', 'auto_reload' => true
    ]);
$twig->addFilter('f3' , new Twig_Filter_Function( ['f3', 'get']) );

// on relève le path (si hébergé sous SSL, on rajoute s à http)
// si la session n'est pas en https// on reroute immédiatement vers le même serveur en https
if (strpos($_SERVER['SERVER_NAME'],'.loc')!==false) {
    $f3->set('path', "http://" . $_SERVER['SERVER_NAME'] . '/');
} else {
    $f3->set('path', "https://" . $_SERVER['SERVER_NAME'] . '/');
    if (empty($_SERVER['HTTPS'])) $f3->reroute($f3->get('path'));
}

session_start();

/***************************************************/
/*                                                 */
/*                     ROUTING                     */
/*                                                 */
/***************************************************/
include('project/'.$f3->get('project').'/routing.php');

/**************************************************/

$f3->run();
