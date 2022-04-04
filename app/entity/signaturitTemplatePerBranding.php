<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 11:00
 */

namespace entity;


class signaturitTemplatePerBranding
{

    private $_id;
    private $_brandingId;
    private $_templateId;
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
    public function getBrandingId() { return $this->_brandingId;}
    public function setBrandingId($brandingId) { $this->_brandingId = $brandingId; }
    public function getTemplateId() { return $this->_templateId;}
    public function setTemplateId($templateId) { $this->_templateId = $templateId; }
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