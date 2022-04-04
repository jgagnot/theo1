<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:34
 */

namespace entity;


class shoppingItemPerStore
{

    private $_id;
    private $_storeId;
    private $_itemId;
    private $_saleStart;
    private $_saleStop;


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
    public function getStoreId() { return $this->_storeId;}
    public function setStoreId($storeId) { $this->_storeId = $storeId; }
    public function getItemId() { return $this->_itemId;}
    public function setItemId($itemId) { $this->_itemId = $itemId; }
    public function getSaleStart() { return $this->_saleStart;}
    public function setSaleStart($saleStart) { $this->_saleStart = $saleStart; }
    public function getSaleStop() { return $this->_saleStop;}
    public function setSaleStop($saleStop) { $this->_saleStop = $saleStop; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}