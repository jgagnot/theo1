<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:35
 */

namespace entity;


class shoppingItemProductFeature
{

    private $_id;
    private $_itemId;
    private $_productId;
    private $_featureName;
    private $_featureContent;
    private $_featureAttribute;


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
    public function getFeatureName() { return $this->_featureName;}
    public function setFeatureName($featureName) { $this->_featureName = $featureName; }
    public function getFeatureContent() { return $this->_featureContent;}
    public function setFeatureContent($featureContent) { $this->_featureContent = $featureContent; }
    public function getFeatureAttribute() { return $this->_featureAttribute;}
    public function setFeatureAttribute($featureAttribute) { $this->_featureAttribute = $featureAttribute; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[substr( $key, 1)]=$value;
        }
        return $array;
    }
}
