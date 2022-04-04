<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:53
 */

namespace entity;

class image
{

    private $_id;
    private $_name;
    private $_description;
    private $_tag;
    private $_path;
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
    public function getName() { return $this->_name;}
    public function setName($name) { $this->_name = $name; }
    public function getDescription() { return $this->_description;}
    public function setDescription($description) { $this->_description = $description; }
    public function getTag() { return $this->_tag;}
    public function setTag($tag) { $this->_tag = $tag; }
    public function getPath() { return $this->_path;}
    public function setPath($path) { $this->_path = $path; }
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