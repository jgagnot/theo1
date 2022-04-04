<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 26/11/2019
 * Time: 15:25
 */

namespace entity;

class boxtalShipping
{

    private $_id;
    private $_orderId;
    private $_emcReference;
    private $_carrierReference;
    private $_status;
    private $_labelAvailable;
    private $_labelUrl;
    private $_urlPush;
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
    public function getOrderId() { return $this->_orderId;}
    public function setOrderId($orderId) { $this->_orderId = $orderId; }
    public function getEmcReference() { return $this->_emcReference;}
    public function setEmcReference($emcReference) { $this->_emcReference = $emcReference; }
    public function getCarrierReference() { return $this->_carrierReference;}
    public function setCarrierReference($carrierReference) { $this->_carrierReference = $carrierReference; }
    public function getStatus() { return $this->_status;}
    public function setStatus($status) { $this->_status = $status; }
    public function getLabelAvailable() { return $this->_labelAvailable;}
    public function setLabelAvailable($labelAvailable) { $this->_labelAvailable = $labelAvailable; }
    public function getLabelUrl() { return $this->_labelUrl;}
    public function setLabelUrl($labelUrl) { $this->_labelUrl = $labelUrl; }
    public function getUrlPush() { return $this->_urlPush;}
    public function setUrlPush($urlPush) { $this->_urlPush = $urlPush; }
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