<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:56
 */

namespace entity;


class signaturePerUser
{

    private $_id;
    private $_userId;
    private $_signaturitSourceId;
    private $_signaturitId;
    private $_iframeUrl;
    private $_signatureStatus;
    private $_signerStatus;
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
    public function getUserId() { return $this->_userId;}
    public function setUserId($userId) { $this->_userId = $userId; }
    public function getSignaturitSourceId() { return $this->_signaturitSourceId;}
    public function setSignaturitSourceId($signaturitSourceId) { $this->_signaturitSourceId = $signaturitSourceId; }
    public function getSignaturitId() { return $this->_signaturitId;}
    public function setSignaturitId($signaturitId) { $this->_signaturitId = $signaturitId; }
    public function getIframeUrl() { return $this->_iframeUrl;}
    public function setIframeUrl($iframeUrl) { $this->_iframeUrl = $iframeUrl; }
    public function getSignatureStatus() { return $this->_signatureStatus;}
    public function setSignatureStatus($signatureStatus) { $this->_signatureStatus = $signatureStatus; }
    public function getSignerStatus() { return $this->_signerStatus;}
    public function setSignerStatus($signerStatus) { $this->_signerStatus = $signerStatus; }
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