<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:01
 */

namespace entity;


class post
{

    private $_id;
    private $_postTypeId;
    private $_postAuthorId;
    private $_title;
    private $_html;
    private $_status;
    private $_postStart;
    private $_postStop;
    private $_seoUrl;
    private $_tag;
    private $_entryImageId;
    private $_userId;
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

    public function getId() { return $this->_id;}
    public function setId($id) { $this->_id = $id; }
    public function getPostTypeId() { return $this->_postTypeId;}
    public function setPostTypeId($postTypeId) { $this->_postTypeId = $postTypeId; }
    public function getPostAuthorId() { return $this->_postAuthorId;}
    public function setPostAuthorId($postAuthorId) { $this->_postAuthorId = $postAuthorId; }
    public function getTitle() { return $this->_title;}
    public function setTitle($title) { $this->_title = $title; }
    public function getHtml() { return $this->_html;}
    public function setHtml($html) { $this->_html = $html; }
    public function getStatus() { return $this->_status;}
    public function setStatus($status) { $this->_status = $status; }
    public function getPostStart() { return $this->_postStart;}
    public function setPostStart($postStart) { $this->_postStart = $postStart; }
    public function getPostStop() { return $this->_postStop;}
    public function setPostStop($postStop) { $this->_postStop = $postStop; }
    public function getSeoUrl() { return $this->_seoUrl;}
    public function setSeoUrl($seoUrl) { $this->_seoUrl = $seoUrl; }
    public function getTag() { return $this->_tag;}
    public function setTag($tag) { $this->_tag = $tag; }
    public function getEntryImageId() { return $this->_entryImageId;}
    public function setEntryImageId($entryImageId) { $this->_entryImageId = $entryImageId; }
    public function getUserId() { return $this->_userId;}
    public function setUserId($userId) { $this->_userId = $userId; }
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