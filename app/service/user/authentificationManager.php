<?php

namespace service\user;

use core\imi;
use entity\user;
use entity\userGroup;
use repository\groupRepository;
use service\mailchimp\mailchimpManager;
use service\mandrill\mandrillManager;
use service\shopping\shoppingManager;


class authentificationManager extends imi
{
    private $salt_length = 22;
    private $hash_method = 'bcrypt';

    public function register($f3, $key, $userData, $password, $noRequiredPassword = 0)
    {
        //passer en paramètre le nom du champ ($key) qui sert de clé d'identification et son contenu ($value)
        $value = strtolower(addslashes($userData[$key]));

        // on vérifier la validité du format de l'email si celui-ci constitue la clé
        if (($key == 'email') && (!filter_var($value, FILTER_VALIDATE_EMAIL))) {
            return array('error', 'wrong_email_format');
        }

        //on vérifie la longueur du password si celui-ci est requis
        if (strlen($password) <= $f3->get('lenPassword') && !$noRequiredPassword) {
            return array('error', 'password_to_short');
        }
        $password = addslashes($password);
        // récupération du profil de l'utilisateur correspondant à la clé donnée.
        $user = new user($this->fetchOneByKeysLike('user', array($key => $value)));

        if ($user->getId() > 0) {
            //si l'utilisateur existe, on récupère son profil de connexion pour le projet.
            $userConnect = $this->fetchOneByKeysLike('userConnect', array('userId' => intval($user->getId()), 'project' => $f3->get('project')));
        } else {
            //si l'utilisateur n'existe pas, on créer un nouvel utilisateur
            $user = new user($userData);
            $user->setIpAddress($this->f3['SERVER']['SERVER_ADDR']);
            $user->setToken(strtoupper($this->randomString(5)));
            $user->setId($this->insertByArray('user', $user->getClassArray(), ['id']));
        }

        if (!empty($userConnect)) {
            // si un profil de connexion existe pour ce projet, on en vérifie le contenu
            if ($userConnect['registerValid'] == true) {
                // si l'utilisateur a déjà validé son inscription, on retourne une erreur
                return array('error', 'register_subscribed');
            } else {
                if (empty($userConnect['password']) && strlen($password) >= $f3->get('lenPassword') && $noRequiredPassword == 0) {
                    //si l'utilisateur n'a pas validé son inscription MAIS que son adresse mail est connue, on crée son mot de passe. et possède un mot de passe, on valide son inscription
                    //et on le log. C'est par exemple le cas de l'utilisateur préalablement inscrit à la newsletter, quiu s'inscrit ensuite de
                    //façon formelle, avec un mot de passe.
                    $salt = $this->salt();
                    $this->updateByArrayById('userConnect', array('password' => $this->hashPassword($password, $salt)), intval($userConnect['id']));
                }
                if ($f3->get('doubleOptin') === true && $userConnect['optinStatus'] !== 'subscribed') {
                    //si l'utilisateur n'a pas validé son inscription et qu'il n'a pas validé le doubleOptin, on renvoie le mail.
                    $this->mailDoubleOptin($f3, $user);
                }
                if ($userConnect['optinStatus'] === 'subscribed' && ($this->hashPasswordDb($f3, $user, $password) === true)) {
                    $this->updateByArrayById('userConnect', array('registerValid' => true), intval($userConnect['id']));
                    return $this->loginComplete($f3, $user);
                }
                //on renvoie une erreur dans tous les cas, sauf le précédent, à savoir celui de l'utilisateur qui se crée un mot de passe.
                return array('error', 'register_pending');
            }
        } else {
            //si aucun profil de connexion n'existe pour cet utilisateur, on en crée un, qu'on insère en abse avant d'envoyer le mail de
            //doubleOptin
            $salt = $this->salt();
            $this->insertByArray('userConnect',
                array('userId' => intval($user->getId()),
                    'project' => $f3->get('project'),
                    'password' => (strlen($password) > 0) ? $this->hashPassword($password, $salt) : '',
                    'optinStatus' => 'pending',
                ), ['id', 'timestamp']);
        }
        if ($f3->get('doubleOptin') === true) $this->mailDoubleOptin($f3, $user);
        return array('success', array('user_array' => $user->getClassArray()));
    }

