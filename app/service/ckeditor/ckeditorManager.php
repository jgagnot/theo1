<?php
/**
 * Created by PhpStorm.
 * User: quentinchampenois
 * Date: 02/05/2019
 * Time: 10:39
 */

namespace service\ckeditor;
use core\imi;

class ckeditorManager extends imi
{
    // NOTE: Helper d'implémentation en bas de fichier (classic / inline)


    public function setClassicType($f3, $postType, $idTextarea='', $imageResize=[300, 'auto']) {
        // retourne l'array de ressources nécessaire pour initialiser le script ckeditor classic

        // $postType: String REQUIRED => champ 'tinyScript' de la table 'postType'. Définit la toolbar qui sera settée
        // $idTextarea: OPTIONNEL => Id du textarea où implémenter l'éditeur. Si vide, par défaut 'htmlContent'
        // $imageResize: OPTIONNEL => Dimensions pour le redimensionnement automatique lors de l'upload d'images dans l'éditeur

        $cdnUrl = 'https://cdn.ckeditor.com/4.11.4/full-all/ckeditor.js';
        $adaptersCdnUrl = 'https://cdn.ckeditor.com/4.11.4/full-all/adapters/jquery.js';
        $cssUrl = $f3->get('path') . 'project/' . $f3->get('project') . '/css/ckeditor.css';
        $type = 'classic';

        return array(
            'cdn' => $cdnUrl,
            'adaptersCdn' => $adaptersCdnUrl,
            'css' => $cssUrl,
            'type' => $type,
            'postType' => $postType,
            'idTextarea' => (! empty($idTextarea)) ? $idTextarea : 'htmlContent',
            'imageResize' => array(
                'width' => $imageResize[0],
                'height' => $imageResize[1]
            )
        );
    }

    public function setInlineType($f3, $imageResize=[300, 'auto']) {
        // retourne l'array de ressources nécessaire pour initialiser le script ckeditor inline

        // $imageResize: OPTIONNEL => Dimensions pour le redimensionnement automatique lors de l'upload d'images dans l'éditeur

        $cdnUrl = 'https://cdn.ckeditor.com/4.11.4/full-all/ckeditor.js';
        $adaptersCdnUrl = 'https://cdn.ckeditor.com/4.11.4/full-all/adapters/jquery.js';
        $cssUrl = $f3->get('path') . 'project/' . $f3->get('project') . '/css/ckeditor.css';
        $type = 'inline';

        return array(
            'cdn' => $cdnUrl,
            'adaptersCdn' => $adaptersCdnUrl,
            'css' => $cssUrl,
            'type' => $type,
            'imageResize' => array(
                'width' => $imageResize[0],
                'height' => $imageResize[1]
            )
        );
    }

    public function buildStructure($f3, $templateName, $data) {
        // Créer la structure adaptée à ckeditor dans la view
        // EXAMPLE structure d'un element : div > span + element + span
        $viewPath = $this->existTemplate($f3, $templateName);

        if ( empty($viewPath) ) return false;

        // Récupération de l'ensemble du contenu de la vue
        $code = file_get_contents($viewPath[0]);

        for ($i = 0; $i < count($data['oldValues']); $i++) {
            $pos = strpos($code, $data['oldValues'][$i]);
            if ($pos !== false) {
                $code = substr_replace($code, $data['editorValues'][$i], $pos, strlen($data['oldValues'][$i]));
            }
        }
        $res = file_put_contents($viewPath[0], $code);

        if ( ! $res ) {
            return false;
        }

        return true;
    }

