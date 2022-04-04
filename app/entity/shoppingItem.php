<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:25
 */

namespace entity;


class shoppingItem
{

    private $_id;
    private $_name;
    private $_subName;
    private $_description;
    private $_seoUrl;
    private $_seoTitle;
    private $_seoMetaDescription;
    private $_view;


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
    public function getSubName() { return $this->_subName;}
    public function setSubName($subName) { $this->_subName = $subName; }
    public function getDescription() { return $this->_description;}
    public function setDescription($description) { $this->_description = $description; }
    public function getSeoUrl() { return $this->_seoUrl;}
    public function setSeoUrl($seoUrl) { $this->_seoUrl = $seoUrl; }
    public function getSeoTitle() { return $this->_seoTitle;}
    public function setSeoTitle($seoTitle) { $this->_seoTitle = $seoTitle; }
    public function getSeoMetaDescription() { return $this->_seoMetaDescription;}
    public function setSeoMetaDescription($seoMetaDescription) { $this->_seoMetaDescription = $seoMetaDescription; }
    public function getView() { return $this->_view;}
    public function setView($view) { $this->_view = $view; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}
