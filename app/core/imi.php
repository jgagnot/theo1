<?php

namespace core;

class imi
{
    protected $f3;
    private $db;

    public function setF3($key , $val) {
        $this->f3->set($key,$val);
    }

    public function getF3($key) {
        return $this->f3->get($key);
    }

    public function getHiveKey($key , $default='') {
        $hive = $this->f3->hive();
        if(isset($hive[$key])) return $hive[$key];
        return $default;
    }

    /****************** REPOSITORY ********************/

    public function __construct() {
        $this->f3 =  \Base::instance();
        $this->db = $this->f3->get("BDD");
    }

    public function set($key , $val) {
        $this->f3->set($key,$val);
    }

    public function prepareRequest($prepare, $key) {
        // Permet de vérifier que la clé souhaitée n'est pas déjà dans le tableau de requête préparée
        // NOTE: Un champ préparé doit avoir un nom unique
        if (! array_key_exists(":$key", $prepare) ) {
            return ":$key";
        } else {
            return ':' . $key . '_' . uniqid();
        }
    }

    public function fetchAllTables($f3) {
        // Récupère le nom de toutes les tables de la db
        $query="show tables";
        $result = $this->db->exec($query);
        $results = array();
        foreach ($result as $row) {
            // EXAMPLE: $row = ['Tables_in_lignededepart' => 'LeNomDuneTable']
            //Je créer un tableau contenant uniquement toutes les tables de la base
            array_push($results, $row['Tables_in_'. $f3->get('dbName')]);
        }
        return $results;
    }

    public function fetchAllFieldsFromTable($table){
        // Pour vérifier qu'une colonne existe, il faut au préalable s'assurer que la table existe
        $existsTable = $this->isValidTable($table);
        if ($existsTable) {
            $query="SHOW COLUMNS FROM $table";
            $result = $this->db->exec($query);

            return $result;
        } else {
            return false;
        }

    }
    public function createEntity($f3) {
        // Récupère tous les noms de tables et colonnes et génère le fichier entity pour chacunes d'entre elles s'il n'existe pas déjà

        // Enregistre dans $tables tous les nom de tables
        $tables = $this->fetchAllTables($f3);
        $fields = array();
        // Récupère tous les fichiers de 'app/entity/' et exclu les fichiers cachés, commençant par un "."
        $allFiles =  preg_grep('/^([^.])/', scandir('app/entity'));

        foreach ($tables as $oneTable) {
            // Pour chaque tables, récupère toutes les colonnes
            $fieldsTable = $this->fetchAllFieldsFromTable($oneTable);
            $fieldsForTable = array();
            for ($i=0; $i < count($fieldsTable); $i++) {
                // Pour chaque fields, enregistre uniquement le nom de la colonne ( et non pas les types, etc...)
                array_push($fieldsForTable, $fieldsTable[$i]['Field']);
            }
            // EXAMPLE : $fields['abonnement'] = ['id', 'trialDateInterval', 'trialCycle', ...]
            $fields[$oneTable] = $fieldsForTable;

            // array_search retourne la clé du fichier s'il existe, sinon retourne false
            $existEntity = array_search("$oneTable.php", $allFiles);
            // Si le fichier d'entity n'existe pas, alors on le créer dans le dossier 'app/entity/'
            if ( !$existEntity ) {
                file_put_contents("app/entity/$oneTable.php", $this->createEntityFile($oneTable, $fieldsForTable));
            }
        }
        return $fields;
    }

    public function createEntityFile($table, Array $table_columns) {
        // Le même que dans le controller, mais retourne le code sous forme de string

        //PARAMS:
        // $table : String
        // $table_columns: Array avec le nom de toutes les columns de la table
        $getterAndSetter='';
        $keyList='';
        foreach ($table_columns as $value) {
            $getterAndSetter.="    public function get".ucfirst($value)."() { return \$this->_".$value.";}\n";
            $getterAndSetter.="    public function set".ucfirst($value)."(\$".$value.") { \$this->_".$value." = \$".$value."; }\n";
            $getterAndSetter.="\n";
            $keyList.="    private \$_".$value.";\n";
        }

        $date=new \DateTime(now,new \DateTimeZone('GMT+2'));

        $code = file_get_contents('ui/include/devEntityGenerator.txt');

        $code=str_replace("{{table}}",$table,$code);

        $code=str_replace("{{date}}",$date->format('d/m/Y'),$code);
        $code=str_replace("{{time}}",$date->format('H:i'),$code);
        $code=str_replace("{{keyList}}",$keyList,$code);
        $code=str_replace("{{namespace}}",'entity',$code);
        $code=str_replace("{{getterAndSetter}}",$getterAndSetter,$code);

        return $code;

    }