    public function createNewTemplateFile($f3, $templateName, $data, $backupLimit=3) {
        // Remplace les anciennes valeurs de la vue $templateName par les nouvelles
        // Si $backupLimit > 0, créer un un backup de la vue initiale

        // $templateName: String => NomDuDossierDeView/NomDuTemplate.html.twig
        // $data: Array de structs
        //      $data = [
        //        {
        //            'key': '',
        //            'value': ''
        //        }, ]

        // Return : True si le backup s'est bien effectué, sinon false si une erreur est survenue

        $viewPath = $this->existTemplate($f3, $templateName);

        if ( empty($viewPath) ) return false;

        if ($backupLimit > 0) {
             $this->createBackup($f3, $templateName, $viewPath[0], $backupLimit);
        } else {
            $folderArray = $this->getBackups($f3, $templateName);
            if ( ! empty($folderArray['backupPath']) ) {
                if ( ! empty($folderArray['fileList'])) {
                    foreach($folderArray['fileList'] as $backup) {
                        unlink($backup);
                    }
                }
                rmdir($folderArray['backupPath'][0]);
            }
        }
        // Récupération de l'ensemble du contenu de la vue
        $code = file_get_contents($viewPath[0]);

        // Eviter les backdoors classiques
        $invalidWords = ['passthru', 'shell_exec', 'system\(', 'phpinfo', 'chmod', 'mkdir', 'fopen', 'fclose', 'readfile', '<?php', '?>'];

        foreach($data as $value) {
            // Boucle optionnelle pour les backdoors
            foreach($invalidWords as $word) {
                if ( strpos($value['value'], $word) !== false ) {
                    return false;
                }
            }

            // Match du contenu dans la vue
            preg_match("'<span name=\"" . $value['key'] ."\"></span>(.*?)<span></span>'si", $code, $matches);
            // remplacement de l'ancien contenu par le nouveau
            $oldText = $matches[0];
            $newText = str_replace($matches[1], $value['value'], $matches[0]);

            $code = str_replace($oldText, $newText, $code);
        }
        $res = file_put_contents($viewPath[0], $code);
        if ( ! $res ) {
            return false;
        }
        return true;
    }

    public function existTemplate($f3, $templateName) {
        // Verifie que le template appelé existe
        // retourne un array vide ou un array avec le path

        // Glob() vérifie qu'un chemin est valide, si le fichier n'existe pas il retourne un array vide, sinon il retourne le chemin dans un array
        $exists = glob('project/' . $f3->get('project') . '/views/' . $templateName);

        return $exists;
    }

    public function existOrCreateBackupFolder($f3, $templateName, $isExistTemplate=false) {
        // Verifie que le dossier de backup existe sinon le créer
        // Retourne array avec le path

        // Glob() vérifie qu'un chemin est valide, si le fichier n'existe pas il retourne un array vide, sinon il retourne le chemin dans un array

        // Vérifie que template existe sinon retourne false
        if ($isExistTemplate) {
            $existsTemplate = $this->existTemplate($f3, $templateName);

            if (empty($existsTemplate)) {
                return false;
            }
        }

        $backupFolder = explode('.', $templateName);
        $backupFolderName = $backupFolder[0] . '_backup';
        $backupPath = 'project/' . $f3->get('project') . '/views/' . $backupFolderName;
        $exists = glob($backupPath);

        if (empty($exists)) {
            mkdir($backupPath);
            return [$backupPath];
        }
        return $exists;
    }

    public function createBackup($f3, $templateName, $templateNamePath, $backupLimit) {
        // Créer un backup de $templateName
        $date=new \DateTime();
        $date = $date->format('Y-m-d_H:i:s');
        // Faire le backup
        // Récupérer les chemins de tous les backups ou vide
        $backups = $this->getBackups($f3, $templateName);
        // S'il y a plus de backups que la limite, on supprime le plus ancien (triés par date)
        if ( count($backups['fileList']) >= $backupLimit ) {
            $countToRemove = count($backups['fileList']) - $backupLimit;
            for ($i = 0; $i <= $countToRemove; $i++) {
                // Supprimer tous les backups en trop et supprimer leur chemin dans l'array $backups['fileList']
                unlink($backups['fileList'][0]);
                array_shift($backups['fileList']);
            }
        }
        $templateName = explode('/', $templateName);

        $getFileName = explode('.', $templateName[count($templateName) - 1]);
        $fileName = $getFileName[0];
        copy($templateNamePath, $backups['backupPath'][0] . '/' . $fileName . '_' . strval($date) );
        return $backups;
    }
    //ok
    public function getBackups($f3, $templateName) {
        // Récupérer les différents backups pour un template et retourne leur chemin
        // Retourne un array avec le path du dossier backup ainsi que la liste des backups disponibles

        // S'il n'existe pas encore de backups pour cette vue, on le créer
        $backupPath = $this->existOrCreateBackupFolder($f3, $templateName);
        $fileName = explode('/', $templateName);

            $getFileName = explode('.', $fileName[count($fileName) - 1]);

        $fileName = $getFileName[0];

        // Récupérer la liste de tous les backups du template
        $fileList = glob($backupPath[0] . '/' . $fileName . '_*');
        return array(
                    'backupPath' => $backupPath,
                    'fileList' => $fileList
                );

    }

