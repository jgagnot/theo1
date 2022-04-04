<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:23
 */

namespace entity;


class shoppingCoupon
{

    private $_id;
    private $_code;
    private $_storeId;
    private $_start;
    private $_end;
    private $_reusable;
    private $_freeItem;
    private $_storeUniquePrice;
    private $_storePercentage;
    private $_firstFree;
    private $_firstDiscountPercentage;
    private $_oneFreeEachQuantity;
    private $_oneDiscountEachQuantity;
    private $_oneDiscountEachPercentage;
    private $_userId;
    private $_userGroupId;


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
    public function getCode() { return $this->_code;}
    public function setCode($code) { $this->_code = $code; }
    public function getStoreId() { return $this->_storeId;}
    public function setStoreId($storeId) { $this->_storeId = $storeId; }
    public function getStart() { return $this->_start;}
    public function setStart($start) { $this->_start = $start; }
    public function getEnd() { return $this->_end;}
    public function setEnd($end) { $this->_end = $end; }
    public function getReusable() { return $this->_reusable;}
    public function setReusable($reusable) { $this->_reusable = $reusable; }
    public function getFreeItem() { return $this->_freeItem;}
    public function setFreeItem($freeItem) { $this->_freeItem = $freeItem; }
    public function getStoreUniquePrice() { return $this->_storeUniquePrice;}
    public function setStoreUniquePrice($storeUniquePrice) { $this->_storeUniquePrice = $storeUniquePrice; }
    public function getStorePercentage() { return $this->_storePercentage;}
    public function setStorePercentage($storePercentage) { $this->_storePercentage = $storePercentage; }
    public function getFirstFree() { return $this->_firstFree;}
    public function setFirstFree($firstFree) { $this->_firstFree = $firstFree; }
    public function getFirstDiscountPercentage() { return $this->_firstDiscountPercentage;}
    public function setFirstDiscountPercentage($firstDiscountPercentage) { $this->_firstDiscountPercentage = $firstDiscountPercentage; }
    public function getOneFreeEachQuantity() { return $this->_oneFreeEachQuantity;}
    public function setOneFreeEachQuantity($oneFreeEachQuantity) { $this->_oneFreeEachQuantity = $oneFreeEachQuantity; }
    public function getOneDiscountEachQuantity() { return $this->_oneDiscountEachQuantity;}
    public function setOneDiscountEachQuantity($oneDiscountEachQuantity) { $this->_oneDiscountEachQuantity = $oneDiscountEachQuantity; }
    public function getOneDiscountEachPercentage() { return $this->_oneDiscountEachPercentage;}
    public function setOneDiscountEachPercentage($oneDiscountEachPercentage) { $this->_oneDiscountEachPercentage = $oneDiscountEachPercentage; }
    public function getUserId() { return $this->_userId;}
    public function setUserId($userId) { $this->_userId = $userId; }
    public function getUserGroupId() { return $this->_userGroupId;}
    public function setUserGroupId($userGroupId) { $this->_userGroupId = $userGroupId; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}