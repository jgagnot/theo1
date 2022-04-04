<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 26/11/2019
 * Time: 15:17
 */

namespace entity;

class boxtalPackage
{

    private $_id;
    private $_contentType;
    private $_weight;
    private $_width;
    private $_length;
    private $_height;
    private $_quantity;
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
    public function getContentType() { return $this->_contentType;}
    public function setContentType($contentType) { $this->_contentType = $contentType; }
    public function getWeight() { return $this->_weight;}
    public function setWeight($weight) { $this->_weight = $weight; }
    public function getWidth() { return $this->_width;}
    public function setWidth($width) { $this->_width = $width; }
    public function getLength() { return $this->_length;}
    public function setLength($length) { $this->_length = $length; }
    public function getHeight() { return $this->_height;}
    public function setHeight($height) { $this->_height = $height; }
    public function getQuantity() { return $this->_quantity;}
    public function setQuantity($quantity) { $this->_quantity = $quantity; }
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