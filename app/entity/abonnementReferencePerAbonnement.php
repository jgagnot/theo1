<?php
/**
 * Created by Imi Creative
 * User: nicolasdelourme
 * Date: 31/12/2019
 * Time: 16:31
 */

namespace entity;

class abonnementReferencePerAbonnement
{

    private $id;
    private $abonnementId;
    private $referenceId;
    private $permanent;
    private $dateStart;
    private $dateStop;
    private $trackUsage;
    private $actived;
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

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    public function getPermanent()
    {
        return $this->permanent;
    }

    public function setPermanent($permanent)
    {
        $this->permanent = $permanent;
    }

    /**
     * @return mixed
     */
    public function getTrackUsage()
    {
        return $this->trackUsage;
    }

    /**
     * @param mixed $trackUsage
     */
    public function setTrackUsage($trackUsage)
    {
        $this->trackUsage = $trackUsage;
    }

    /**
     * @param mixed $actived
     */
    public function setActived($actived)
    {
        $this->actived = $actived;
    }

    /**
     * @return mixed
     */
    public function getActived()
    {
        return $this->actived;
    }

    public function getDateStart()
    {
        return $this->dateStart;
    }

    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
    }

    public function getDateStop()
    {
        return $this->dateStop;
    }

    public function setDateStop($dateStop)
    {
        $this->dateStop = $dateStop;
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