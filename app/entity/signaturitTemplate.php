<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:59
 */

namespace entity;


class signaturitTemplate
{

    private $_id;
    private $_type;
    private $_content;
    private $_description;
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
    public function getType() { return $this->_type;}
    public function setType($type) { $this->_type = $type; }
    public function getContent() { return $this->_content;}
    public function setContent($content) { $this->_content = $content; }
    public function getDescription() { return $this->_description;}
    public function setDescription($description) { $this->_description = $description; }
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