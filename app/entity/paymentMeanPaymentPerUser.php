<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 09:19
 */

namespace entity;


class paymentMeanPaymentPerUser
{

    private $_id;
    private $_userId;
    private $_meanPaymentType;
    private $_meanPaymentId;
    private $_actived;
    private $_first;


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
    public function getMeanPaymentType() { return $this->_meanPaymentType;}
    public function setMeanPaymentType($meanPaymentType) { $this->_meanPaymentType = $meanPaymentType; }
    public function getMeanPaymentId() { return $this->_meanPaymentId;}
    public function setMeanPaymentId($meanPaymentId) { $this->_meanPaymentId = $meanPaymentId; }
    public function getActived() { return $this->_actived;}
    public function setActived($actived) { $this->_actived = $actived; }
    public function getFirst() { return $this->_first;}
    public function setFirst($first) { $this->_first = $first; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}