    public function rollback($f3, $templateBackupPath) {
        // Permet de revenir à une version précédente, tout en créant un backup de la version actuelle
        // $templateBackupPath : String => 'viewsFolder/templateBackupFolder/templateBackupName'

        // Séparer le dossier de backup et le fichier de backup
        $arrayPath = explode('/', $templateBackupPath);

        // Explode du nom de fichier de backup pour récupérer juste le nom initial
        $templateName = explode('_', $arrayPath[count($arrayPath) - 1]);
        // EXAMPLE $templateName = ['fichier', '2019-04-06', '22:54:34']
        // EXAMPLE fichier avec des '_' $templateName = ['fichier' 'suite du fichier', '2019-04-06', '22:54:34']
        // Suppression de la date et de l'heure du nom de fichier pour récupérer l'original ( Retirer les deux derniers index )
        array_pop($templateName);
        array_pop($templateName);
        if ( count($templateName) > 1 ) { // Si jamais le nom du fichier contient des '_', on reconstitue le nom du fichier
            $templateName = [implode('_', $templateName)];
        }

        array_pop($arrayPath); // Suppression du nom de fichier
        $pathBackupFolder = implode('/',$arrayPath);
        array_pop($arrayPath); // Suppression du dossier de backup

        // Pour les fichiers de premier niveau, $folderPath est vide
        $folderPath = implode('/', $arrayPath);
        if ( ! empty($folderPath)) { // Si le fichier n'est pas au premier niveau
            $folderPath .= '/';
        }

        // Vérifier l'existence du template
        $fileTemplate = $this->existTemplate($f3, $folderPath . $templateName[0].  '.html.twig');

        if (empty($fileTemplate)) return false;
        $date=new \DateTime();
        $date = $date->format('Y-m-d_H:i:s');

        // Renommer la view originale en backup
        rename($fileTemplate[0], 'project/' . $f3->get('project') . '/views/' . $pathBackupFolder .  '/' . $templateName[0] . '_' . strval($date) );

        // Vérifier l'existence du backup recherché
        $fileTemplateBackup = $this->existTemplate($f3, $templateBackupPath);

        // Besoin de passer par une copie du backup renommé avec le nom du template initial puis une suppression pour que les changements soient détectés
        copy($fileTemplateBackup[0], $fileTemplate[0]);
        unlink($fileTemplateBackup[0]);
        return true;
    }

    public function setSessionPreview(Array $data, Array $postType) {
        // Créer un array preview en session pour permettre la visualisation du post dans le template prévu
        // $data: Array de structs =>
        // $_POST = [
        //    'data' => [],
        //    'htmlContent' => String ( Contenu html du textarea ckeditor classic )

        $post_array = [];
        foreach($data['data'] as $key => $line){
            $post_array[$line['name']] = $line['value'];
        }
        $post_array['html'] = $data['htmlContent'];

        $_SESSION['preview'] = [
            'post' => $post_array,
            'postType' => array(
                'id'=> $postType['id'],
                'ckScript'=> $postType['ckScript'],
                'name'=> $postType['name'],
                'viewTemplate'=> $postType['viewTemplate'],
                )
        ];

        return $_SESSION['preview'];
    }
}
