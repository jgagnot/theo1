<?php
/**
 * Created by Imi Creative
 * User: nicolasdelourme
 * Date: 12/12/2019
 * Time: 15:24
 */

namespace entity;

class abonnement
{

    private $id;
    private $name;
    private $stripeAbonnementId;
    private $description;
    private $seoUrl;
    private $seoMetaDescription;
    private $view;
    private $active;
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

    public function getStripeAbonnementId()
    {
        return $this->stripeAbonnementId;
    }

    public function setStripeAbonnementId($stripeAbonnementId)
    {
        $this->stripeAbonnementId = $stripeAbonnementId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getSeoUrl()
    {
        return $this->seoUrl;
    }

    public function setSeoUrl($seoUrl)
    {
        $this->seoUrl = $seoUrl;
    }

    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    public function setSeoMetaDescription($seoMetaDescription)
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
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