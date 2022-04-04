<?php
/**
 * Created by Imi Creative
 * User: nicolasdelourme
 * Date: 12/12/2019
 * Time: 15:25
 */

namespace entity;

class abonnementPerStore
{

    private $_id;
    private $_storeId;
    private $_abonnementId;
    private $_visible;
    private $_actived;
    private $_timestamp;


    public function __construct($array)
    {
        $this->hydrate($array);
    }

    public function hydrate(array $array)
    {
        foreach ($array as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    public function getAbonnementId()
    {
        return $this->_abonnementId;
    }

    public function setAbonnementId($abonnementId)
    {
        $this->_abonnementId = $abonnementId;
    }

    public function getVisible()
    {
        return $this->_visible;
    }

    public function setVisible($visible)
    {
        $this->_visible = $visible;
    }

    public function getActived()
    {
        return $this->_actived;
    }

    public function setActived($actived)
    {
        $this->_actived = $actived;
    }

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }


    function getClassArray()
    {
        $array = array();
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }
}