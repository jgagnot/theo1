<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 24/04/2019
 * Time: 14:29
 */

namespace entity;


class shoppingReferencePerItem
{

    private $_id;
    private $_itemId;
    private $_name;
    private $_subname;
    private $_reference;
    private $_tag;
    private $_overstock;
    private $_available;
    private $_visible;
    private $_actived;
    private $_saleStart;
    private $_saleStop;
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
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->_tag = $tag;
    }

    /**
     * @return mixed
     */
    public function getSaleStart()
    {
        return $this->_saleStart;
    }

    /**
     * @param mixed $saleStart
     */
    public function setSaleStart($saleStart)
    {
        $this->_saleStart = $saleStart;
    }

    /**
     * @return mixed
     */
    public function getSaleStop()
    {
        return $this->_saleStop;
    }

    /**
     * @param mixed $saleStop
     */
    public function setSaleStop($saleStop)
    {
        $this->_saleStop = $saleStop;
    }

    /**
     * @return mixed
     */
    public function getAvailable()
    {
        return $this->_available;
    }

    /**
     * @param mixed $available
     */
    public function setAvailable($available)
    {
        $this->_available = $available;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->_visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->_visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getSubname()
    {
        return $this->_subname;
    }

    /**
     * @param mixed $subname
     */
    public function setSubname($subname)
    {
        $this->_subname = $subname;
    }
    public function getId() { return $this->_id;}
    public function setId($id) { $this->_id = $id; }
    public function getItemId() { return $this->_itemId;}
    public function setItemId($itemId) { $this->_itemId = $itemId; }
    public function getReference() { return $this->_reference;}
    public function setReference($reference) { $this->_reference = $reference; }
    public function getOverstock() { return $this->_overstock;}
    public function setOverstock($overstock) { $this->_overstock = $overstock; }
    public function getActived() { return $this->_actived;}
    public function setActived($actived) { $this->_actived = $actived; }
    public function getTimestamp() { return $this->_timestamp;}
    public function setTimestamp($timestamp) { $this->_timestamp = $timestamp; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}