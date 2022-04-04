<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:49
 */

namespace entity;


class abonnementSubscription
{

    private $_id;
    private $_userId;
    private $_abonnementId;
    private $_userAdressId;
    private $_status;
    private $_timestamp;


    public function _construct($array)
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
    public function getAbonnementId() { return $this->_abonnementId;}
    public function setAbonnementId($abonnementId) { $this->_abonnementId = $abonnementId; }
    public function getUserAdressId() { return $this->_userAdressId;}
    public function setUserAdressId($userAdressId) { $this->_userAdressId = $userAdressId; }
    public function getStatus() { return $this->_status;}
    public function setStatus($status) { $this->_status = $status; }
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