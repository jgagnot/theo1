<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:52
 */

namespace entity;


class shoppingStore
{

    private $_id;
    private $_name;
    private $_start;
    private $_stop;
    private $_countdown;
    private $_firstShipping;
    private $_deferredShipping;
    private $_numberPayment;
    private $_firstPayment;
    private $_deferredPayment;
    private $_meanPayment;
    private $_stockActived;
    private $_currency;
    private $_view;
    private $_wishListActived;


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
    public function getStart() { return $this->_start;}
    public function setStart($start) { $this->_start = $start; }
    public function getStop() { return $this->_stop;}
    public function setStop($stop) { $this->_stop = $stop; }
    public function getCountdown() { return $this->_countdown;}
    public function setCountdown($countdown) { $this->_countdown = $countdown; }
    public function getFirstShipping() { return $this->_firstShipping;}
    public function setFirstShipping($firstShipping) { $this->_firstShipping = $firstShipping; }
    public function getDeferredShipping() { return $this->_deferredShipping;}
    public function setDeferredShipping($deferredShipping) { $this->_deferredShipping = $deferredShipping; }
    public function getNumberPayment() { return $this->_numberPayment;}
    public function setNumberPayment($numberPayment) { $this->_numberPayment = $numberPayment; }
    public function getFirstPayment() { return $this->_firstPayment;}
    public function setFirstPayment($firstPayment) { $this->_firstPayment = $firstPayment; }
    public function getDeferredPayment() { return $this->_deferredPayment;}
    public function setDeferredPayment($deferredPayment) { $this->_deferredPayment = $deferredPayment; }
    public function getMeanPayment() { return $this->_meanPayment;}
    public function setMeanPayment($meanPayment) { $this->_meanPayment = $meanPayment; }
    public function getStockActived() { return $this->_stockActived;}
    public function setStockActived($stockActived) { $this->_stockActived = $stockActived; }
    public function getCurrency() { return $this->_currency;}
    public function setCurrency($currency) { $this->_currency = $currency; }
    public function getView() { return $this->_view;}
    public function setView($view) { $this->_view = $view; }
    public function getWishListActived() { return $this->_wishListActived;}
    public function setWishListActived($wishListActived) { $this->_wishListActived = $wishListActived; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}