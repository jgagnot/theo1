<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 18/04/2019
 * Time: 17:50
 */

namespace entity;


class adminForm
{

    private $_id;
    private $_name;
    private $_sort;
    private $_links;
    private $_formList;


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
    public function getSort() { return $this->_sort;}
    public function setSort($sort) { $this->_sort = $sort; }
    public function getLinks() { return $this->_links;}
    public function setLinks($links) { $this->_links = $links; }
    public function getFormList() { return $this->_formList;}
    public function setFormList($formList) { $this->_formList = $formList; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}