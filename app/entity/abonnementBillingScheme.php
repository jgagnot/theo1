<?php
/**
 * Created by Imi Creative
 * User: nicolasdelourme
 * Date: 20/12/2019
 * Time: 11:12
 */

namespace entity;

class abonnementBillingScheme
{

    private $id;
    private $planId;
    private $price;
    private $tierPrice;
    private $flatPrice;
    private $minQuantity;
    private $maxQuantity;
    private $quantityDivider;
    private $roundUp;
    private $timestamp;


    public function __construct($array)
    {
        $this->hydrate($array);
    }

    public function hydrate(array $array)
    {
        foreach ($array as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getPlanId()
    {
        return $this->planId;
    }

    public function setPlanId($planId)
    {
        $this->planId = $planId;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getTierPrice()
    {
        return $this->tierPrice;
    }

    public function setTierPrice($tierPrice)
    {
        $this->tierPrice = $tierPrice;
    }

    public function getFlatPrice()
    {
        return $this->flatPrice;
    }

    public function setFlatPrice($flatPrice)
    {
        $this->flatPrice = $flatPrice;
    }

    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    public function setMinQuantity($minQuantity)
    {
        $this->minQuantity = $minQuantity;
    }

    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity($maxQuantity)
    {
        $this->maxQuantity = $maxQuantity;
    }

    public function getQuantityDivider()
    {
        return $this->quantityDivider;
    }

    public function setQuantityDivider($quantityDivider)
    {
        $this->quantityDivider = $quantityDivider;
    }

    public function getRoundUp()
    {
        return $this->roundUp;
    }

    public function setRoundUp($roundUp)
    {
        $this->roundUp = $roundUp;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }


    function getClassArray()
    {
        $array = array();
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }
}