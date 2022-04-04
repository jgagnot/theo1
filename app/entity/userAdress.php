<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 13:43
 */

namespace entity;


class userAdress
{

    private $_id;
    private $_userId;
    private $_firstName;
    private $_lastname;
    private $_recipient;
    private $_line1;
    private $_line2;
    private $_zipcode;
    private $_city;
    private $_country;
    private $_mainAdress;
    private $_actived;
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
    public function getFirstName() { return $this->_firstName;}
    public function setFirstName($firstName) { $this->_firstName = $firstName; }
    public function getLastname() { return $this->_lastname;}
    public function setLastname($lastname) { $this->_lastname = $lastname; }
    public function getRecipient() { return $this->_recipient;}
    public function setRecipient($recipient) { $this->_recipient = $recipient; }
    public function getLine1() { return $this->_line1;}
    public function setLine1($line1) { $this->_line1 = $line1; }
    public function getLine2() { return $this->_line2;}
    public function setLine2($line2) { $this->_line2 = $line2; }
    public function getZipcode() { return $this->_zipcode;}
    public function setZipcode($zipcode) { $this->_zipcode = $zipcode; }
    public function getCity() { return $this->_city;}
    public function setCity($city) { $this->_city = $city; }
    public function getCountry() { return $this->_country;}
    public function setCountry($country) { $this->_country = $country; }
    public function getMainAdress() { return $this->_mainAdress;}
    public function setMainAdress($mainAdress) { $this->_mainAdress = $mainAdress; }
    public function getActived() { return $this->_actived;}
    public function setActived($actived) { $this->_actived = $actived; }
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