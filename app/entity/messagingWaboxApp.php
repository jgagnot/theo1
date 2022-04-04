<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:55
 */

namespace entity;


class messagingWaboxApp
{

    private $_id;
    private $_userId;
    private $_recipient;
    private $_text;
    private $_success;
    private $_error;
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
    public function getUserId() { return $this->_userId;}
    public function setUserId($userId) { $this->_userId = $userId; }
    public function getRecipient() { return $this->_recipient;}
    public function setRecipient($recipient) { $this->_recipient = $recipient; }
    public function getText() { return $this->_text;}
    public function setText($text) { $this->_text = $text; }
    public function getSuccess() { return $this->_success;}
    public function setSuccess($success) { $this->_success = $success; }
    public function getError() { return $this->_error;}
    public function setError($error) { $this->_error = $error; }
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