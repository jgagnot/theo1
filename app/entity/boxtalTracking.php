<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 26/11/2019
 * Time: 15:26
 */

namespace entity;

class boxtalTracking
{

    private $_id;
    private $_text;
    private $_localisation;
    private $_state;
    private $_boxtalShippingId;
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
    public function getText() { return $this->_text;}
    public function setText($text) { $this->_text = $text; }
    public function getLocalisation() { return $this->_localisation;}
    public function setLocalisation($localisation) { $this->_localisation = $localisation; }
    public function getState() { return $this->_state;}
    public function setState($state) { $this->_state = $state; }
    public function getBoxtalShippingId() { return $this->_boxtalShippingId;}
    public function setBoxtalShippingId($boxtalShippingId) { $this->_boxtalShippingId = $boxtalShippingId; }
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