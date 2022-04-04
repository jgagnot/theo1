<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/04/2019
 * Time: 10:02
 */

namespace entity;


class postAuthor
{

    private $_id;
    private $_userId;
    private $_resume;
    private $_imageId;
    private $_email;
    private $_twitterId;


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
    public function getUserId() { return $this->_userId;}
    public function setUserId($userId) { $this->_userId = $userId; }
    public function getResume() { return $this->_resume;}
    public function setResume($resume) { $this->_resume = $resume; }
    public function getImageId() { return $this->_imageId;}
    public function setImageId($imageId) { $this->_imageId = $imageId; }
    public function getEmail() { return $this->_email;}
    public function setEmail($email) { $this->_email = $email; }
    public function getTwitterId() { return $this->_twitterId;}
    public function setTwitterId($twitterId) { $this->_twitterId = $twitterId; }


    function getClassArray() {
        $array = array();
        foreach($this as $key => $value) {
            $array[$key]=$value;
        }
        return $array;
    }
}