<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:58
 */

namespace entity;


class signaturitEventSubscription
{

    private $_id;
    private $_subscriptionId;
    private $_event;
    private $_url;
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
    public function getSubscriptionId() { return $this->_subscriptionId;}
    public function setSubscriptionId($subscriptionId) { $this->_subscriptionId = $subscriptionId; }
    public function getEvent() { return $this->_event;}
    public function setEvent($event) { $this->_event = $event; }
    public function getUrl() { return $this->_url;}
    public function setUrl($url) { $this->_url = $url; }
    public function getTimestamp() { return $this->_timestamp;}
    public function setTimestamp($timestamp) { $this->_timestamp = $timestamp; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}