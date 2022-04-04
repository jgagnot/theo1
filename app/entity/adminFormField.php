<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:51
 */

namespace entity;


class adminFormField
{

    private $_id;
    private $_name;
    private $_field;
    private $_defaultValue;
    private $_type;
    private $_style;
    private $_required;
    private $_disabled;
    private $_hidden;


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
    public function getField() { return $this->_field;}
    public function setField($field) { $this->_field = $field; }
    public function getDefaultValue() { return $this->_defaultValue;}
    public function setDefaultValue($defaultValue) { $this->_defaultValue = $defaultValue; }
    public function getType() { return $this->_type;}
    public function setType($type) { $this->_type = $type; }
    public function getStyle() { return $this->_style;}
    public function setStyle($style) { $this->_style = $style; }
    public function getRequired() { return $this->_required;}
    public function setRequired($required) { $this->_required = $required; }
    public function getDisabled() { return $this->_disabled;}
    public function setDisabled($disabled) { $this->_disabled = $disabled; }
    public function getHidden() { return $this->_hidden;}
    public function setHidden($hidden) { $this->_hidden = $hidden; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}