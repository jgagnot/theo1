<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 09:59
 */

namespace entity;


class paymentStripeSource
{

    private $_id;
    private $_stripeId;
    private $_stripeCustomerId;
    private $_currency;
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
    public function getStripeId() { return $this->_stripeId;}
    public function setStripeId($stripeId) { $this->_stripeId = $stripeId; }
    public function getStripeCustomerId() { return $this->_stripeCustomerId;}
    public function setStripeCustomerId($stripeCustomerId) { $this->_stripeCustomerId = $stripeCustomerId; }
    public function getCurrency() { return $this->_currency;}
    public function setCurrency($currency) { $this->_currency = $currency; }
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