    public function login($f3, $key, $value, $password = '')
    {
        //passer en paramètre le nom du champ ($key) qui sert de clé d'identification et son contenu ($value)

        if (empty($key) || empty($value)) return 'something_empty';
        $value = addslashes($value);
        $password = addslashes($password);
        //on vérifie la longueur du mot de passe
        if (strlen($password) <= $f3->get('lenPassword')) {
            return array('error', 'password_to_short');
        }

        //on récupère le profil de l'utilisateur. Si celui-ci n'existe pas, on renvoie une erreur
        $user = new user ($this->fetchOneByKeysLike('user', array($key => $value)));
        if ($user->getId() <= 0)
            return array('error', 'user_doesnt_exist');

        //on récupère le profil de connexion de l'utilisateur. Si celui-ci n'existe pas, on renvoie une erreur
        $userConnect = $this->fetchOneByKeysLike('userConnect', array('userId' => intval($user->getId()), 'project' => $f3->get('project')));
        if (empty($userConnect)) {
            return array('error', 'user_not_subscribed');
        }

        // on hash le mot de passe.
        $password = $this->hashPasswordDb($f3, $user, $password);

        if (boolval($userConnect['registerValid']) === false) {
            //si l'utilisateur n'est pas déjà validé, on vérifie si il rempli toutes les conditions pour l'être:
            if ($userConnect['optinStatus'] !== 'subscribed') {
                // si l'utilisateur n'a pas validé le doubleOptin, on renvoie le mail et on retourne une erreur
                $this->mailDoubleOptin($f3, $user);
                return array('error', 'register_pending');
            } else if ($password === TRUE && $userConnect['optinStatus'] === 'subscribed') {
                // si l'utilisateur a effectivement validé le mail d'optIn et que le mot de passe correspond à celui en base
                //, on le valide, puis on le connecte
                $this->updateByArrayById('userConnect', array('registerValid' => true), intval($userConnect['id']));
                return $this->loginComplete($f3, $user);
            } else {
                // dans tous les autres cas, on retourne une erreur
                return array('error', 'register_incomplete');
            }
        } else {
            // si l'utilisateur est validé, on se contente de vérifier la validité de son mot de passe, puis
            // de le connecter ou de retourner une erreur le cas échéant.
            if ($password === TRUE) {
                return $this->loginComplete($f3, $user);
            } else {
                return array('error', 'wrong_password');
            }
        }
    }

    public function loginComplete($f3, user $user)
    {
        $groupRepository = new groupRepository();
        $userConnect = $this->fetchOneByKeysLike('userConnect', array('userId' => intval($user->getId()), 'project' => $f3->get('project')));

        $date = date('Y-m-d H:i:s');
        $f3->set("SESSION.userId", $user->getId());
        $f3->set("SESSION.email", $user->getEmail());
        $f3->set("SESSION.token", $user->getToken());
        $f3->set("SESSION.permission", $groupRepository->checkPermission($f3, $user->getId()));
        $this->updateByArrayById('userConnect', array('lastLogin' => $date), $userConnect['id']);
        $ip_address = $this->f3['SERVER']['SERVER_ADDR'];
        $this->insertByArray('userSession', array('userId' => $user->getId(), 'sessionId' => $f3->get('COOKIE.PHPSESSID'), 'ip' => $ip_address), array('id', 'timestamp'));
        return array('success', 'user_array' => $user->getClassArray());

    }

    public function mailDoubleOptin($f3, user $user)
    {
        $this->setUserCookies($f3, $user);
        if ($f3->get('mailchimpCustomer') == true) {
            $mailchimpManager = new mailchimpManager();
            $mailchimpManager->registerCustomer($f3, true, $user);
            return true;
        } else if ($f3->get('mailchimpMember') == true) {
            $mailchimpManager = new mailchimpManager();
            return $mailchimpManager->registerMember($f3, true, $user);
        } else {
            $user->setDoubleOptinCode($this->randomString(40));
            $this->updateByArrayById('userConnect', array('optinStatus' => $user->getDoubleOptinCode()), $user->getId());
            $subject = 'Validez votre compte ' . $f3->get('projectName');
            $link = $f3->get('path') . 'doubleOptinComplete?r=' . $user->getDoubleOptinCode() . '$' . $user->getId();
            $message = "Validez votre compte en <a href='$link'>cliquant ici</a> ou sur le lien ci-dessous :<br><br>" . $link;
            return $this->sendMailF3($f3, $subject, $message, $user->getEmail(), $user->getEmail());
        }
    }

