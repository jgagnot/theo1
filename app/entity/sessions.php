<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:06
 */

namespace entity;


class sessions
{

    private $_session_id;
    private $_data;
    private $_ip;
    private $_agent;
    private $_stamp;


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

    public function getSession_id() { return $this->_session_id;}
    public function setSession_id($session_id) { $this->_session_id = $session_id; }
    public function getData() { return $this->_data;}
    public function setData($data) { $this->_data = $data; }
    public function getIp() { return $this->_ip;}
    public function setIp($ip) { $this->_ip = $ip; }
    public function getAgent() { return $this->_agent;}
    public function setAgent($agent) { $this->_agent = $agent; }
    public function getStamp() { return $this->_stamp;}
    public function setStamp($stamp) { $this->_stamp = $stamp; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}