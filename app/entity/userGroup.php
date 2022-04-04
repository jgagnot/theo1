<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 13:44
 */

namespace entity;


class userGroup
{

    private $_id;
    private $_name;
    private $_description;
    private $_maxUserAmount;
    private $_joinStart;
    private $_joinStop;
    private $_permission;
    private $_segmentId;


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
    public function getName() { return $this->_name;}
    public function setName($name) { $this->_name = $name; }
    public function getDescription() { return $this->_description;}
    public function setDescription($description) { $this->_description = $description; }
    public function getMaxUserAmount() { return $this->_maxUserAmount;}
    public function setMaxUserAmount($maxUserAmount) { $this->_maxUserAmount = $maxUserAmount; }
    public function getJoinStart() { return $this->_joinStart;}
    public function setJoinStart($joinStart) { $this->_joinStart = $joinStart; }
    public function getJoinStop() { return $this->_joinStop;}
    public function setJoinStop($joinStop) { $this->_joinStop = $joinStop; }
    public function getPermission() { return $this->_permission;}
    public function setPermission($permission) { $this->_permission = $permission; }
    public function getSegmentId() { return $this->_segmentId;}
    public function setSegmentId($segmentId) { $this->_segmentId = $segmentId; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}