<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:51
 */

namespace entity;


class shoppingShipper
{

    private $_id;
    private $_name;
    private $_flatFarePrice;
    private $_perGrammePrice;


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
    public function getFlatFarePrice() { return $this->_flatFarePrice;}
    public function setFlatFarePrice($flatFarePrice) { $this->_flatFarePrice = $flatFarePrice; }
    public function getPerGrammePrice() { return $this->_perGrammePrice;}
    public function setPerGrammePrice($perGrammePrice) { $this->_perGrammePrice = $perGrammePrice; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}