<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:00
 */

namespace entity;


class paymentStripeSubscription
{

    private $_id;
    private $_stripeSubscriptionId;
    private $_name;
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
    public function getStripeSubscriptionId() { return $this->_stripeSubscriptionId;}
    public function setStripeSubscriptionId($stripeSubscriptionId) { $this->_stripeSubscriptionId = $stripeSubscriptionId; }
    public function getName() { return $this->_name;}
    public function setName($name) { $this->_name = $name; }
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