    public function lostPassword($f3, $email)
    {
        $email = strtolower($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array('error', 'wrong_email_format');
        }
        $user = new user($this->fetchOneByKeysLike('user', array('email' => htmlspecialchars($email))));
        if ($user->getId() <= 0) {
            return array('error', 'user_unknown');
        }
        $userConnect = $this->fetchOneByKeysLike('userConnect', array('userId' => intval($user->getId()), 'project' => $f3->get('project')));
        if (empty($userConnect)) {
            return array('error', 'user_not_subscribed');
        }
        return $this->lostPasswordSendMail($f3, $user);

    }


    public function lostPasswordSendMail($f3, user $user)
    {
        $mandrillManager = new mandrillManager();

        $userConnect = $this->fetchOneByKeysLike('userConnect', array('userId' => intval($user->getId()), 'project' => $f3->get('project')));
        if (empty($userConnect)) {
            return array('error', 'user_not_subscribed');
        }

        // on créé un code pour le changement de password et on donne 15 minutes pour l'utiliser
        $forgottenPasswordTime = time() + (60 * 15);
        $forgottenPasswordCode = uniqid(mt_rand(), true);

        $this->updateByArrayById('userConnect', array('forgottenPasswordCode' => $forgottenPasswordCode, 'forgottenPasswordTime' => $forgottenPasswordTime), $userConnect['id']);
        $link = $f3->get('path') . 'authentification/lostPassword/' . $forgottenPasswordCode . '$' . $user->getId();
        $subject = 'Réinitialisez votre mot de passe pour ' . $f3->get('projectName');
        if (empty($f3->get('mandrillApiKey'))) {
            $message = "Réinitialisez votre mot de passe en <a href='$link'>cliquant ici</a> ou sur le lien ci-dessous :<br><br>" . $link;
            $this->sendMailF3($f3, $subject, $message, $user->getEmail(), $user->getEmail());
        } else {
            $template = array(
                'template_name' => $f3->get('project') . 'ChangePassword',
                'global_merge_vars' => array(
                    array(
                        'name' => 'user',
                        'content' => $user
                    ),
                    array('name' => 'link_validation',
                        'content' => $link
                    )
                )
            );
            $mandrillManager->sendMailTemplate($f3, $subject, $user->getId(), $template);
        }
        return array('success');
    }

    public function lostPasswordReturn($f3, $return)
    {

        $return_array = explode('$', $return);
        if ($this->existsValue('user', array('id' => intval($return_array[1])))) {
            if (!$this->existsValue('userConnect', array('project' => $f3->get('project'), 'userId' => intval($return_array[1])))) {
                return array('error', 'lostPasswordReturn_failed');
            }
            $user = new user($this->fetchOneById('user', intval($return_array[1])));
            $userConnect = $this->fetchOneByKeysEqual('userConnect', array('project' => $f3->get('project'), 'userId' => intval($return_array[1])));
            $difTime = $userConnect['forgottenPasswordTime'] - time();

            if ($difTime < 0) {
                return array('error', 'lostPasswordReturn_late');
            } else {
                $this->updateByArrayById('userConnect', array('forgottenPasswordTime' => 0), $userConnect['id']);
                return array('success', array('forgottenPasswordCode' => $return_array[0], 'userId' => $user->getId()));
            }
        } else {
            return array('error', 'lostPasswordReturn_failed');
        }
    }

    public function changePasswordComplete($f3, $id, $password, $forgottenPasswordCode)
    {
        $user = new user($this->fetchOneById('user', intval($id)));

        $userConnect = $this->fetchOneByKeysLike('userConnect', array(
            'userId' => intval($user->getId()),
            'project' => $f3->get('project'),
            'forgottenPasswordCode' => $forgottenPasswordCode));
        if (empty($userConnect)) {
            return array('error', 'doesnt_exist');
        }

        if (strlen($password) >= $f3->get('lenPassword')) {
            $salt = $user->getSalt();
            $password = $this->hashPassword($password, $salt);
            $this->updateByArrayById('userConnect', array('password' => $password), $userConnect['id']);
            $f3->set("SESSION.userId", $user->getId());
            return array('success');
        } else {
            return array('error', 'password_to_short');
        }
    }

