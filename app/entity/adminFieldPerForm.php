<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:49
 */

namespace entity;

class adminFieldPerForm
{

    private $_id;
    private $_adminFormId;
    private $_formFieldId;


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
    public function getAdminFormId() { return $this->_adminFormId;}
    public function setAdminFormId($adminFormId) { $this->_adminFormId = $adminFormId; }
    public function getFormFieldId() { return $this->_formFieldId;}
    public function setFormFieldId($formFieldId) { $this->_formFieldId = $formFieldId; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}