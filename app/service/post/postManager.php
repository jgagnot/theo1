<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 28/02/2018
 * Time: 14:23
 */


namespace service\post;

use core\imi;
use entity\postType;
use entity\postAuthor;
use entity\user;
use repository\postRepository;
use repository\groupRepository;
use service\image\imageManager;

class postManager extends imi
{

    public function displayPost($f3, $userId, $postId){
        $postRepository = new postRepository();

        if (($data = $this->fetchOneById('post', $postId)) === null)
            return (false);
        if ($postRepository->checkPermission($f3, $userId, new postType($this->fetchOneById('postType', $data['postTypeId'])), 'read') == false)
            return (false);
        $twig = $GLOBALS['twig'];

        echo $twig->render($this->getF3('viewsRoot').'/loader/testCkEditor.html.twig', array('f3'=>$f3, 'data'=>$data));
    }

    public function postList ($f3, $postTypeId, $postTypeStatus=NULL)
    {

        $groupRepository = new groupRepository();
        $postRepository=new postRepository();

        //si le type passé n'existe pas, on retourne false
        if ($this->existsValue('postType', array('id'=>$postTypeId)) === false) return false;

        //si le user n'est pas en session, on retourne false
        if (($f3->get("SESSION.userId")===-1)||($f3->get("SESSION.userId")==='')||($f3->get("SESSION.userId")===null)) return false;

        $user  = new user($this->fetchOneById('user', $f3->get("SESSION.userId")));

        //si le user n'a pas les droits ou qu'il n'est pas auteur, on retourne false
        if ($groupRepository->checkPermission($f3,$user->getId())<$this->fetchOneById('postType',$postTypeId)['readPermission'])
            return false;

        $post_array = $this->addStatusColor($postRepository->fetchAllPost($postTypeId, $postTypeStatus));

        return $post_array;
    }

    public function postNew($f3, $postTypeId) {

        $groupRepository = new groupRepository();
        $postRepository=new postRepository();

        //si le user n'est pas en session, on retourne false
        if (($f3->get("SESSION.userId")===-1)||($f3->get("SESSION.userId")===null)) return false;

        $user  = new user($this->fetchOneById('user', $f3->get("SESSION.userId")));

        $postType = new postType($this->fetchOneById('postType', intval($postTypeId)));

        //si le user n'a pas les droits ou qu'il n'est pas auteur, on retourne false
        if (($groupRepository->checkPermission($f3,$user->getId())<$this->fetchOneById('postType',$postTypeId)['editPermission'])&&($this->existsValue('postAuthor',array('userId'=>$user->getId()))===false))
            return false;

        $post_array['postTypeId'] = $postType->getId();
        $post_array['author'] = $postRepository->fetchAllAuthor();
        $post_array['entryImageId']=-1;

        $imageJs_string=$this->imageArrayToJsStringObject($f3,$this->fetchAll('image'));

        return array(
            'post_array' => $post_array,
            'imageJs_string' => $imageJs_string,
            'postType' => $postType->getTinyScript()
        );
    }

    public function postEdit($f3, $postId){

        $groupRepository = new groupRepository();
        $postRepository=new postRepository();

        //si le user n'est pas en session, on retourne false
        //if (($f3->get("SESSION.userId")===-1)||($f3->get("SESSION.userId")==='')) return false;

        $user  = new user($this->fetchOneById('user', 2));

        $post_array = $this->fetchOneById('post', intval($postId));
        $postType = new postType($this->fetchOneById('postType', intval($post_array['postTypeId'])));

        //si le user n'a pas les droits ou qu'il n'est pas auteur, on retourne false
        //if (($groupRepository->checkPermission($f3,$user->getId())<$this->fetchOneById('postType',$postType->getId())['editPermission'])&&($this->existsValue('postAuthor',array('userId'=>$user->getId()))===false))
        // return false;

        $post_array['insert'] = false;
        $post_array['author'] = $postRepository->fetchAllAuthor();

        if ($post_array['entryImageId']!==-1) {
            $post_array['entryImage']=$this->fetchOneById('image',$post_array['entryImageId']);
        }

        if (strlen($f3->get('SESSION.imageId'))>0) {
            $this->flash($f3,'L\'image que vous avez sélectionnée porte le n°'.$f3->get("SESSION.imageId"),'#flash-top','warning',5000);
            $this->sessionClear('imageId');
        }

        $imageJs_string=$this->imageArrayToJsStringObject($f3,$this->fetchAll('image'));
        return array(
            'post_array' => $post_array,
            'imageJs_string' => $imageJs_string,
            'postType' => $postType->getTinyScript()
        );
    }

