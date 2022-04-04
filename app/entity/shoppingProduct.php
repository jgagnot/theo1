<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:50
 */

namespace entity;

class shoppingProduct
{

    private $_id;
    private $_name;
    private $_stock;
    private $_quantity;
    private $_overstock;
    private $_weight;
    private $_size;
    private $_physical;
    private $_immaterial;
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
    public function getName() { return $this->_name;}
    public function setName($name) { $this->_name = $name; }
    public function getStock() { return $this->_stock;}
    public function setStock($stock) { $this->_stock = $stock; }
    public function getQuantity() { return $this->_quantity;}
    public function setQuantity($quantity) { $this->_quantity = $quantity; }
    public function getOverstock() { return $this->_overstock;}
    public function setOverstock($overstock) { $this->_overstock = $overstock; }
    public function getWeight() { return $this->_weight;}
    public function setWeight($weight) { $this->_weight = $weight; }
    public function getSize() { return $this->_size;}
    public function setSize($size) { $this->_size = $size; }
    public function getPhysical() { return $this->_physical;}
    public function setPhysical($physical) { $this->_physical = $physical; }
    public function getVirtual() { return $this->_immaterial;}
    public function setVirtual($immaterial) { $this->_immaterial = $immaterial; }
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