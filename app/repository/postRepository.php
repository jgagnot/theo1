<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 02/03/2018
 * Time: 16:51
 */

namespace repository;
use core\imi;


class postRepository extends imi
{
    protected $f3;
    private $db;

    public function __construct() {
        $this->f3 =  \Base::instance();
        $this->db = $this->f3->get("BDD");
    }

    public function fetchAllAuthor()
    {
        $query = 'SELECT postAuthor.id as authorId, firstName,lastName FROM postAuthor,`user`';
        $result = $this->db->exec($query);
        return $result;
    }

    public function fetchAllOpenedPost($postTypeId, $postStatus)
    {
        // PAS DE COLONNE CONTENT
        $query = 'SELECT content FROM post WHERE postTypeId= :postTypeId AND status= :postStatus AND postStart< CURDATE() AND postStop > CURDATE()';
        $result = $this->db->exec($query, array(
            ':postTypeId' => array($postTypeId, \PDO::PARAM_INT),
            ':postStatus' => array($postStatus, \PDO::PARAM_INT)
        ));
        return $result;
    }

    public function fetchAllPost($postTypeId, $postStatus=null)
    {
        $prepare = array();
        $prepare[':postTypeId'] = array($postTypeId, \PDO::PARAM_INT);
        $query = 'SELECT id,title,postStart,postStop,status,tag,seoUrl FROM post WHERE postTypeId= :postTypeId';
        if (isset($postStatus)) {
            $prepare[':postStatus'] = array($postStatus, \PDO::PARAM_INT);
            $query.= ' AND `status`= :postStatus';
        }

        $query .=  ' ORDER BY postStart';
        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchOnePost($field,$value,$entryContentLenght=110)
    {
        $query = 'SELECT path,post.id AS id,title,post.tag,seoUrl,postStart,html FROM post,image WHERE status=3 AND entryImageId=image.id AND '.$field.' like :like_field';
        $result = $this->db->exec($query, array(
            ':like_field' => $value
        ));

        if(empty($result)) return false;

        //on ajoute une colonne de début du texte réduite à $entryContentLenght caractères
        $result[0]['entryContent'] = $this->cutAtNextSpace(strip_tags($result[0]['html']), $entryContentLenght);
        return $result[0];
    }

    public function fetchPublishedPost($postTypeId_array,$tag_array=[],$authorId=null,$order='id',$direction='ASC',$number=8,$entryContentLenght=110,$withTagArray=false)
    {
        $query = 'SELECT path,post.id AS id,title,post.tag,seoUrl,postStart';

        if ($entryContentLenght>0) {
            $query.=',html';
        }

        //$query .= ' FROM post,image WHERE status=3 AND postStart<= CURDATE() AND postStop >= CURDATE() AND entryImageId=image.id ';
        $query .= ' FROM post,image WHERE status=2 AND postStart<= CURDATE() AND postStop >= CURDATE() AND entryImageId=image.id ';

        if (count($postTypeId_array)>0) {
            $query.=' AND (';
            foreach ($postTypeId_array as $key => $value) {
                if ($key>0) $query.=' OR ';
                $query.=' post.postTypeId = '.$value;
            }
            $query.=' )';
        }

        if (count($tag_array)>0) {
            foreach ($tag_array as $key => $value) {
                $query.=' AND post.tag like "%'.$value.'%"';
            }
        }

        if (isset($authorId)) {
            $query.=' AND postAuthorId = '.$authorId;
        }

        $query .= ' ORDER BY '.$order.' '.$direction.' LIMIT 0,'.intval($number);

        $result = $this->db->exec($query);

        foreach ($result as $key => $value) {
            if ($withTagArray) {
                //on crée une table des tag pour chaque post
                $result[$key]['tag_array']=explode(',',$value['tag']);
            }
            if ($entryContentLenght>0) {
                //on ajoute une colonne de début du texte réduite à $entryContentLenght caractères
                $result[$key]['entryContent'] = $this->cutAtNextSpace(strip_tags($value['html']), $entryContentLenght);
            }
        }

        return $result;
    }

    public function fetchTagPost($postTypeId)
    {
        $query = 'SELECT tag FROM post WHERE status=3 AND postTypeId= :postTypeId';
        $result = $this->db->exec($query, array(
            ':postTypeId' => $postTypeId
        ));

        $tag_array=[];
        //on enrichit le tableau d'une table de tag pour chaque entrée
        foreach ($result as $key1 => $value1) {
            $tag_array_post=explode(',',$value1['tag']);
            foreach ($tag_array_post as $key2 => $value2) {
                if ((in_array($value2,$tag_array)===false)&&($value2!=='focus')&&($value2!=='update')) {
                    $tag_array[]=$value2;
                }
            }
        }

        return $tag_array;
    }

    public function fetchAuthorId($name) {
        $query = 'SELECT postAuthor.id FROM `postAuthor`,`user` WHERE userId=`user`.id AND CONCAT(firstname,lastname)= :name';
        $result = $this->db->exec($query, array(
            ':name'=>$name
        ));
        return $result[0]['id'];
    }

    public function fetchNearbyPost ($postId,$number=4) {

        $query = 'SELECT tag FROM post WHERE id= :postId';
        $postTag=$this->db->exec($query, array(
            ':postId' => array($postId, \PDO::PARAM_INT)
        ))[0]['tag'];
        $tag_array= explode(',',$postTag);
        $post_array=[];

        //on recherche les post pour chaque tag équivalent
        foreach ($tag_array as $key1 => $value1) {
            $query='SELECT path,post.id,title,postStart,html,seoUrl FROM post,image WHERE status=3 AND post.id!= :postId AND postStart<= CURDATE() AND postStop >= CURDATE() AND entryImageId=image.id AND post.tag like "%'. addslashes($value1).'%" ORDER BY post.id';
            $result = $this->db->exec($query, array(
                ':postId' => array($postId, \PDO::PARAM_INT)
            ));
            $seoUrlString='';
            $post_array=[];
            foreach ($result as $key2 => $value2) {
                if (strpos($seoUrlString,$value2['seoUrl'])===false) {
                    $post_array[]=$value2;
                    $seoUrlString.=$value2['seoUrl'];
                }
            }
        }

        foreach ($post_array as $key => $value) {
            //on réduit le début du texte de chaque post
            $post_array[$key]['entryContent']=$this->cutAtNextSpace(strip_tags($value['html']),80);
        }
        return $post_array;
    }

}
