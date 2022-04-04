<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:04
 */

namespace entity;


class postType
{

    private $_id;
    private $_name;
    private $_readPermission;
    private $_editPermission;
    private $_tinyScript;


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
    public function getReadPermission() { return $this->_readPermission;}
    public function setReadPermission($readPermission) { $this->_readPermission = $readPermission; }
    public function getEditPermission() { return $this->_editPermission;}
    public function setEditPermission($editPermission) { $this->_editPermission = $editPermission; }
    public function getTinyScript() { return $this->_tinyScript;}
    public function setTinyScript($tinyScript) { $this->_tinyScript = $tinyScript; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}