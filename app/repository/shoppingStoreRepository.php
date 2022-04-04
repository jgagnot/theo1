<?php
/**
 * Created by PhpStorm.
 * User: nicolasdelourme
 * Date: 15/02/2018
 * Time: 17:56
 */

namespace repository;
use entity\shoppingItem;
use core\imi;

use function Sodium\add;


class shoppingStoreRepository
{

    protected $f3;
    private $db;

    public function __construct() {
        $this->f3 =  \Base::instance();
        $this->db = $this->f3->get("BDD");
    }

    public function executeQuery($query, $prepare=null) {
        return $this->db->exec($query, $prepare);
    }

    public function fetchItemStore($storeId, $data=null, $orderKey1='itemId',$orderKey2='saleStop',$direction1='ASC',$direction2='ASC') {
        $prepare = array();
        $prepare[':storeId'] = array($storeId, \PDO::PARAM_INT);
        //attention quand un id dans chaque table, retourne l'id de la $table2
        $query="SELECT * FROM `shoppingItem`,`shoppingItemPerStore` WHERE `shoppingItem`.`id`=`shoppingItemPerStore`.`itemId` AND storeId= :storeId AND shoppingItemPerStore.actived = true ";

        if (isset($data))
            foreach($data as $key => $value) {
                $query .= " AND " . addslashes($key)." like '".addslashes($value)."'";
            }

        $query.=" ORDER BY ". addslashes($orderKey1)." ".addslashes($direction1).",".addslashes($orderKey2)." ".addslashes($direction2);
        //echo $query;
        $result = $this->db->exec($query, $prepare);

        return $result;
    }

    public function fetchProductPerStore($f3, $storeId){
        $prepare = array();
        $prepare['storeId'] = array($storeId, \PDO::PARAM_INT);

        $query = "SELECT DISTINCT shoppingProduct.id, 
                         shoppingProduct.name, 
                         shoppingProduct.stock, 
                         shoppingProduct.quantity, 
                         shoppingProduct.overstock, 
                         shoppingProduct.weight, 
                         shoppingProduct.width,
                         shoppingProduct.height, 
                         shoppingProduct.depth, 
                         shoppingProduct.physical,
                         shoppingProduct.virtual,
                         shoppingProduct.timestamp
                 FROM shoppingProduct, shoppingProductPerReference, shoppingReferencePerItem, shoppingItemPerStore
                 WHERE
                 shoppingItemPerStore.storeId = :storeId AND 
                 shoppingReferencePerItem.itemId = shoppingItemPerStore.itemId AND 
                 shoppingProductPerReference.referenceId = shoppingReferencePerItem.id AND 
                 shoppingProduct.id = shoppingProductPerReference.productId";

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchReferencePerItem($f3, $itemId){

        $prepare = array();
        $prepare[':itemId'] = array($itemId, \PDO::PARAM_INT);
        //attention quand un id dans chaque table, retourne l'id de la $table2
        $query="SELECT * FROM `shoppingReferencePerItem` WHERE `shoppingReferencePerItem`.`itemId`= :itemId AND actived = true";

//echo $query;
        $result = $this->db->exec($query, $prepare);

        return $result;
    }

    public function fetchOrderByPeriod($f3, $startDate, $endDate){
        $query = "SELECT * FROM shoppingOrder WHERE DATEDIFF( '".$startDate."', shoppingOrder.timestamp ) < 0 AND DATEDIFF( '".$endDate."' , shoppingOrder.timestamp ) >= 0 AND shoppingOrder.type = 'bill' AND shoppingOrder.status = 'complete'";

        $result = $this->db->exec($query);
        return $result;

    }

    public function fetchSubscriberPerABonnement($f3, $abonnementId){
        $prepare = array();
        $prepare[':abonnementId'] = array($abonnementId, \PDO::PARAM_INT);

        $query="SELECT abonnementSubscription.* FROM user, abonnementSubscription, abonnementPlan WHERE user.id = abonnementSubscription.userId = abonnementSubscription.userId and abonnementSubscription.planId = abonnementPlan.id and abonnementPlan.abonnementId = :abonnementId";

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchAbonnementReferencePerDate($f3, $abonnementId){
        $prepare = array();
        $prepare[':abonnementId'] = array($abonnementId, \PDO::PARAM_INT);

        $query="SELECT shoppingReferencePerItem.* 
              FROM shoppingReferencePerItem, abonnementReferencePerAbonnement WHERE 
          abonnementReferencePerAbonnement.abonnementId = :abonnementId  
          AND abonnementReferencePerAbonnement.referenceId = shoppingReferencePerItem.id 
          AND NOW() >= abonnementReferencePerAbonnement.dateStart 
           AND NOW() <= abonnementReferencePerAbonnement.dateStop 
          AND abonnementReferencePerAbonnement.actived = 1
          AND shoppingReferencePerItem.actived = 1";

        $result = $this->db->exec($query, $prepare);
;
        return $result;
    }
}
