<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 10/07/2019
 * Time: 09:28
 */

namespace entity;


class pdfTemplate
{
    private $_id;
    private $_name;
    private $_templateId;
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    /**
     * @param mixed $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->_templateId = $templateId;
    }

    /**
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->_templateId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->_description;
    }

    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}