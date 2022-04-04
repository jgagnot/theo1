<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 09:29
 */

namespace entity;


class paymentPaypalBillingPlan
{

    private $_id;
    private $_paypalId;
    private $_name;
    private $_type;
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
    public function getPaypalId() { return $this->_paypalId;}
    public function setPaypalId($paypalId) { $this->_paypalId = $paypalId; }
    public function getName() { return $this->_name;}
    public function setName($name) { $this->_name = $name; }
    public function getType() { return $this->_type;}
    public function setType($type) { $this->_type = $type; }
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