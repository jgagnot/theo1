<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:57
 */

namespace entity;


class signaturitBranding
{

    private $_id;
    private $_brandingId;
    private $_name;
    private $_description;
    private $_signButton;
    private $_sendButton;
    private $_photo;
    private $_voice;
    private $_termsAndConditions;
    private $_layoutColor;
    private $_logo;
    private $_signatureColor;
    private $_textColor;
    private $_showSurveyPage;
    private $_showCsv;
    private $_showBiometricHash;
    private $_showWelcomePage;
    private $_timestamp;


    public function __construct($array)
    {
        $this->hydrate($array);
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

    public function getId() { return $this->_id;}
    public function setId($id) { $this->_id = $id; }
    public function getBrandingId() { return $this->_brandingId;}
    public function setBrandingId($brandingId) { $this->_brandingId = $brandingId; }
    public function getName() { return $this->_name;}
    public function setName($name) { $this->_name = $name; }
    public function getDescription() { return $this->_description;}
    public function setDescription($description) { $this->_description = $description; }
    public function getSignButton() { return $this->_signButton;}
    public function setSignButton($signButton) { $this->_signButton = $signButton; }
    public function getSendButton() { return $this->_sendButton;}
    public function setSendButton($sendButton) { $this->_sendButton = $sendButton; }
    public function getPhoto() { return $this->_photo;}
    public function setPhoto($photo) { $this->_photo = $photo; }
    public function getVoice() { return $this->_voice;}
    public function setVoice($voice) { $this->_voice = $voice; }
    public function getTermsAndConditions() { return $this->_termsAndConditions;}
    public function setTermsAndConditions($termsAndConditions) { $this->_termsAndConditions = $termsAndConditions; }
    public function getLayoutColor() { return $this->_layoutColor;}
    public function setLayoutColor($layoutColor) { $this->_layoutColor = $layoutColor; }
    public function getLogo() { return $this->_logo;}
    public function setLogo($logo) { $this->_logo = $logo; }
    public function getSignatureColor() { return $this->_signatureColor;}
    public function setSignatureColor($signatureColor) { $this->_signatureColor = $signatureColor; }
    public function getTextColor() { return $this->_textColor;}
    public function setTextColor($textColor) { $this->_textColor = $textColor; }
    public function getShowSurveyPage() { return $this->_showSurveyPage;}
    public function setShowSurveyPage($showSurveyPage) { $this->_showSurveyPage = $showSurveyPage; }
    public function getShowCsv() { return $this->_showCsv;}
    public function setShowCsv($showCsv) { $this->_showCsv = $showCsv; }
    public function getShowBiometricHash() { return $this->_showBiometricHash;}
    public function setShowBiometricHash($showBiometricHash) { $this->_showBiometricHash = $showBiometricHash; }
    public function getShowWelcomePage() { return $this->_showWelcomePage;}
    public function setShowWelcomePage($showWelcomePage) { $this->_showWelcomePage = $showWelcomePage; }
    public function getTimestamp() { return $this->_timestamp;}
    public function setTimestamp($timestamp) { $this->_timestamp = $timestamp; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}