    public function postRead($f3, $seoUrl,$lastPostNumber,$popularPostNumber,$imagePostNumber){

        $groupRepository = new groupRepository();
        $postRepository = new postRepository();

        $post_array = $this->fetchOneByKeysEqual('post', array('seoUrl'=>$seoUrl));
        $post_array['seniority']=$this->postSeniority($f3,$post_array['lastStatusUpdate']);
        $post_array['readingTime']=intval(strlen(strip_tags($post_array['html']))/1500);

        $postType = new PostType($this->fetchOneById('postType', intval($post_array['postTypeId']) ));

        $postTypeReadPermission=$this->fetchOneById('postType',intval($postType->getId()))['readPermission'];
        /*
        if (intval($f3->get("SESSION.permission"))<$postTypeReadPermission) {
            //si le user n'a pas la permission et que l'accès est public, on coupe le texte
            if ($f3->get('publicPost')===1) {
                $post_array['entryContent'] = $this->cutAtNextSpace(strip_tags($post_array['html']), 1000);
                $post_array['permission']=0;
            } else {
                //si le user n'a pas la permission et que l'accès n'est pas public, on reroute
                $f3->reroute('/noPermission?type=post');
            }
        } else {
            $post_array['permission']=1;
            //si le user a la permission, on vérifie ses favoris s'il est loggué
            if (($f3->get("SESSION.userId")!==-1)&&($f3->get("SESSION.userId")!=='')&&(intval($f3->get("SESSION.userId")))!==0) {
                $user  = new user($this->fetchOneById('user', $f3->get("SESSION.userId")));
                if ($this->existsValue('favorite',array('url'=>$f3->get('path').'p/'.$post_array['seoUrl'],'userId'=>$user->getId()))) $post_array['favorite']=true;
            }
        }
        */

        $lastPost_array=$postRepository->fetchPublishedPost([1,2],[],null,'id','DESC',$lastPostNumber,0,0);
        $popularPost_array=$postRepository->fetchPublishedPost([1,2],[],null,'title','ASC',$popularPostNumber,0,0);
        $imagePost_array=$postRepository->fetchPublishedPost([1,2],[],null,'entryImageId','DESC',$imagePostNumber,0,0);
        $tagPost_array=$postRepository->fetchTagPost($postType->getId());
        $nearbyPost_array=$postRepository->fetchNearbyPost($post_array['id'],$imagePostNumber);

        $post_array['insert'] = false;

        $post_array['author'] =$this->fetchAllCrossTableEqualKeys('postAuthor','user','userId','id',array('postAuthor.id'=>$post_array['postAuthorId']))[0];
        if (isset($post_array['author']['imageId'])) {
            $post_array['author']['image']=$this->fetchOneById('image',$post_array['author']['imageId']);
        }

        //on incrémente le nombre d'articles lus au cours de la session
        $f3->set("SESSION.postRead",$f3->get("SESSION.postRead")+1);

        //echo '<pre>'.print_r($post_array, true).'</pre>';die();

        $twig = $GLOBALS['twig'];
        echo $twig->render($this->getF3('viewsRoot').'/post/read.html.twig', array(
                'f3'=>$f3,
                'post_array'=>$post_array,
                'lastPost_array'=>$lastPost_array,
                'popularPost_array'=>$popularPost_array,
                'tagPost_array'=>$tagPost_array,
                'imagePost_array'=>$imagePost_array,
                'nearbyPost_array'=>$nearbyPost_array,
                'postTypeReadPermission'=>$postTypeReadPermission
            )
        );
    }

