<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:49
 */

namespace entity;


class shoppingOrderLine
{

    private $_id;
    private $_orderId;
    private $_itemId;
    private $_variantPerItemId;
    private $_price;
    private $_vat;
    private $_discountPrice;
    private $_couponId;
    private $_quantity;
    private $_release;
    private $_shipperId;
    private $_shipped;


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
    public function getOrderId() { return $this->_orderId;}
    public function setOrderId($orderId) { $this->_orderId = $orderId; }
    public function getItemId() { return $this->_itemId;}
    public function setItemId($itemId) { $this->_itemId = $itemId; }
    public function getVariantPerItemId() { return $this->_variantPerItemId;}
    public function setVariantPerItemId($variantPerItemId) { $this->_variantPerItemId = $variantPerItemId; }
    public function getPrice() { return $this->_price;}
    public function setPrice($price) { $this->_price = $price; }
    public function getVat() { return $this->_vat;}
    public function setVat($vat) { $this->_vat = $vat; }
    public function getDiscountPrice() { return $this->_discountPrice;}
    public function setDiscountPrice($discountPrice) { $this->_discountPrice = $discountPrice; }
    public function getCouponId() { return $this->_couponId;}
    public function setCouponId($couponId) { $this->_couponId = $couponId; }
    public function getQuantity() { return $this->_quantity;}
    public function setQuantity($quantity) { $this->_quantity = $quantity; }
    public function getRelease() { return $this->_release;}
    public function setRelease($release) { $this->_release = $release; }
    public function getShipperId() { return $this->_shipperId;}
    public function setShipperId($shipperId) { $this->_shipperId = $shipperId; }
    public function getShipped() { return $this->_shipped;}
    public function setShipped($shipped) { $this->_shipped = $shipped; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}