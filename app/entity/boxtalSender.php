<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 26/11/2019
 * Time: 15:19
 */

namespace entity;

class boxtalSender
{

    private $_id;
    private $_collectDate;
    private $_operator;
    private $_service;
    private $_collection;
    private $_collectionDropoff;
    private $_delivery;
    private $_deliveryDropoff;
    private $_HTPrice;
    private $_TTCPrice;
    private $_boxtalShippingId;
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
    public function getCollectDate() { return $this->_collectDate;}
    public function setCollectDate($collectDate) { $this->_collectDate = $collectDate; }
    public function getOperator() { return $this->_operator;}
    public function setOperator($operator) { $this->_operator = $operator; }
    public function getService() { return $this->_service;}
    public function setService($service) { $this->_service = $service; }
    public function getCollection() { return $this->_collection;}
    public function setCollection($collection) { $this->_collection = $collection; }
    public function getCollectionDropoff() { return $this->_collectionDropoff;}
    public function setCollectionDropoff($collectionDropoff) { $this->_collectionDropoff = $collectionDropoff; }
    public function getDelivery() { return $this->_delivery;}
    public function setDelivery($delivery) { $this->_delivery = $delivery; }
    public function getDeliveryDropoff() { return $this->_deliveryDropoff;}
    public function setDeliveryDropoff($deliveryDropoff) { $this->_deliveryDropoff = $deliveryDropoff; }
    public function getHTPrice() { return $this->_HTPrice;}
    public function setHTPrice($HTPrice) { $this->_HTPrice = $HTPrice; }
    public function getTTCPrice() { return $this->_TTCPrice;}
    public function setTTCPrice($TTCPrice) { $this->_TTCPrice = $TTCPrice; }
    public function getBoxtalShippingId() { return $this->_boxtalShippingId;}
    public function setBoxtalShippingId($boxtalShippingId) { $this->_boxtalShippingId = $boxtalShippingId; }
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