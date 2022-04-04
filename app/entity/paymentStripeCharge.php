<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 09:58
 */

namespace entity;


class paymentStripeCharge
{

    private $_id;
    private $_stripeChargeId;
    private $_amount;
    private $_currency;
    private $_paid;
    private $_failureCode;
    private $_created;
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
    public function getStripeChargeId() { return $this->_stripeChargeId;}
    public function setStripeChargeId($stripeChargeId) { $this->_stripeChargeId = $stripeChargeId; }
    public function getAmount() { return $this->_amount;}
    public function setAmount($amount) { $this->_amount = $amount; }
    public function getCurrency() { return $this->_currency;}
    public function setCurrency($currency) { $this->_currency = $currency; }
    public function getPaid() { return $this->_paid;}
    public function setPaid($paid) { $this->_paid = $paid; }
    public function getFailureCode() { return $this->_failureCode;}
    public function setFailureCode($failureCode) { $this->_failureCode = $failureCode; }
    public function getCreated() { return $this->_created;}
    public function setCreated($created) { $this->_created = $created; }
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