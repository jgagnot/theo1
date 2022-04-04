<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 24/04/2019
 * Time: 14:22
 */

namespace entity;


class shoppingPricePerReference
{

    private $_id;
    private $_referenceId;
    private $_storeId;
    private $_currency;
    private $_price;
    private $_HTPrice;
    private $_promo;
    private $_HTDiscount;
    private $_discountPrice;
    private $_vat;
    private $_active;
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
    public function getCurrency() { return $this->_currency;}
    public function setCurrency($currency) { $this->_currency = $currency; }
    public function getPrice() { return $this->_price;}
    public function setPrice($price) { $this->_price = $price; }
    public function getDiscountPrice() { return $this->_discountPrice;}
    public function setDiscountPrice($discountPrice) { $this->_discountPrice = $discountPrice; }
    public function getVat() { return $this->_vat;}
    public function setVat($vat) { $this->_vat = $vat; }
    public function getHTDiscount(){return $this->_HTDiscount;}
    public function setHTDiscount($HTDiscount){$this->_HTDiscount = $HTDiscount;}
    public function getHTPrice(){return $this->_HTPrice;}
    public function setHTPrice($HTPrice){$this->_HTPrice = $HTPrice;}

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->_active = $active;
    }

    /**
     * @return mixed
     */
    public function getPromo()
    {
        return $this->_promo;
    }

    /**
     * @param mixed $promo
     */
    public function setPromo($promo)
    {
        $this->_promo = $promo;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}