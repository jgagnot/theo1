<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 07/05/2019
 * Time: 11:43
 */

namespace service\image;

use core\imi;
use entity\image;

class imageManager extends imi
{
    public function insertImage ($f3,$files_array,$name,$tag, $description, $imageTypeId) {

        //Cette méthode vérifie le format et la taille des fichiers uploadés et retourne un message d'erreur

        $insertion_array=[];
        $error='';
        $result=true;

        foreach ($files_array as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {

                    if ($key2=='name') {
                        $insertion_array[$key3]=true;
                    }
                    if ($key2=='type' && ($value3!=='image/png')&&($value3!=='image/jpeg')) {
                        $insertion_array[$key3]=false;
                        $error = array("error","wrong_fromat");

                    }
                    if ($key2=='size' && ($value3>10000000)) {
                        $insertion_array[$key3]=false;
                        $error = array("error", "size_error");
                    }
                    if ($key2=='error' && ($value3==true)) {
                        $insertion_array[$key3]=false;
                        $error = array("error", "unexpected_error");
                    }
                }

            }
        }

        foreach ($insertion_array as $key1 => $value1) {

            if ($value1) {

                //$file=$this->randomString();
                //$extension=substr($files_array['file']['name'][$key1],stripos($files_array['file']['name'][$key1],'.'),4);
                $path='project/'.$f3->get('project').'/images/upload/'.$files_array['file']['name'][$key1];

                if ($this->existsValue('image', array('path'=>$path))==false) {
                    if ($this->existsValue('image', array('name'=>$name)) == false){
                        $result= $files_array['file']['name'][$key1].'('.$files_array['file']['name'][$key1].') : '.move_uploaded_file($files_array['file']['tmp_name'][$key1], $path);
                        if ($result){
                            $imageId = $this->insertByArray('image',array('name'=>$name,'tag'=>$tag,'path'=>$path, 'description'=>$description, 'imageTypeId' => $imageTypeId),[]);

                            if (intval($imageId) > 0)
                                return array("success", array("image"=> $this->fetchOneById("image", intval($imageId))));
                            else
                                return array("error", "unexpected_error");
                        }
                    }else{
                        return array("error", "name_exist");
                    }
                }else {
                    return array("error", "image_exist");
                }
            }
        }
        return  $error;
    }

    public function curlInsertImage($f3, $files_array, $name, $tag, $description, $imageTypeId)
    {
        //Cette méthode vérifie le format et la taille des fichiers uploadés et retourne un message d'erreur

        $insertion_array = [];
        $error = '';
        $result = true;

        foreach ($files_array as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if ($key2 == 'name') {
                    $insertion_array[$key2] = true;
                }
                if ($key2 == 'type' && ($value2 !== 'image/png') && ($value2 !== 'image/jpeg')) {
                    $insertion_array[$key2] = false;
                    $error = array("error", "wrong_fromat");
                }
                if ($key2 == 'size' && ($value2 > 10000000)) {
                    $insertion_array[$key2] = false;
                    $error = array("error", "size_error");
                }
                if ($key2 == 'error' && ($value2 == true)) {
                    $insertion_array[$key2] = false;
                    $error = array("error", "unexpected_error");
                }
            }
        }

        //$file=$this->randomString();
        //$extension=substr($files_array['file']['name'][$key1],stripos($files_array['file']['name'][$key1],'.'),4);

        $path = 'project/' . $f3->get('project') . '/images/upload/' . $files_array['file']['name'];

        if ($this->existsValue('image', array('path' => $path)) == false) {
            if ($this->existsValue('image', array('name' => $name)) == false) {
                $result = $files_array['file']['name'] . '(' . $files_array['file']['name'] . ') : ' . move_uploaded_file($files_array['file']['tmp_name'], $path);

                if ($result) {
                    $imageId = $this->insertByArray('image', array('name' => $name, 'tag' => $tag, 'path' => $path, 'description' => $description, 'imageTypeId' => intval($imageTypeId)), []);

                    if (intval($imageId) > 0)
                        return array("success", array("image" => $this->fetchOneById("image", intval($imageId))));
                    else
                        return array("error", "unexpected_error");
                }
            } else {
                return array("error", "name_exist");
            }
        } else {
            return array("error", "image_exist");
        }
        return $error;
    }

    public function tagArray($f3) {
        //cette méthode retourne un tableau des tag associées aux images dans $image_array

        $image_array=$this->fetchOneField('image','tag');

        $tag_array=[];
        foreach ($image_array as $key1 => $value1) {
            $tagImage_array=explode(',',$value1);
            foreach ($tagImage_array as $key2 => $value2) {
                if ((in_array($value2,$tag_array)===false)&&($value2!=='')) {
                    $tag_array[]=$value2;
                }
            }
        }

        return $tag_array;

    }
    public function cropPreview($f3, $file_array){
        $allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
        $temp = explode(".", $file_array["img"]["name"]);
        $extension = end($temp);

        if (in_array($extension, $allowedExts)){
            if ($file_array['img']['error'] > 0){
                return array('error', 'unexpected_error');
            }
            else{
                $filename = $file_array['img']['tmp_name'];
                list($width, $height) = getimagesize($filename);
                return array('status'=>'success', 'width' => $width, 'height'=>$height ,'url' =>  'data:image/'.$extension .';base64,'.base64_encode(file_get_contents($file_array['img']['tmp_name'])));
            }
        }
        else
            return array('error', 'wrong_format');
    }

    public function cropInsert($f3, $data_array){

        $imagePath = 'project/'.$f3->get('project')."/images/temp/";
        $allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
        $temp = explode(".", $data_array["img"]["name"]);
        $extension = end($temp);

        if(!is_writable($imagePath)){
            $response = Array(
                "status" => 'error',
                "message" => 'Can`t upload File; no write Access'
            );
            return $response;
        }

        if ( in_array($extension, $allowedExts))
        {
            if ($data_array["img"]["error"] > 0)
            {
                $response = array(
                    "status" => 'error',
                    "message" => 'ERROR Return Code: '. $data_array["img"]["error"],
                );
            }
            else
            {

                $filename = $data_array["img"]["tmp_name"];
                list($width, $height) = getimagesize( $filename );
                move_uploaded_file($filename,  $imagePath . $data_array["img"]["name"]);
                $response = array(
                    "status" => 'success',
                    "url" => $f3->get('path').$imagePath.$data_array["img"]["name"],
                    "width" => $width,
                    "height" => $height
                );

            }
        }
        else
        {
            $response = array(
                "status" => 'error',
                "message" => 'something went wrong, most likely file is to large for upload. check upload_max_filesize, post_max_size and memory_limit in you php.ini',
            );
        }

        return $response;
    }


    public function cropImage($f3, $data_array)
    {
        $url = $data_array['imgUrl'];
        //$data_array['imageName'] = str_replace(' ', '',$data_array['imageName']);

        $imgInitW = $data_array['imgInitW'];
        $imgInitH = $data_array['imgInitH'];

        $imgW = $data_array['imgW'];
        $imgH = $data_array['imgH'];

        $imgY1 = $data_array['imgY1'];
        $imgX1 = $data_array['imgX1'];

        $cropW = $data_array['cropW'];
        $cropH = $data_array['cropH'];

        $angle = $data_array['rotation'];
        $jpeg_quality = 100;
        $imageId = intval($this->fetchMax('image', 'id')) + 1;

        $output_filename = "project/" . $f3->get('project') . '/images/upload/' . $data_array['imageName'].'-'.$imageId;

        if ($this->existsValue('image', array('path' => $output_filename)) == false) {

            if ($this->existsValue('image', array('name' => $data_array['imageName'].'-'.$imageId)) == false) {

                $imageData = getimagesize($url);
                switch (strtolower($imageData['mime'])) {
                    case 'image/png':
                        $source_image = imagecreatefrompng($url);
                        $type = '.png';
                        break;
                    case 'image/jpeg':
                        $source_image = imagecreatefromjpeg($url);
                        error_log("jpg");
                        $type = '.jpeg';
                        break;
                    case 'image/gif':
                        $source_image = imagecreatefromgif($url);
                        $type = '.gif';
                        break;
                    default:
                        die('image type not supported');
                }

                if (!is_writable(dirname($output_filename)) || $source_image === false) {
                    return array(
                        "status" => 'error',
                        "message" => 'Can`t write cropped File'
                    );
                } else {
                    $resizedImage = imagecreatetruecolor($imgW, $imgH);

                    imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
                    $rotated_image = imagerotate($resizedImage, -$angle, 0);

                    $rotated_width = imagesx($rotated_image);
                    $rotated_height = imagesy($rotated_image);

                    $dx = $rotated_width - $imgW;
                    $dy = $rotated_height - $imgH;

                    $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
                    imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
                    imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);

                    $final_image = imagecreatetruecolor($cropW, $cropH);
                    imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
                    imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);

                    imagejpeg($final_image, $output_filename . $type, $jpeg_quality);

                    $cfile = curl_file_create($output_filename.$type, $imageData['mime'], $data_array['imageName'].'-'.$imageId.$type);

                    $imgdata = array('file' => $cfile, 'name'=>htmlspecialchars($data_array['imageName'].'-'.$imageId.$type), 'description'=>htmlspecialchars($data_array['imageDescription']), 'tag' =>htmlspecialchars($data_array['imageTag']), 'imageTypeId' =>htmlspecialchars($data_array['imageTypeId']));

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $f3->get('imagePath').'uploadImage');
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $imgdata);
                    $r = curl_exec($curl);

                    unlink($output_filename.$type);
                    $image = $this->fetchOneById('image', intval($imageId));

                    /*
                                        var_dump($r);
                                        $imageId = $this->insertByArray('image', array('name' => htmlspecialchars($data_array['imageName'].'-'.$imageId), 'tag' => $tags, 'description'=>htmlspecialchars($data_array['imageDescription']), 'path' =>$output_filename.$type), []); */
                    return Array(
                        "status" => 'success',
                        "url" => $f3->get('imagePath') .$image['path'],
                        "image_array" => $this->fetchOneById('image', intval($imageId))
                    );
                }
            } else {
                return array("error", "name_exist");
            }
        } else {
            return array("error", "image_exist");
        }

    }
}
