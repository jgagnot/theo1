<?php
/**
 * Created by Imi Creative
 * User: nicolasdelourme
 * Date: 19/07/2019
 * Time: 11:31
 */
namespace entity;
class userTracking
{
    private $_id;
    private $_origin;
    private $_referrer;
    private $_ipClient;
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
    public function getOrigin() { return $this->_origin;}
    public function setOrigin($origin) { $this->_origin = $origin; }
    public function getReferrer() { return $this->_referrer;}
    public function setReferrer($referrer) { $this->_referrer = $referrer; }
    public function getIp_client() { return $this->_ipClient;}
    public function setIp_client($ipClient) { $this->_ipClient = $ipClient; }
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