    public function postFavorite($f3) {
        $postRepository = new postRepository();
        $postFavorite_array=$this->fetchAllByKeysLike('favorite',array('name'=>'post','userId'=>$f3->get("SESSION.userId")));
        $userPost_array=[];
        foreach ($postFavorite_array as $key => $value) {
            //on recherche les post à afficher
            $startseoUrl=strpos($value['url'],'.fr/p/')+6;
            $seoUrl=substr($value['url'],$startseoUrl,strlen($value['url'])-$startseoUrl);
            $newPost_array=$postRepository->fetchOnePost('seoUrl',$seoUrl);
            if ($newPost_array!==false) {
                $userPost_array[]=$postRepository->fetchOnePost('seoUrl',$seoUrl,200);
            }
        }
        $twig = $GLOBALS['twig'];

        if (count($userPost_array)>0) {
            echo $twig->render('post/favorite.html.twig', array(
                'f3' => $f3,
                'userPost_array' => $userPost_array
            ));
        } else {
            $this->express($f3,"Oups !","Vous n'avez aucun favori enregistré pour le moment. Cliquez sur le bouton <strong>Mettre dans mes favoris</strong> sous les textes des articles pour en ajouter.",'sm');
            $f3->reroute('/');
        }
    }

    public function postAuthor($f3,$author) {
        $postRepository = new postRepository();

        $postAuthor = new postAuthor($this->fetchOneById('postAuthor',$postRepository->fetchAuthorId($author)));
        $authorAsUser = new user($this->fetchOneById('user',$postAuthor->getUserId()));
        $imageAuthorPath=$this->fetchOneById('image',$postAuthor->getImageId())['path'];

        $authorPost_array=$postRepository->fetchPublishedPost(1,[],$postAuthor->getId(),'postStart','DESC',10,200,0);

        $twig = $GLOBALS['twig'];
        echo $twig->render('post/author.html.twig',array(
            'f3'=>$f3,
            'authorAsUser'=>$authorAsUser,
            'postAuthor'=>$postAuthor,
            'imageAuthorPath'=>$imageAuthorPath,
            'authorPost_array'=>$authorPost_array
        ));
    }

    public function postTag ($f3,$tag) {
        $postRepository = new postRepository();

        $tagPost_array=$postRepository->fetchPublishedPost(1,[$tag],null,'id','DESC',7,200,0);

        $twig = $GLOBALS['twig'];
        echo $twig->render('post/tag.html.twig',array(
            'f3'=>$f3,
            'tag'=>$tag,
            'tagPost_array'=>$tagPost_array
        ));
    }

    public function postSeniority ($f3,$postStart) {

        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

        $datePostStart =  new \DateTime($postStart);
        $now = new \DateTime();

        $interval = $datePostStart->diff($now);

        if (intval(($interval->format('%y')))>=1 || intval(($interval->format('%m')))>=1 || intval(($interval->format('%d')))>=1) {

            $seniority=strftime("%d %B %Y",$datePostStart->getTimestamp());

            if (substr($seniority,0,1)==='0') {
                $seniority=substr($seniority,1,strlen($seniority)-1);
            }
            if (substr($seniority,0,1)==='1') {
                $seniority='1er '.substr($seniority,2,strlen($seniority)-2);
            }

            return $seniority;
        }
        if (intval(($interval->format('%d')))===1) return "hier";
        if (intval(($interval->format('%d')))===2) return "avant-hier";
        if (intval(($interval->format('%h')))===1) return "il y a une heure";
        if (intval(($interval->format('%h')))>1) return "aujourd'hui";

        return "à l'instant";

    }

    /**************************************************/

    private function imageArrayToJsStringObject ($f3,$image_array) {
        //cette méthode retourne une string pouvant être exploitée JS dans TinyMCE (objet image)

        $imageJs_string='';

        foreach ($image_array as $key => $value) {
            $imageJs_string.="{title: '".$value['id'].' : '.$value['name']."',value: '".$f3->get('path').$value['path']."'},";
        }

        $imageJs_string=substr($imageJs_string,0,strlen($imageJs_string)-1);

        return $imageJs_string;
    }

}