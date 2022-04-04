<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:46
 */

namespace entity;


class shoppingItemProductText
{

    private $_id;
    private $_itemId;
    private $_productId;
    private $_textName;
    private $_textContent;
    private $_textAttribute;


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
    public function getItemId() { return $this->_itemId;}
    public function setItemId($itemId) { $this->_itemId = $itemId; }
    public function getProductId() { return $this->_productId;}
    public function setProductId($productId) { $this->_productId = $productId; }
    public function getTextName() { return $this->_textName;}
    public function setTextName($textName) { $this->_textName = $textName; }
    public function getTextContent() { return $this->_textContent;}
    public function setTextContent($textContent) { $this->_textContent = $textContent; }
    public function getTextAttribute() { return $this->_textAttribute;}
    public function setTextAttribute($textAttribute) { $this->_textAttribute = $textAttribute; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}