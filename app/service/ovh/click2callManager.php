<?php
/**
 * Created by PhpStorm.
 * User: quentinchampenois
 * Date: 17/05/2019
 * Time: 10:39
 */

namespace service\ovh;
use core\imi;
use Ovh\Api; // wrapper OVH


class click2callManager extends imi
{
    public function makePhoneCallById($f3, $id, $numberToCall) {
        // Lancer un appel de la ligne courante vers $numberToCall
        # POST /telephony/{billingAccount}/line/{serviceName}/{id}/click2Call

        $conn = $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);

        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/' . intval($id) . '/click2Call';

        $data = array(
            'calledNumber' => (string) $numberToCall, //  (type: string)
            'callingNumber' => ( $f3->get('ovhCallingNumber') ) ? $f3->get('ovhCallingNumber') : '', //(string)
            'intercom' => false, // Activate the calling number in intercom mode automatically (pick up and speaker automatic activation). (type: boolean)
        );
        return $conn->post($requestUrl, $data);
    }

    public function makePhoneCall($f3, $numberToCall, $intercom=false) {
        // Lancer un appel de la ligne courante vers $numberToCall
        // $intercom permet d'activer ou non le mode intercom, soit le fait de passer automatiquement l'appel en haut parleur lorsque l'on décroche
        # POST /telephony/{billingAccount}/line/{serviceName}/click2Call

        $conn = $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);

        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/click2Call';

        $data = array(
            'calledNumber' => (string) $numberToCall, //  (type: string)
            'callingNumber' => ( $f3->get('ovhCallingNumber') ) ? $f3->get('ovhCallingNumber') : '', //(string)
            'intercom' => $intercom, // (type: boolean)
        );
        return $conn->post($requestUrl, $data);
    }

    public function currentCredential($f3) {
        // Récupérer l'identifiant courant
        $conn = $this->setOvhApi($f3);
        $requestUrl = '/auth/currentCredential';

        return $conn->get($requestUrl);
    }

    public function getTelephony($f3) {
        // Récupérer la ligne téléphonique
        $url = '/telephony';
        $conn = $this->setOvhApi($f3);
        return $conn->get($url);
    }

    public function getUsersAuthorized($f3) {
        // retourne les utilisateurs pouvant utiliser click2call sur cette ligne
        $authorization = $this->authorization($f3);
        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/click2CallUser';

        $conn = $this->setOvhApi($f3);
        return $conn->get($requestUrl);
    }

    public function changePasswordUser($f3, $id, $password) {
        // Changer le mot de passe d'un utilisateur
        $conn = $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);
        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/click2CallUser/'. intval($id).'/changePassword';

        $data = array(
            'password' => $password, //  (type: password)
        );
        return $conn->post($requestUrl, $data);
    }

    public function deleteUser($f3, $id) {
        // Supprimer un utilisateur
        $conn = $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);
        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/click2CallUser/'. intval($id);

        return $conn->delete($requestUrl);
    }

    public function createUser($f3, Array $data) {
        // Créer un nouvel utilisateur pour utiliser l'api

        $conn = $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);
        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/click2CallUser';

        $data = array(
            'login' => $data['login'], //  (type: string)
            'password' => $data['password'], //  (type: string)
        );
        return $conn->post($requestUrl, $data);
    }

    public function getNumberLineAssociated($f3) {
        // Récupérer les numeros internationaux des lignes téléphoniques associées au compte

        $conn =  $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);
        $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line';

        return $conn->get($requestUrl);
    }

    public function getUsers($f3, Array $array_id) {
        // Récupérer les informations d'un utilisateur du service
        # GET /telephony/{billingAccount}/line/{serviceName}/click2CallUser/{id}

        $conn =  $this->setOvhApi($f3);
        $authorization = $this->authorization($f3);
        $res = array();
        for ($i = 0; $i < count($array_id); $i++) {
            $requestUrl = '/telephony/' . $authorization['billingAccount'] . '/line/' . $authorization['serviceName'] . '/click2CallUser/' . intval($array_id[$i]);
            array_push($res, $conn->get($requestUrl));
        }
        return $res;
    }


    public function logoutFromApi($f3) {
        // Se déconnecter de l'api
        $conn = $this->setOvhApi($f3);

        $requestUrl = '/auth/logout';
        $f3->set('SESSION.consumerKey', '');
        return $conn->post($requestUrl);
    }


    public function connectToApi($f3, $get=true, $post=true, $put=true, $delete=true) {
        // Créer une connexion à l'api OVH (REQUIRED) pour pouvoir utiliser l'api
        $rights = array();

        if ($get) array_push($rights, [ 'method' => 'GET', 'path' => '/*' ]);
        if ($post) array_push($rights, [ 'method' => 'PUT', 'path' => '/*' ]);
        if ($put) array_push($rights, [ 'method' => 'POST', 'path' => '/*' ]);
        if ($delete) array_push($rights, [ 'method' => 'DELETE', 'path' => '/*' ]);

        // Get credentials
        $conn =  $this->setOvhApi($f3);
        $credentials = $conn->requestCredentials($rights, 'http://localhost:8888/ovh');
        // Save consumer key and redirect to authentication page
        $f3->set('SESSION.consumerKey', $credentials["consumerKey"]);
        return $credentials;
    }


    public function setOvhApi($f3) {
        // Créer un object API pour utiliser les méthodes vendor/ovh/ovh/src/Api.php
        return new Api(
            $f3->get('applicationKey'),
            $f3->get('applicationSecret'),
            'ovh-eu',
            $f3->get('SESSION.consumerKey')
        );
    }

    public function authorization($f3) {
        // Récupération des authorisations required pour la plus part des routes de l'api
        return array(
            'billingAccount' => $f3->get('ovhBillingAccount'),
            'serviceName' => $f3->get('ovhServiceName')
        );
    }

}