    public function updateUser($f3, $userId, $userData)
    {
        $user = new user($this->fetchOneById('user', intval($userId)));
        if ($user->getId() <= 0) {
            return array('error', 'user_doesnt_exist');
        }
        if (isset($userData['email']) && intval($this->fetchOneByKeysEqual('user', array('email' => htmlspecialchars($userData['email'])))['id']) !== intval($user->getId())) {
            return array('error', 'register_subscribed');
        }
        $user->hydrate($userData);
        $this->updateByArrayById('user', $user->getClassArray(), intval($user['id']));
        return array('success', array('user_array' => $this->fetchOneById('user', intval($user->getId()))));
    }


    public function registerGroup($f3, $groupId, user $user, $refreshSession = false)
    {

        //cette méthode ajoute $user au userGroup dont l'id est $groupId
        //si mailchimp est setté dans setup.ini du project et que le grope dispose d'un segmentId alors $user est ajouté dans le segment chez Mailchimp
        $userGroup = new userGroup($this->fetchOneById('userGroup', $groupId));
        if ($this->existsValue('userPerGroup', array('userId' => $user->getId(), 'groupId' => $userGroup->getId())) === false) {
            $this->insertByArray('userPerGroup', array(
                    'userId' => $user->getId(),
                    'groupId' => $userGroup->getId())
                , []);
            if ($refreshSession) {
                //on update les permissions du user en session
                $groupRepository = new groupRepository();
                $f3->set("SESSION.permission", $groupRepository->checkPermission($f3, $user->getId()));
            }
            if ($f3->get('mailchimpMember') && ($userGroup->getSegmentId() > 0)) {
                $mailchimpManager = new mailchimpManager();
                $mailchimpManager->registerSegmentMember($f3, $userGroup->getSegmentId(), $user->getEmail());
            }
        }
    }


    public function getPermissionSession($f3)
    {

        //cette méthode retourne le niveau de permission du user en session (ou 0 si aucun user en session)
        if (($f3->get("SESSION.userId") !== -1) && ($f3->get("SESSION.userId") !== '') || (intval($f3->get("SESSION.userId")) !== 0)) {
            $groupRepository = new groupRepository();
            return $groupRepository->checkPermission($f3, $f3->get("SESSION.userId"));
        } else {
            return 0;
        }
    }


    public function logout($f3)
    {
        $f3->clear('SESSION');
        return true;
    }

    public function deleteUser($f3, $userId)
    {
        // ATTENTION, cette méthode delete complètement l'utilisateur, détruit ses paiements passés et à venir.
        // Elle le supprime de la liste mailchimp et l'efface de la bdd.
        $mailchimpManager = new mailchimpManager();
        $shoppingManager = new shoppingManager();

        $user = $this->fetchOneById('user', intval($userId));
        if (empty($user)) {
            return array('error', 'user_not_exist');
        }

        //suppression des sessions de l'utilisateur
        $this->deleteByKeysEqual('userSession', array('userId' => intval($user['id'])));
        //suppression des groupes du user
        $this->deleteByKeysEqual('userPerGroup', array('userId' => intval($user['id'])));
        //suppression de la connxion du user
        $this->deleteByKeysEqual('userConnect', array('userId' => intval($user['id'])));
        //on recupère toutes les listes mailchimp et on itère sur celles-ci pour unsubscribe proprement l'utilisateur.
        $list_array = $mailchimpManager->fetchAllList($f3);

        foreach ($list_array['lists'] as $key => $value) {
            $mailchimpManager->unsubscribeUser($f3, $user['email'], $value['id']);
        }
        // on efface les adresses de l'utilisateur
        $this->deleteByKeysEqual('userAdress', array('userId' => intval($user['id'])));
        // on efface les réponse de l'utilisateur aux questionnaires éventuels
        $this->deleteByKeysEqual('surveyReply', array('userId' => intval($user['id'])));
        // on efface les signatures électroniques de l'utilisateur
        $this->deleteByKeysEqual('signaturitEvent', array('userId' => intval($user['id'])));
        $this->deleteByKeysEqual('signaturePerUser', array('userId' => intval($user['id'])));
        //on efface les coupons utilisés par l'utilisateur
        $this->deleteByKeysEqual('shoppingUsedCouponPerUser', array('userId' => intval($user['id'])));

        //on efface les commandes de l'utilisateur.
        $order_array = $this->fetchAllByKeysLike('shoppingOrder', array('userId' => intval($user['id'])));

        foreach ($order_array as $key => $order) {
            $this->deleteByKeysEqual('shoppingOrderLine', array('orderId' => intval($order['id'])));
            $this->deleteByKeysEqual('payment', array('paymentOriginType' => 'SHOP', 'paymentOriginId' => intval($order['id'])));
        }

        // on efface les données client et les méthodes de paiement de l'utilisateur

        $customer_array = $this->fetchAllByKeysLike('paymentStripeCustomer', array('userId' => intval($user['id'])));
        foreach ($customer_array as $key => $customer){
            $this->deleteByKeysEqual('paymentStripePaymentMethod', array('customerId' => intval($customer['id'])));
            $this->deleteById('paymentStripeCustomer', intval($customer['id']));
        }

        // on efface les images de l'utilisateur

        $this->deleteByKeysEqual('imagePerUser', array('userId' => intval($user['id'])));
        // on efface les feature de l'utilisateur
        $this->deleteByKeysEqual('featurePerUser',  array('userId' => intval($user['id'])));
        // on efface les favoris de l'utilisateur
        $this->deleteByKeysEqual('favorite',  array('userId' => intval($user['id'])));
        // on efface les usages d'abonnement de l'utilisateur


        $subscription_array = $this->fetchAllByKeysLike('abonnementSubscription', array('userId' => intval($user['id'])));
        foreach ($subscription_array as $key => $subscription){
            $shoppingManager->deleteSubscription($f3, $subscription['id'], $user['id']);
        }
        $this->deleteById('user', intval($userId));

    }


