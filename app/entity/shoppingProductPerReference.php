<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 24/04/2019
 * Time: 14:26
 */

namespace entity;


class shoppingProductPerReference
{

    private $_id;
    private $_referenceId;
    private $_productId;
    private $_productQuantity;
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
    public function getReferenceId() { return $this->_referenceId;}
    public function setReferenceId($referenceId) { $this->_referenceId = $referenceId; }
    public function getProductId() { return $this->_productId;}
    public function setProductId($productId) { $this->_productId = $productId; }
    public function getProductQuantity() { return $this->_productQuantity;}
    public function setProductQuantity($productQuantity) { $this->_productQuantity = $productQuantity; }
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