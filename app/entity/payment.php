<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:57
 */

namespace entity;


class payment
{

    private $_id;
    private $_userId;
    private $_paymentOriginType;
    private $_paymentOriginId;
    private $_amount;
    private $_currency;
    private $_execution;
    private $_status;
    private $_meanChargeType;
    private $_meanChargeId;
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
    public function getPaymentOriginType() { return $this->_paymentOriginType;}
    public function setPaymentOriginType($paymentOriginType) { $this->_paymentOriginType = $paymentOriginType; }
    public function getPaymentOriginId() { return $this->_paymentOriginId;}
    public function setPaymentOriginId($paymentOriginId) { $this->_paymentOriginId = $paymentOriginId; }
    public function getAmount() { return $this->_amount;}
    public function setAmount($amount) { $this->_amount = $amount; }
    public function getCurrency() { return $this->_currency;}
    public function setCurrency($currency) { $this->_currency = $currency; }
    public function getExecution() { return $this->_execution;}
    public function setExecution($execution) { $this->_execution = $execution; }
    public function getStatus() { return $this->_status;}
    public function setStatus($status) { $this->_status = $status; }
    public function getMeanChargeType() { return $this->_meanChargeType;}
    public function setMeanChargeType($meanChargeType) { $this->_meanChargeType = $meanChargeType; }
    public function getMeanChargeId() { return $this->_meanChargeId;}
    public function setMeanChargeId($meanChargeId) { $this->_meanChargeId = $meanChargeId; }
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