    public function isValidTable($table) {
        // Verifie que l'entity de la table existe (et donc que la table elle même existe en DB)

        // Glob() vérifie qu'un chemin est valide, si le fichier n'existe pas il retourne un array vide, sinon il retourne le chemin dans un array
        $exists = glob('app/entity/'. $table . '.php');
        if (!empty($exists)) {
            return true;
        }

        return false;
    }


    public function fetchAll($table, $orderField=NULL,$direction='ASC') {
        $query="SELECT * FROM `$table`";
        if (isset($orderField)) {
            // Si $direction n'est pas égale à 'ASC' alors elle est égale à 'DESC'
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY ".addslashes($orderField)." $direction";
        }

        $result = $this->db->exec($query);
        return $result;
    }

    public function fetchAllCrossTableEqualKeys($table1,$table2,$key1,$key2,$data=array(),$orderField=NULL,$orderFieldTable=1,$direction='ASC',$limitStart=NULL,$limitStop=NULL) {

        $prepare = array();

        //attention quand un id dans chaque table, retourne l'id de la $table2
        $query="SELECT * FROM `".addslashes($table1)."`,`".addslashes($table2)."` WHERE `".addslashes($table1)."`.`".addslashes($key1)."`=`".addslashes($table2)."`.`".addslashes($key2)."`";
        //s'il y a d'autres conditions, on les rajoute
        if (isset($data)) {
            foreach ($data as $key => $value){
                $query.=" AND ".addslashes($key)." = '".addslashes($value)."'";
            }
        }

        if (isset($orderField)) {
            // Si $direction n'est pas égale à 'ASC' alors elle est égale à 'DESC'
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY ".($orderFieldTable===1?addslashes($table1):addslashes($table2)).".".addslashes($orderField)." $direction";
        }

        if (is_int($limitStart) && is_int($limitStop)) {
            $query.=" LIMIT $limitStart,$limitStop";
        }

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchOneCrossTableEqualKeys($table1,$table2,$key1,$key2,$data=null) {

        $prepare = array();

        //attention quand un id dans chaque table, retourne l'id de la $table2
        $query="SELECT * FROM `".addslashes($table1)."`,`".addslashes($table2)."` WHERE `".addslashes($table1)."`.`".addslashes($key1)."`=`".addslashes($table2)."`.`".addslashes($key2)."`";
        //s'il y a d'autres conditions, on les rajoute
        if (isset($data)) {
            foreach ($data as $key => $value){
                $query.=" AND ".addslashes($key)." like '".addslashes($value)."'";
            }
        }
        $query.=" LIMIT 1";
        // echo $query;
        $result = $this->db->exec($query, $prepare);
        return $result[0];
    }

    public function fetchOneById($table,$id) {

        // Récupère une ligne de la table
        if (is_int($id)) {
            $query="SELECT * FROM `$table` WHERE id=:id";

            $result = $this->db->exec($query, array(
                'id'=> array($id, \PDO::PARAM_INT)
            ));
            return $result[0];
        }
        return ["Must be int"];
    }

    public function fetchOneByKeysEqual($table,$data,$orderField=NULL,$direction='ASC') {
//        Récuperer la premiere ligne de db selon les clés renseignées
        // Exemple de requête préparée :
        // "SELECT * FROM user WHERE email=:email ORDER BY id ASC LIMIT 1
        // Les champs :email / :orderField correspondent aux valeurs attendues et sont settés en deuxieme parametre de la méthode exec()

        $query="SELECT * FROM `$table` WHERE ";
        $i=0;

        $prepare = array();
        foreach ($data as $key => $value){
            $prepared_key = $this->prepareRequest($prepare, $key);
            $prepare[$prepared_key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key=$prepared_key";
            $i++;
        }

        if (isset($orderField)) {
            // Si $direction n'est pas égale à 'ASC' alors elle est égale à 'DESC'
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY ".addslashes($orderField)." $direction";
        }

//        Rechercher seulement une ligne
        $query .= ' LIMIT 1';

        $result = $this->db->exec($query, $prepare);
        return $result[0];
    }

    public function fetchAllByKeysEqual($table,$data,$orderField=NULL,$direction='ASC') {
//        Récuperer toutes les lignes de db selon les clés renseignées

        $query="SELECT * FROM `$table` WHERE ";
        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepared_key = $this->prepareRequest($prepare, $key);
            $prepare[$prepared_key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key=$prepared_key";
            $i++;
        }
        if (isset($orderField)) {
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY $orderField $direction";
        }

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchLastByKeysEqual($table,$data,$orderField=NULL,$direction='ASC') {
//        Récuperer la derniere ligne de db selon les clés renseignées
        // Exemple de requête préparée :
        // "SELECT * FROM user WHERE email=:email ORDER BY id DESC LIMIT 1
        // Les champs :email correspondent aux valeurs attendues et sont settés en deuxieme parametre de la méthode exec()

        $query="SELECT * FROM `$table` WHERE ";
        $i=0;

        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key=:$key";
            $i++;
        }

        if (isset($orderField)) {
            // Si $direction n'est pas égale à 'ASC' alors elle est égale à 'DESC'
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY $orderField $direction";
        }

//        Rechercher seulement une ligne
        $query .= ' LIMIT 1';

        $result = $this->db->exec($query, $prepare);
        return $result[0];
    }

    public function fetchLast($table,$orderField='DESC') {

        // Récupère une ligne de la table
        $query="SELECT * FROM `$table` ORDER BY id ".$orderField." LIMIT 1";

        $result = $this->db->exec($query, $prepare);
        return $result[0];

    }

    public function fetchOneByKeysLike($table,$data,$orderField=NULL,$direction='ASC') {
        // rechercher une ligne specifique
        $query="SELECT * FROM `$table` WHERE ";
        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key LIKE :$key";
            $i++;
        }
        if (isset($orderField)) {
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY $orderField $direction";
        }

//        Rechercher seulement une ligne
        $query .= ' LIMIT 1';
        $result = $this->db->exec($query, $prepare);
        return $result[0];
    }

    public function fetchAllByKeysLike($table,$data,$orderField=NULL,$direction='ASC',$limitStart=NULL,$limitStop=NULL) {
        // rechercher toutes les lignes qui matchent
        $query="SELECT * FROM `$table` WHERE ";
        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key LIKE :$key";
            $i++;
        }
        if (isset($orderField)) {
            if ($direction != 'ASC') $direction = 'DESC';
            $query.=" ORDER BY $orderField $direction";
        }

        if (is_int($limitStart) && is_int($limitStop)) {
            $query.=" LIMIT $limitStart,$limitStop";
        }

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchAllInDateInterval($table,$dateKey,$start,$stop,$strict=false) {
        //retourne les lignes dont la valeur en base de $dateKey est dans l'intervalle démarrant à $start et s'arrêtant à $stop
        $query="SELECT * FROM `$table` WHERE $dateKey";

        $prepare = array();
        $prepare[':start'] = $start;
        $prepare[':stop'] = $stop;
        if ($strict) {
            $query.=">:start AND $dateKey<:stop";
        } else {
            $query.=">=:start AND $dateKey<=:stop";
        }

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchAllMatchToday ($table,$startKey,$stopKey,$strict=false) {
        //retourne les lignes dont la valeur en base de $startKey est antérieure à aujourd'hui et dont la valeur de $stopKey est postérieur à aujourd'hui
        $query="SELECT * FROM `$table` WHERE " .addslashes($startKey);
        if ($strict) {
            $query.="< CURDATE() AND " . addslashes($stopKey). "> CURDATE()";
        } else {
            $query.="<= CURDATE() AND " . addslashes($stopKey). ">= CURDATE()";
        }
        $result = $this->db->exec($query);
        return $result;
    }

    public function countByKeysEqual($table,$data) {
        // compte le nombre de lignes trouvées
        // data: Array: key (db field) => value
        $query="SELECT count(*) as total FROM `$table` WHERE ";
        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;

            if ($i!=0) $query.=" AND ";
            $query.="$key=:$key";
            $i++;
        }

        $result = $this->db->exec($query, $prepare);
        return $result[0]['total'];
    }

    public function existsValue($table, $data) {
        // Retourne tous les ids de lignes trouvées
        $query="SELECT * FROM `$table` WHERE ";
        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key like :$key";
            $i++;
        }

        $result = $this->db->exec($query, $prepare);
        if(empty($result)) return false;

        return $result[0]['id'];
        // POURQUOI PAS LA METHODE CI-DESSOUS ???
        $to_return = array();
        for ($i=0; $i < count($result); $i++) {
            array_push($to_return, $result[$i]['id']);
        }
        return $to_return;
    }

