<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 19/03/2018
 * Time: 18:40
 */

namespace repository;

use entity\userGroup;


class groupRepository
{
    protected $f3;
    private $db;

    public function __construct() {
        $this->f3 =  \Base::instance();
        $this->db = $this->f3->get("BDD");
    }

    public function isFullGroup($f3, $group)
    {
        if ($group['maxUserAmount'] == 0)
            return (true);
        $query = 'SELECT COUNT(*) FROM `userPerGroup` WHERE `userPerGroup`.`groupId` = :groupId';
        $userAmount = $this->db->exec($query, array(
            ':id' => array($group['id'], \PDO::PARAM_INT)
        ));

        return (intval($userAmount[0]['COUNT(*)']) < $group['maxUserAmount']);
    }

    public function isNotSetted($f3, $groupId, $userId){
        $query = 'SELECT COUNT(*) FROM `userPerGroup` WHERE `userPerGroup`.`userId` = :userId AND `userPerGroup`.`groupId` = :groupId';

        $result = $this->db->exec($query, array(
            ':userId' => array($userId, \PDO::PARAM_INT),
            ':groupId' => array($groupId, \PDO::PARAM_INT)
        ));
        return (intval($result[0]['COUNT(*)']) == 0);
    }

    public function checkPermission($f3, $userId)
    {
        $query = 'SELECT `permission` FROM `userGroup`, `userPerGroup` WHERE `userPerGroup`.`userId` = :userId AND `userPerGroup`.`GroupId` = `userGroup`.`id`';
        $result = $this->db->exec($query, array(
            ':userId' => array($userId, \PDO::PARAM_INT)
        ));

        $permission = 0;
        foreach($result as $key=>$value){
            $permission = ($permission <  intval($value['permission']))?$value['permission']:$permission;
        }
        return ($permission);
    }
}
