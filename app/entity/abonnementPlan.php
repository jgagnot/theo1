<?php
/**
 * Created by Imi Creative
 * User: nicolasdelourme
 * Date: 20/12/2019
 * Time: 11:14
 */

namespace entity;

class abonnementPlan
{

    private $id;
    private $abonnementId;
    private $stripePlanId;
    private $name;
    private $licensedUsage;
    private $aggregateMethod;
    private $tieredScheme;
    private $graduated;
    private $trialDays;
    private $billingAnchor;
    private $regularInterval;
    private $intervalNumber;
    private $currency;
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

    public function getAbonnementId()
    {
        return $this->abonnementId;
    }

    public function setAbonnementId($abonnementId)
    {
        $this->abonnementId = $abonnementId;
    }

    public function getStripePlanId()
    {
        return $this->stripePlanId;
    }

    public function setStripePlanId($stripePlanId)
    {
        $this->stripePlanId = $stripePlanId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLicensedUsage()
    {
        return $this->licensedUsage;
    }

    public function setLicensedUsage($licensedUsage)
    {
        $this->licensedUsage = $licensedUsage;
    }

    public function getAggregateMethod()
    {
        return $this->aggregateMethod;
    }

    public function setAggregateMethod($aggregateMethod)
    {
        $this->aggregateMethod = $aggregateMethod;
    }

    public function getTieredScheme()
    {
        return $this->tieredScheme;
    }

    public function setTieredScheme($tieredScheme)
    {
        $this->tieredScheme = $tieredScheme;
    }

    public function getGraduated()
    {
        return $this->graduated;
    }

    public function setGraduated($graduated)
    {
        $this->graduated = $graduated;
    }

    public function getTrialDays()
    {
        return $this->trialDays;
    }

    public function setTrialDays($trialDays)
    {
        $this->trialDays = $trialDays;
    }

    public function getBillingAnchor()
    {
        return $this->billingAnchor;
    }

    public function setBillingAnchor($billingAnchor)
    {
        $this->billingAnchor = $billingAnchor;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getRegularInterval()
    {
        return $this->regularInterval;
    }

    public function setRegularInterval($regularInterval)
    {
        $this->regularInterval = $regularInterval;
    }

    /**
     * @param mixed $intervalNumber
     */
    public function setIntervalNumber($intervalNumber)
    {
        $this->intervalNumber = $intervalNumber;
    }

    /**
     * @return mixed
     */
    public function getIntervalNumber()
    {
        return $this->intervalNumber;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
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