<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:31
 */
namespace entity;


class user
{
    // id de l'utilisateur.
    private $id;

    // id facebook, fourni par facebook et servant a identifier l'utilisateur chez facebook (renseigne dans le oauthManager) . Permet le login via facebook.
    private $facebookId;

    // id google, fourni par google et servant a identifier l'utilisateur via google+ (renseigne dans le oauthManager). Permet le login via google.
    private $googleId;

    // id linkedin, fourni par linkedin et servant a identifier l'utilisateur via linkedin (renseigne dans le oauthManager). Permet le login via linkedin.
    private $linkedinId;

    // adresse ip de l'utilisateur au moment de son dernier login.
    private $ipAddress;


    private $title;

    // prenom.
    private $firstname;

    // nom de famille.
    private $lastname;

    // mot de passe de l'utilisateur, chiffre. Renseigne au moment du register (si un mot de passe est requis dans le projet).
    private $password;

    // salt, permettant d'augmenter la securite du chiffrement du mot de passe.
    private $salt;

    // email de l'utilisateur.
    private $email;

    private $phone;

    //token cree au moment du register. Permet divers traitements en fonction du projet (par exemple, peut etre insere dans une url pour identifier l'utilisateur). Est associe a l'utilisateur chez mailchimp lors de l'enregistrement de l'utilisateur (si mailchimp est utilise).
    private $token;

    //tag libre.
    private $tag;

    // statut de l'utilisateur chez mailchimp (si mailchimp est utilise). Peut prendre les valeurs pending (en attente de double optin), subscribed (double optin valide ou mail enregistre si le double optin n'est pas exige dans le projet) ou unsubscribed.
    private $doubleOptin;

    // code genere aleatoirement et permettant le doubleOptin via des methodes "maison" (sans utilisation de mailchimp). Deconseille.
    private $doubleOptinCode;

    //code genere aleatoirement lors de la demande par l'utilisateur d'un envoi de mail de reinitialisation du mot de passe.
    private $forgottenPasswordCode;

    // temps de validite du mail de reinitialisation du mot de passe.
    private $forgottenPasswordTime;

    // indice sette par l'utilisateur pour retrouver son mot de passe.
    private $rememberCode;

    // date de creation de l'utilisateur.
    private $createdOn;

    // date de derniere connection de l'utilisateur.
    private $lastLogin;

    // origine de l'utilisateur, enregistre lors de sa premiere connexion.
    private $origin;


    public function __construct($array)
    {
        if (!empty($array)) {
            $this->hydrate($array);
        }
    }

    public function hydrate(array $array)
    {
        foreach ($array as $key => $value)
        {
            $method = 'set'.ucfirst($key);
            if (method_exists($this, $method))
            {
                $this->$method($value);
            }
        }
    }

    public function getId() { return $this->id;}
    public function setId($id) { $this->id = $id; }
    public function getFacebookId() { return $this->facebookId;}
    public function setFacebookId($facebookId) { $this->facebookId = $facebookId; }
    public function getGoogleId() { return $this->googleId;}
    public function setGoogleId($googleId) { $this->googleId = $googleId; }
    public function getLinkedinId() { return $this->linkedinId;}
    public function setLinkedinId($linkedinId) { $this->linkedinId = $linkedinId; }
    public function getIpAddress() { return $this->ipAddress;}
    public function setIpAddress($ipAddress) { $this->ipAddress = $ipAddress; }
    public function getFirstname() { return $this->firstname;}
    public function setFirstname($firstname) { $this->firstname = $firstname; }
    public function getLastname() { return $this->lastname;}
    public function setLastname($lastname) { $this->lastname = $lastname; }
    public function getPassword() { return $this->password;}
    public function setPassword($password) { $this->password = $password; }
    public function getSalt() { return $this->salt;}
    public function setSalt($salt) { $this->salt = $salt; }
    public function getEmail() { return $this->email;}
    public function setEmail($email) { $this->email = $email; }
    public function getToken() { return $this->token;}
    public function setToken($token) { $this->token = $token; }
    public function getTag() { return $this->tag;}
    public function setTag($tag) { $this->tag = $tag; }
    public function getDoubleOptin() { return $this->doubleOptin;}
    public function setDoubleOptin($doubleOptin) { $this->doubleOptin = $doubleOptin; }
    public function getDoubleOptinCode() { return $this->doubleOptinCode;}
    public function setDoubleOptinCode($doubleOptinCode) { $this->doubleOptinCode = $doubleOptinCode; }
    public function getForgottenPasswordCode() { return $this->forgottenPasswordCode;}
    public function setForgottenPasswordCode($forgottenPasswordCode) { $this->forgottenPasswordCode = $forgottenPasswordCode; }
    public function getForgottenPasswordTime() { return $this->forgottenPasswordTime;}
    public function setForgottenPasswordTime($forgottenPasswordTime) { $this->forgottenPasswordTime = $forgottenPasswordTime; }
    public function getRememberCode() { return $this->rememberCode;}
    public function setRememberCode($rememberCode) { $this->rememberCode = $rememberCode; }
    public function getCreatedOn() { return $this->createdOn;}
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; }
    public function getLastLogin() { return $this->lastLogin;}
    public function setLastLogin($lastLogin) { $this->lastLogin = $lastLogin; }
    public function getOrigin() { return $this->origin;}
    public function setOrigin($origin) { $this->origin = $origin; }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}