    public function fetchOneField($table,$field) {
        $query="SELECT $field FROM $table";
        $data = $this->db->exec($query);
        $result=[];
        foreach ($data as $key => $value){
            $result[$key]=$value[$field];
        }
        return $result;
    }

    public function fetchOneFieldByKeysEqual($table,$field, $data) {
        $query="SELECT DISTINCT $field FROM `$table` WHERE ";

        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key = :$key";
            $i++;
        }

        $result = $this->db->exec($query, $prepare);
        return $result;
    }

    public function fetchMax($table,$field) {
        $query="SELECT Max($field) as $field FROM $table";
        $result = $this->db->exec($query);
        return $result[0][$field];
    }

    public function fetchMaxByKeysEqual($table,$field,$data) {
        $query="SELECT Max($field) as $field FROM $table WHERE ";
        $i=0;
        $prepare = array();
        foreach ($data as $key => $value){
            $prepare[':' . $key] = $value;
            if ($i!=0) $query.=" AND ";
            $query.="$key=:$key";
            $i++;
        }
        $result = $this->db->exec($query, $prepare);
        return $result[0][$field];
    }

    public function fetchAllFields($table,$fields) {
        $query="SELECT ";

        foreach ($fields as $value){
            $query.="$value,";
        }
        $query=substr($query,0,(strlen($query)-1));
        $query.=" FROM $table";
        $result = $this->db->exec($query);
        return $result;
    }

    public function insertByArray( $table , $data, $exclude ) {

        $prepare = array();
        $fields = array();

        foreach ($data as $key => $value){
            if (!in_array($key, $exclude)) {
                array_push($fields, str_replace("_","",$key));
                $prepare[':'. str_replace("_","",$key)] = $value;
            }
        }

        $listF = "`" . implode( "`,`" , $fields). "`";
        $insert = implode( ", " , array_keys($prepare));

        $query = "INSERT INTO `$table` ( $listF )  VALUES ( $insert )";

        $this->db->exec($query, $prepare);
        return $this->db->lastInsertId();
    }

    public function updateByArrayById( $table , $data, $id ) {

        $query='';
        $prepare = array();
        foreach ($data as $key => $value){
            $prepared_field = ':'. str_replace("_","",$key);
            $prepare[$prepared_field] = $value;

            $query.=str_replace("_","",$key)."= $prepared_field,";
        }

        //on supprime la dernière virgule
        $query=substr($query,0,strlen($query)-1);
        $prepare[':id'] = array($id, \PDO::PARAM_INT);

        $query="UPDATE `$table` SET $query WHERE id = :id";

        return $this->db->exec($query, $prepare);
    }

    public function updateByKeysEqual( $table , $dataUpdated, $data ) {
        $query='';
        $prepare = array();
        foreach ($dataUpdated as $key => $value){
            // ajout de '_update' car les columns peuvent avoir le même nom
            $prepared_field = ':'. str_replace("_","",$key) . '_update';
            $prepare[$prepared_field] = $value;
            $query.=str_replace("_","",$key)."= $prepared_field,";
            //$query.=str_replace("_","",$key)."='$value',";
        }
        //on supprime la dernière virgule
        $query=substr($query,0,strlen($query)-1);
        $query="UPDATE `$table` SET $query WHERE ";
        $i=0;
        foreach ($data as $key => $value){
            $prepare[":$key"] = $value;
            if ($i!=0) $query.=" AND ";
            // Ajout des parenthèses autour de ma clé car sinon la chaine est interprétée comme une column et non pas une valeur
            $query.="$key=(:" . $key . ")";
            $i++;
        }
        return $this->db->exec($query, $prepare);
    }

    public function updateByKeysLike( $table , $dataUpdated, $data ) {
        $query='';
        $prepare = array();
        foreach ($dataUpdated as $key => $value){
            // ajout de '_update' car les columns peuvent avoir le même nom
            $prepared_field = ':'. str_replace("_","",$key) . '_update';
            $prepare[$prepared_field] = $value;
            $query.=str_replace("_","",$key)."= $prepared_field,";
            //$query.=str_replace("_","",$key)."='$value',";
        }
        //on supprime la dernière virgule
        $query=substr($query,0,strlen($query)-1);
        $query="UPDATE `$table` SET $query WHERE ";
        $i=0;
        foreach ($data as $key => $value){
            $prepare[":$key"] = $value;
            if ($i!=0) $query.=" AND ";
            // Ajout des parenthèses autour de ma clé car sinon la chaine est interprétée comme une column et non pas une valeur
            $query.="$key like (:" . $key . ")";
            $i++;
        }
        var_dump($query);
        return $this->db->exec($query, $prepare);
    }
    public function deleteById($table,$id) {

        if (is_int($id)) {
            $query="DELETE FROM `$table` WHERE id=:id";

            return $this->db->exec($query, array(
                'id'=> array($id, \PDO::PARAM_INT)
            ));
        }

        return false;
    }

    public function deleteByKeysEqual($table,$data) {
        $query="DELETE FROM `$table` WHERE ";
        $prepare = array();
        $i=0;
        foreach ($data as $key => $value){
            $prepare[":$key"] = $value;

            if ($i!=0) $query.=" AND ";
            $query.="$key=:$key";
            $i++;
        }
        return $this->db->exec($query, $prepare);
    }

    public function copyRow($table,$id) {
        //cette méthode duplique une entrée en base (sauf l'id) et retourne l'id de la nouvelle entrée
        $data_array=$this->fetchOneById($table,$id);
        $result=$this->insertByArray($table,$data_array,array('id'));
        return $result;
    }

    public function existsTable($table) {
        $query="SHOW TABLES LIKE :$table";
        $result = $this->db->exec($query, array(
            ":$table" => $table
        ));
        if (count($result)>0) {
            return true;
        } else {
            return false;
        }
    }

    public function existsColumns($table, $field) {

        // Pour vérifier qu'une colonne existe, il faut au préalable s'assurer que la table existe
        $existsTable = $this->existsTable($table);

        if ($existsTable) {
            $query="SHOW COLUMNS FROM $table";

            $result = $this->db->exec($query);

            foreach($result as $key=> $value) {
                if ($value['Field'] === $field) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }

    }

    /******************** SESSION *********************/

    public function sessionClear($key) {
        $this->f3->clear("SESSION.".$key);
        return $key.'_clear';
    }

    public function sessionSet($key,$value) {
        $this->f3->set('SESSION.'.$key,$value);
        return $key.'_set:'.$this->f3->get("SESSION.".$key);
    }


    public function sessionIdUser() {
        $cookie = $this->getHiveKey("COOKIE");
        $sessionId = 'none';
        if(isset($cookie['PHPSESSID'])) $sessionId = $cookie['PHPSESSID'];
        return $sessionId;
    }

    public function loadSession(){
        $f3 =  \Base::instance();
        $id = $f3->get('COOKIE.PHPSESSID');
        $session = new \DB\SQL\Session( $f3->get('BDD') );
        $active = trim ( $session->read($id) );
        $isLogged = 1;
        if($active == ''){
            session_start();
            $f3->set("SESSION.userId", -1);
        }
        $f3->get("SESSION");
        if( $f3->get("SESSION.userId") == -1 ) $isLogged = 0;
        $f3->set('ISLOGGED' , $isLogged);
    }


    public function flash($f3,$content,$container,$style='success',$timeout=6000) {
        $f3->set("SESSION.flashContent", $content);
        $f3->set("SESSION.flashContainer", $container);
        $f3->set("SESSION.flashStyle", $style);
        $f3->set("SESSION.flashTimeout", $timeout);
    }

    public function express($f3,$title,$body,$width='sm') {
        $f3->set("SESSION.expressTitle", $title);
        $f3->set("SESSION.expressBody", $body);
        $f3->set("SESSION.expressWidth", $width);
    }

    public function toast($f3,$toastContent,$containerStyle='toast-top-right',$toastStyle='success',$enabledTimeout=2000,$disabledTimeout=10000,$clickClose=1) {
        if ($enabledTimeout>$disabledTimeout) $disabledTimeout=$enabledTimeout+6000;
        $f3->set("SESSION.toastContent", $toastContent);
        $f3->set("SESSION.containerStyle", $containerStyle);
        $f3->set("SESSION.toastStyle", $toastStyle);
        $f3->set("SESSION.toastEnabledTimeout", $enabledTimeout);
        $f3->set("SESSION.toastDisabledTimeout", $disabledTimeout);
        $f3->set("SESSION.toastClickClose", $clickClose);
    }

    public function clearToast($f3){
        $this->sessionClear('toastHeading');
        $this->sessionClear('toastContent');
        $this->sessionClear('toastIcon');
        $this->sessionClear('toastLoader');
        $this->sessionClear('toastPosition');
    }

    public function unsetToastJson($f3){
        $this->sessionClear('toast_json');
    }

    /********************* MAIL ***********************/

    public function sendMailF3($f3,$subject,$message,$name,$email)
    {
        $smtp = $f3->get('smtp');
        $smtp->set('Content-type', 'text/html; charset=UTF-8');
        $smtp->set('From', '<nepasrepondre@jeandeportal.com>');
        $smtp->set('To', '"'.$name.'" <'.$email.'>');
        $smtp->set('Subject', $subject);
        $smtp->set('Errors-to', '<nicolasdelourme@imi-creative.fr>');
        return $smtp->send($message, TRUE);
    }


    public function sendContactMailF3($f3,$subject,$message,$name,$email)
    {
        $name = str_replace(' ', '', $name);
        $smtp = $f3->get('smtp');
        $smtp->set('Content-type', 'text/html; charset=UTF-8');
        $smtp->set('From', $name.'<'.$email.'>');
        $smtp->set('To', '"'.$f3->get('project').'" <'.$f3->get('contactMail').'>');
        $smtp->set('Subject', $subject);
        $smtp->set('Errors-to', '<'.$f3->get('project').'>');
        return $smtp->send($message, TRUE);
    }

    /******************* AJAX *************************/
    public function ajaxSelectById($f3,$table,$id,$nonce2) {
        //cette méthode affiche la ligne $id de la table $table

        $ret = '';
        $nonce2=substr($nonce2,0,22);
        $nonce2Session = $f3->get("SESSION.nonce2");
        $this->sessionClear('nonce2');

        //on vérifie que nonce et nonce2 sont identiques aux valeurs en session
        if (($nonce2==='n'.$nonce2Session.'d')&&($f3->get("nonce")=='2gjd0hjdsd321')) {
            $table_array = $this->fetchOneById($table, $id);
            foreach ($table_array as $key => $value) {
                $ret .= '[' . strtoupper($key) . ']' . $value . '[/' . strtoupper($key) . ']';
            }
        }
        return $ret;
    }

    public function ajaxExistValue($f3,$table,$key,$value,$nonce2) {

        $nonce2=substr($nonce2,0,22);
        $nonce2Session = $f3->get("SESSION.nonce2");
        $this->sessionClear('nonce2');

        //on vérifie que nonce et nonce2 sont identiques aux valeurs en session
        if (($nonce2==='n'.$nonce2Session.'d')&&($f3->get("nonce")=='2gjd0hjdsd321')) {
            return $this->existsValue($table,array($key=>$value));
        } else {
            return false;
        }
    }

    public function ajaxInsertUpdate($f3,$insert,$table,$keyString,$valueString,$excludeString,$nonce2,$id=null) {

        //cette méthode retourne l'ID de la ligne $ketString=>$valueString insérée en base dans la table $table
        //ATTENTION : si $nonce2 se termine par 1, le login de l'utilisateur est obligatoire en cession et sera ajoutée à l'insertion dans la table

        //on décode : le €€£€€ remplace le &
        $keyString=str_replace('€€£€€','&',$keyString);
        $valueString=str_replace('€€£€€','&',$valueString);

        $userIdRequired=substr($nonce2,22,1);
        $nonce2=substr($nonce2,0,22);
        $key_array=explode('$$£$$',substr($keyString,0,(strlen($keyString)-6)));
        $value_array=explode('$$£$$',substr($valueString,0,(strlen($valueString)-6)));
        $exclude_array=explode('$$£$$',substr($excludeString,0,(strlen($valueString)-6)));
        $nonce2Session = $f3->get("SESSION.nonce2");
        $this->sessionClear('nonce2');

        /*   print_r($key_array);
           echo ('<br>');
           print_r($value_array);
           echo ('<br><br>');
   */
        //on vérifie que nonce et nonce2 sont identiques aux valeurs en session
        //if (($nonce2==='n'.$nonce2Session.'d')&&($f3->get("nonce")=='2gjd0hjdsd321')) {
        $data=[];

        if ($userIdRequired){
            if (($f3->get("SESSION.userId")!=-1)&&($f3->get("SESSION.userId")!='')) {
                if ($table == 'user'){
                    //if (filter_var($key_array['email'], FILTER_VALIDATE_EMAIL))
                    $data['id'] = $f3->get("SESSION.userId");
                    //else
                    //  return ('doesnt_an_email');
                }
                else
                    $data['userId']=$f3->get("SESSION.userId");
            } else {
                return false;
            }
        }

        foreach ($key_array as $key => $value) {
            $data[$value]=$value_array[$key];
        }

        if ($insert) {
            return $this->insertByArray($table, $data, ['id']);
        } else {
            return $this->updateByArrayById($table, $data, $id);
        }

        //} else {
        //    return false;
        //}
    }



    /********************** CRON **********************/

    public function cronScheduling ($f3,$cronGroup) {

        // cette méthode exécute les cron de $cronGroup de la table cronScheduling

        $route_array=$this->fetchAllByKeysLike('cronScheduling',array('cronGroup'=>$cronGroup));

        $ch_array=[];
        $curlNumber=0;
        $mh = curl_multi_init();

        foreach ($route_array as $key1 => $value1) {

            $cronSchedule_array=explode(',',$value1['schedule']);

            foreach ($cronSchedule_array as $key2 => $value2) {

                echo $f3->get('path').$value1['route'].' '.$value2;

                if ($this->parseCronTab($value2)) {
                    echo " RUN";
                    $ch_array[$curlNumber]=curl_init();
                    curl_setopt($ch_array[$curlNumber], CURLOPT_URL, $f3->get('path').$value1['route']);
                    curl_setopt($ch_array[$curlNumber], CURLOPT_HEADER, 0);
                    curl_multi_add_handle($mh,$ch_array[$curlNumber]);
                    $curlNumber++;
                }
                echo "<br>";
            }
        }

        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        for ($i = 0; $i < $curlNumber; $i++) {
            $responseCode = curl_getinfo($ch_array[$i], CURLINFO_RESPONSE_CODE);
            $url = curl_getinfo($ch_array[$i], CURLINFO_EFFECTIVE_URL);
            $this->insertByArray('cronJournal', array('url' => $url, 'event'=> $responseCode), []);
            curl_multi_remove_handle($mh, $ch_array[$i]);
        }
        curl_multi_close($mh);

    }

    private function parseCronTab($cronSchedule,$time=null)
    {

        // cette méthode retourne true si $cronSchedule doit être exécuté à l'heure $time

        if (!isset($time)) $time=(new \DateTime())->format('Y-m-d H:i:s');

        $time=explode(' ', date('i G j n w', strtotime($time)));
        $cronSchedule=explode(' ', $cronSchedule);
        foreach ($cronSchedule as $k=>&$v)
        {$time[$k]=intval($time[$k]);
            $v=explode(',', $v);
            foreach ($v as &$v1) {
                $v1=preg_replace(array('/^\*$/', '/^\d+$/', '/^(\d+)\-(\d+)$/', '/^\*\/(\d+)$/'), array('true', $time[$k].'===\0', '(\1<='.$time[$k].' and '.$time[$k].'<=\2)', $time[$k].'%\1===0'), $v1);
            }
            $v='('.implode(' or ', $v).')';
        }
        $cronSchedule=implode(' and ', $cronSchedule);
        return eval('return '.$cronSchedule.';');
    }

    /******************** DIVERS **********************/

    public function sitePath($path) {
        $actuelPath= "http://".$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"] ;
        $slashPosition=strrpos($actuelPath,'/');
        $oldPath=substr($actuelPath,0,$slashPosition+1);
        $newPath=$oldPath.$path;
        return $newPath;
    }

    function randomString($length=20) {
        $originalString = array_merge(range(0,9), range('a','z'), range('A', 'Z'));
        $originalString = implode("", $originalString);
        return substr(str_shuffle($originalString), 0, $length);
    }

    public function nextDateOfDay (\datetime $baseDate,$day=1) {

        //cette méthode retourne la prochaine date, à partir de $baseDate, dont le numéro de jour est $day
        //par exemple : si $baseDate=='2018-09-14' et $day==10, la méthode retourne 2018-10-10.
        //si $day n'existe pas pour le mois considéré (exemple: 30 fevrier), retourne le dernier jour du mois.
        // par exemple: si baseDate=='2018-01-31' et $day==30, la methode retourne 2018-02-28

        $today = $baseDate;
        $baseDate=new \datetime($baseDate->format('Y-m').'-'.$day, new \DateTimeZone('Europe/Paris'));

        if (($today->format('d')<$day)&&($baseDate>$today)&&$baseDate->format('m') === $today->format('m')) {
            return $baseDate->format('Y-m-d H:i:s');
        } else {
            $baseDate->add(new \DateInterval('P1M'));
            $today=new \datetime($today->format('Y-m').'-01', new \DateTimeZone('Europe/Paris'));
            $today->add(new \DateInterval('P1M'));

            if ($baseDate->format('m') != $today->format('m'))
                return $today->modify('last day of this month')->format('Y-m-d H:i:s');
            else
                return ($baseDate->format('Y-m-d H:i:s'));
        }

    }

    public function addStatusColor($table) {
        //cette méthode rajoute un champ statusColor à $table en fonction de la valeur de status (1,2,3,4)

        foreach ($table as $key => $value) {
            if (intval($value['status'])===1) $table[$key]['statusColor']='primary';
            if (intval($value['status'])===2) $table[$key]['statusColor']='warning';
            if (intval($value['status'])===3) $table[$key]['statusColor']='success';
            if (intval($value['status'])===4) $table[$key]['statusColor']='danger';
        }

        return $table;

    }

    public function cutAtNextSpace ($string,$lenght,$char_array=['?','!','.',';',' ']) {

        //cette méthode retourne le début de $string coupée à l'un des caractères présent dans $char_array et avant $lenght

        $string=substr($string,0,$lenght);
        $fistReverseChar=strlen($string);


        //on recherche chaque caractère de $char_array dans la chaine inversé
        foreach ($char_array as $key => $value) {
            $reversePos=strpos(strrev($string), $value);
            if ($reversePos<$fistReverseChar) {
                $fistReverseChar=$reversePos;
            }
        }

        //si on a trouvé le caractère, on relève la position dans le sens normal
        if ($fistReverseChar!=strlen($string)) {
            $lastPos=strlen($string)-$reversePos-1;
        } else {
            $lastPos=strlen($string);
        }

        return substr($string,0,$lastPos).'…';

    }

    public function nonBreakingSpace ($string) {
        //cette méthode rajoute des &nbsp; devant les doubles ponctuations de $string

        //on isole les !important pour éviter qu'ils ne soient remplacés
        $string=$string=str_replace(" !important",'$$$$$!!important',$string);

        $string=str_replace(" !",'&nbsp;!',$string);
        $string=str_replace(" ?",'&nbsp;?',$string);
        $string=str_replace(" :",'&nbsp;:',$string);
        $string=str_replace(" ;",'&nbsp;!',$string);
        $string=str_replace(" %",'&nbsp;%',$string);
        $string=str_replace(" €",'&nbsp;€',$string);
        $string=str_replace(" &euro;",'&nbsp;&euro;',$string);
        $string=str_replace(" »",'&nbsp;»',$string);
        $string=str_replace("« ",'«&nbsp;',$string);

        $string=$string=str_replace("$$$$$!!important",' !!important',$string);

        return $string;
    }

    public function convertToDateTimeObj ($date_string) {
        //cette méthode retourne un objet dateTime si $date_string est bien une date (retourne false dans le cas inverse)
        if (!checkdate(intval(substr($date_string,5,2)), intval(substr($date_string,8,2)), intval(substr($date_string,0,4)))) {
            return false;
        } else {
            setlocale(LC_TIME, 'fr_FR.utf8','fra');
            $date=new \DateTime($date_string);
            return $date;
        }
    }

    public function objectToArray($object) {
        //cette méthode retourne un array de $object
        $_arr = is_object($object) ? get_object_vars($object) : $object;
        foreach ($_arr as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
            $result_array[$key] = $value;
        }
        return $result_array;
    }

    public function execCustomRequest($request){

        return $this->db->exec($request);
    }

    public function execVariableMethod($f3, $data){
        /*
               * cette methode permet de faire appel à N'IMPORTE QUELLE méthode publique de lignededepart et d'en afficher la valeur de retour (en JSON)
               * $_GET['entity'] represente le nom de la classe, NAMESPACE inclus (par exemple, pour faire appel a une methode de la classe imi, le champ doit être setté à "core\imi");
               * $_GET['method'] est le nom de la méthode (pour faire appel à la méthode fetchOneById() de imi, le champ est setté à 'fetchOneById');
               * chaque autre champ setté doit être le miroir d'un argument attendu dans le prototype de la méthode.
               * Pour faire appel à la méthode imi->fetchOneById($table, $id), on attendra donc un champ $_GET['table'] et un champ $_GET['id'].
               *
               * exemple de requête pour imi->fetchOneById('user', 2) ==> /curl/anyFunc?entity=core\imi&method=fetchOneById&table='user'&id=2
               */

        $method = new ReflectionMethod(new $data['entity'](), $data['method']);

        foreach($method->getParameters() as $index => $param) {
            $args[] = $data[$param->getName()];
        }
        return json_encode($method->invokeArgs(new $data['entity'], $args), true);
    }

    public function setResponseCode(){
        $headers = getallheaders();

        if (strtotime('now')  * 1000  > $headers['Timestamp']) {
            http_response_code(408);
            return false;
        }


        if ( md5(base64_encode(hash_hmac($headers['Algorithm'], $headers['Host'].$headers['Origin'].$headers['Keyid'].$headers['Timestamp'] , $this->getF3('secretKey'), true))) !== md5($headers['Authorization'])) {
            http_response_code(403);
            return false;
        }
        else {
            http_response_code(200);
            return true;
        }
    }

    public function tableReindex($array,$newKey='id') {

        $newArray=[];
        // cette méthode permet de réindexer un array en utilisant $newKey comme clé
        foreach ($array as $key => $value) {
            $newArray[$value[$newKey]]=$value;
        }
        return $newArray;

    }

    public function arraySortByColumn(&$array, $col, $dir = SORT_ASC) {
        // cette méthode tri le tableau multidimensionnel array$ par ordre de $col
        $sort_col = array();
        foreach ($array as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        return array_multisort($sort_col, $dir, $array);

    }




}
