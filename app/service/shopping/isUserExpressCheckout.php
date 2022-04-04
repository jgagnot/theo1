<?php
/**
 * Created by PhpStorm.
 * User: nicolasdelourme
 * Date: 13/02/2018
 * Time: 22:43
 */

namespace service\shopping;


class isUserExpressCheckout
{
    private $_isUserExpressCheckout;
    private $_activedMeanPaymentId;
    private $_activedUserAdressId;

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

    public function getIsUserExpressCheckout() { return $this->_isUserExpressCheckout; }
    public function setIsUserExpressCheckout($isUserExpressCheckout) { $this->_isUserExpressCheckout = $isUserExpressCheckout; }
    public function getActivedMeanPaymentId() { return $this->_activedMeanPaymentId; }
    public function setActivedMeanPaymentId($activedMeanPaymentId) { $this->_activedMeanPaymentId = $activedMeanPaymentId; }
    public function getActivedUserAdressId() { return $this->_activedUserAdressId; }
    public function setActivedUserAdressId($activedUserAdressId) { $this->_activedUserAdressId = $activedUserAdressId; }

    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }

}