    public function salt()
    {

        $raw_salt_len = 16;
        $buffer = '';
        $buffer_valid = false;
        if (function_exists('random_bytes') && !defined('PHALANGER')) {
            $buffer = random_bytes($raw_salt_len);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $buffer = openssl_random_pseudo_bytes($raw_salt_len);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && @is_readable('/dev/urandom')) {
            $f = fopen('/dev/urandom', 'r');
            $read = strlen($buffer);
            while ($read < $raw_salt_len) {
                $buffer .= fread($f, $raw_salt_len - $read);
                $read = strlen($buffer);
            }
            fclose($f);
            if ($read >= $raw_salt_len) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid || strlen($buffer) < $raw_salt_len) {
            $bl = strlen($buffer);
            for ($i = 0; $i < $raw_salt_len; $i++) {
                if ($i < $bl) {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                } else {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }
        $salt = $buffer;
        // encode string with the Base64 variant used by crypt
        $base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $base64_string = base64_encode($salt);
        $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        $salt = substr($salt, 0, $this->salt_length);
        return $salt;
    }

    private function hashPasswordDb($f3, user $user, $password, $use_sha1_override = false)
    {

        if (empty($user->getId()) || empty($password)) return false;

        $hashPasswordDb = $this->fetchOneByKeysEqual('userConnect', array('userId' => $user->getId(), 'project' => $f3->get('project')));
        // bcryptManager
        if ($use_sha1_override === false && $this->hash_method == 'bcrypt') {
            $bcrypt = new bcryptManager();
            if ($bcrypt->verify($password, $hashPasswordDb['password'])) return true;
            return false;
        }
        // sha1
        if ($this->store_salt) {
            $dbPassword = sha1($password . $hashPasswordDb->salt);
        } else {
            $salt = substr($hashPasswordDb->password, 0, $this->salt_length);
            $dbPassword = $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }
        if ($dbPassword == $hashPasswordDb->password) return true;
        return false;
    }

    private function hashPassword($password, $salt = false, $use_sha1_override = FALSE)
    {

        if (empty($password)) return false;
        // bcryptManager
        if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt') {
            $bcrypt = new bcryptManager();
            return $bcrypt->hash($password);
        }
        if ($this->store_salt && $salt) {
            return sha1($password . $salt);
        } else {
            $salt = $this->salt();
            return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }
    }

    private function setUserCookies($f3, user $user)
    {
        //on set les cookies
        $f3->set('COOKIE.id', $user->getId(), 365 * 24 * 3600, null, null, false, true);
        $f3->set('COOKIE.email', $user->getEmail(), 365 * 24 * 3600, null, null, false, true);
        $f3->set('COOKIE.token', $user->getToken(), 365 * 24 * 3600, null, null, false, true);
    }


}