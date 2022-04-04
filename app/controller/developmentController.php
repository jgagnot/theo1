<?php
/**
 * Created by PhpStorm.
 * User: nicolasdelourme
 * Date: 15/10/2017
 * Time: 22:13
 */

namespace controller;

use core\imi;

class developmentController extends imi
{
    public function editEntityGenerator ($f3,$namespace,$table) {
        if (isset($namespace)&&isset($table)) {
            if ($this->existsTable($table)) {
                $code = $this->entityGenerator($f3,$namespace,$table);
                $code=htmlentities($code);
                $twig = $GLOBALS['twig'];
                echo $twig->render('devTools/devEntityGeneratorCode.html.twig',array('code'=>$code,'namespace'=>$namespace,'table'=>$table));
            } else {
                echo 'la table '.$table.' n\'existe pas';
            }

        }
        else {
            //on saisit le namespace et la table
            $twig = $GLOBALS['twig'];
            echo $twig->render('devTools/devEntityGenerator.html.twig');
        }
    }

    private function entityGenerator ($f3,$namespace,$table) {

        //il faut que la table contienne un élément pour que cette méthode fonctionne
        $tableKey_array=array_keys($this->fetchAll($table)[0]);

        $getterAndSetter='';
        $keyList='';
        foreach ($tableKey_array as $value) {
            $getterAndSetter.='public function get'.ucfirst($value).'() { return $this->_'.$value.';}'.PHP_EOL;
            $getterAndSetter.=' public function set'.ucfirst($value).'($'.$value.') { $this->_'.$value.' = $'.$value.'; }'.PHP_EOL;
            $getterAndSetter.=' ';
            $keyList.='private $_'.$value.';'.PHP_EOL;
        }

        $date=new \DateTime(now,new \DateTimeZone('GMT+2'));

        $code = file_get_contents('ui/include/devEntityGenerator.txt');

        $code=str_replace("{{table}}",$table,$code);

        $code=str_replace("{{date}}",$date->format('d/m/Y'),$code);
        $code=str_replace("{{time}}",$date->format('H:i'),$code);
        $code=str_replace("{{keyList}}",$keyList,$code);
        $code=str_replace("{{namespace}}",$namespace,$code);
        $code=str_replace("{{getterAndSetter}}",$getterAndSetter,$code);

        return $code;
    }

    public function bootstrapTinymce ($f3) {
        $twig = $GLOBALS['twig'];
        echo $twig->render('devTools/bootstrapTinymce.html.twig');
    }


}