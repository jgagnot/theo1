<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 12/03/2018
 * Time: 13:16
 */

namespace service\user;

use core\imi;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Authentication\AccessTokenMetadata;
use entity\user;
use Google_Client;


class oauthManager extends imi
{
    private function generateState(){
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($chars);
        $state = '';
        for ($i = 0; $i < 20; $i++) {
            $state .= $chars[rand(0, $charactersLength - 1)];
        }
        return hash('whirlpool', $state);
    }

    public function facebookLogin($f3){
        $fb = new Facebook(array(
            'app_id'=>$f3->get('facebookId'),
            'app_secret'=>$f3->get('facebookSecret'),
            'default_graph_version'=>'v2.12'));

      $helper = $fb->getRedirectLoginHelper();

      $loginUrl= $helper->getLoginUrl($f3->get('path').'facebookCallback', ['email']);

      header('Location: '.$loginUrl);
    }

    public function facebookCallBack($f3){
        $manager = new authentificationManager();

        $fb = new Facebook(array(
            'app_id'=>$f3->get('facebookId'),
            'app_secret'=>$f3->get('facebookSecret'),
            'default_graph_version'=>'v2.12'));

        $helper = $fb->getRedirectLoginHelper();

        if (isset($_GET['state'])) {
            $helper->getPersistentDataHandler()->set('state', $_GET['state']);
        }


        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        $oAuth2Client = $fb->getOAuth2Client();

        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($f3->get('facebookId'));
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }
        }

        try{
            $response = $fb->get('/me?fields=id,name,first_name,location,email', $accessToken);
        }catch (FacebookResponseException $e){
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $response->getGraphUser();

        if (($userData = $this->fetchOneByKeysEqual('user', array('facebookId' => $user->getId()))) != null) {
            return $manager->loginComplete($f3, new user($userData));
        }
         else if (($userData = $this->fetchOneByKeysEqual('user', array('email' => $user->getEmail()))) != null) {
            $this->updateByArrayById('user',array('facebookId' => $user->getId()),$userData['id']);
            return $manager->loginComplete($f3, new user($userData));
         }
        else {
            return ($manager->register($f3,'facebookId', array(
                'facebookId' => $user->getId(),
                'lastname' => $user->getName(),
                'firstname' => $user->getFirstName(),
                'email' => $user->getEmail()
            ), '', 1));
        }
    }

    public function googleLogin($f3)
    {
        $client = new Google_Client();
        $redirect_uri = $f3->get('path').'googleCallback';

        $client->setAuthConfig('vendor/google/apiclient/src/Google/client_secret.json');
        $client->setIncludeGrantedScopes(true);
        $client->addScope('profile');
        $client->addScope('email');
        $client->addScope('https://www.googleapis.com/auth/plus.login');
        $client->addScope('https://www.googleapis.com/auth/plus.me');
        $client->setRedirectUri($redirect_uri);
        $apiKey = 'AIzaSyDLdjj9X-Nbo3jSlJTc3SOxbpQ07xVNg24';

        $client->setDeveloperKey($apiKey);
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url));
    }

    public function googleCallback($f3, $datas)
    {
        $manager = new authentificationManager();

        if (!isset($datas['code']))
            exit;
        $client = new Google_Client();
        $redirect_uri = $f3->get('path').'googleCallback';

        $client->setAuthConfig('vendor/google/apiclient/src/Google/client_secret.json');
        $client->setRedirectUri($redirect_uri);
        $client->authenticate($datas['code']);
        $token = $client->getAccessToken();
        $client->setAccessToken($token);

        $service = new \Google_Service_Plus($client);
        $user = $service->people->get('me');

        if (($userData = $this->fetchOneByKeysEqual('user', array('googleId' => $user->getId()))) != null)
            return $manager->loginComplete($f3, new user($userData));
        else if (($userData = $this->fetchOneByKeysEqual('user', array('email' => $user->getEmails()[0]->getvalue()))) != null) {
            $this->updateByArrayById('user', array('googleId' => $user->getId()), $userData['id']);
            return $manager->loginComplete($f3, new user($userData));
        }
        else
            return ($manager->register($f3,'googleId', array(
                'googleId' => $user->getId(),
                'lastname' =>$user->getName()->getFamilyName(),
                'firstname' => $user->getName()->getGivenName(),
                'email' => $user->getEmails()[0]->getvalue()
            ),'',1));
    }


    public function linkedinLogin($f3){
        session_start();
        $_SESSION['state'] = $this->generateState();

        header('Location: https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id='.$f3->get('linkedinId').'&redirect_uri='.$f3->get('path').'linkedinCallback&state='.$_SESSION['state']);
    }

    public function linkedinCallback($f3, $datas){
        session_start();
        if ($datas['state'] !== $_SESSION['state'])
            exit ;
        if (isset($datas['error']))
            header('Location: '.$f3->get('path'));
        $query = http_build_query(array(
            'grant_type' => 'authorization_code',
            'code' => $datas['code'],
            'redirect_uri' => $f3->get('path').'linkedinCallback',
            'client_id' => $f3->get('linkedinId'),
            'client_secret' => $f3->get('linkedinSecret')
            ));


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.linkedin.com/oauth/v2/accessToken');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Host: www.linkedin.com'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($ch);
        return $this->getLinkedinProfile($f3, json_decode($res, true));
    }

    private function getLinkedinProfile($f3, $token){
        $manager = new authentificationManager();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.linkedin.com/v1/people/~:(id,email-address,first-name,last-name)?format=json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: api.linkedin.com', 'Connection: Keep-Alive', 'Authorization: Bearer '.$token['access_token']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $user = json_decode(curl_exec($ch), true);

        if (($userData = $this->fetchOneByKeysEqual('user', array('linkedinId' => $user['id']))) != null)
            return $manager->loginComplete($f3, new user($userData));
        else if (($userData = $this->fetchOneByKeysEqual('user', array('email' => $user['emailAddress']))) != null) {
            $this->updateByArrayById('user',array('linkedinId' => $user['id']),$userData['id']);
            return $manager->loginComplete($f3, new user($userData));
        }
        else
            return ($manager->register($f3, 'linkedinId',array(
                'linkedinId' => $user['id'],
                'lastname' =>$user['lastName'],
                'firstname' => $user['firstName'],
                'email' => $user['emailAddress']
            ), '', 1));
    }
}
