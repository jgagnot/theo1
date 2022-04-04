<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:55
 */

namespace entity;


class shoppingWishList
{

    private $_id;
    private $_userId;
    private $_itemId;
    private $_couponId;
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
    public function getUserId() { return $this->_userId;}
    public function setUserId($userId) { $this->_userId = $userId; }
    public function getItemId() { return $this->_itemId;}
    public function setItemId($itemId) { $this->_itemId = $itemId; }
    public function getCouponId() { return $this->_couponId;}
    public function setCouponId($couponId) { $this->_couponId = $couponId; }
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