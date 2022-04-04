<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 09:22
 */

namespace entity;


class paymentPaypalAgreement
{

    private $_id;
    private $_paypalAgreementId;
    private $_token;
    private $_paypalBillingplanId;
    private $_success;
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
    public function getPaypalAgreementId() { return $this->_paypalAgreementId;}
    public function setPaypalAgreementId($paypalAgreementId) { $this->_paypalAgreementId = $paypalAgreementId; }
    public function getToken() { return $this->_token;}
    public function setToken($token) { $this->_token = $token; }
    public function getPaypalBillingplanId() { return $this->_paypalBillingplanId;}
    public function setPaypalBillingplanId($paypalBillingplanId) { $this->_paypalBillingplanId = $paypalBillingplanId; }
    public function getSuccess() { return $this->_success;}
    public function setSuccess($success) { $this->_success = $success; }
    public function getTimestamp() { return $this->_timestamp;}
    public function setTimestamp($timestamp) { $this->_timestamp = $timestamp; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}