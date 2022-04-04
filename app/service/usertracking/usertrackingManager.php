<?php
/**
 * Created by PhpStorm.
 * User: quentinchampenois
 * Date: 17/05/2019
 * Time: 10:39
 */
namespace service\usertracking;
use core\imi;

class usertrackingManager extends imi
{
    private $referrers = [ 'localhost', 'anneeblanche', 'publications-agora' ]; //ici les sites externes où le tracking est établi (cherche la présence de la sring dans l'URL)
    
    public function storeTracking($httpReferer, $data) {
        // Si la variable $isFirstTrack est true, alors on créer une nouvelle navigation où l'on stocke toutes les informations
        // Si celle-ci est à false, cela signifie que l'utilisateur est en train de naviguer, on stocke donc le HTTP_REFERER dans le field `target` et l'id du parent dans le field `parentTrackingId`
        
        $isFirstTrack = ( $data['tOrigin'] && $data['ipClient']);
        if ( $isFirstTrack ) {
            $token = $this->createNewTrack($httpReferer, $data);
            $res = array('tXhr' => $token);
        } elseif ( ! $isFirstTrack && strlen($data['tXhr']) == 32 ) {
            $token = explode(' ', $data['tXhr']);
            $track = $this->fetchOneByKeysEqual('userTracking', array(
                'token' => $token[0]
            ));
            if ( empty($track) ) {
                return "Une erreur est survenue, veuillez rouvrir un nouvel onglet";
            }
            $res = $this->trackNavigation($httpReferer, intval($track['id']));
        } else {
            $res = "Les données semblent invalides";
        }
        return $res;
    }
    
    public function createNewTrack($httpReferer, $data) {
        // Créer une nouvelle navigation parent. Cela signifie que l'utilisateur vient d'être redirigé de Jeandeportal vers un partenaire
        // Retourne le token unique servant d'identifiant du parent et stocké dans le sessionStorage "tXhr" de l'utilisateur
        
        $referrerName = $this->getReferrerName($httpReferer);
        $token = $this->token();
        $this->insertByArray('userTracking', array(
            'target' => $httpReferer,
            'parentTrackingId' => -1,
            'token' => $token,
            'referrer' => ( ! empty($referrerName) ) ? $referrerName : 'UNKNOWN REFERRER',
            'ipClient' => ( (filter_var($data['ipClient'], FILTER_VALIDATE_IP) ) ? $data['ipClient'] : "UNKNOWN IP"),
            'origin' => ( (filter_var($data['tOrigin'], FILTER_VALIDATE_URL) ) ? $data['tOrigin'] : "***" . addslashes($data['tOrigin']) )
        ), [ 'id' ] );
        
        return $token;
    }
    
    public function trackNavigation($httpReferer, $parentTrackingId) {
        // Rajoute une navigation enfant au tracking parent $parentTrackingId
        
        $this->insertByArray('userTracking', array(
            'origin' => '',
            'target' => ( (filter_var($httpReferer, FILTER_VALIDATE_URL) ) ? $httpReferer : "***" . addslashes($httpReferer) ),
            'parentTrackingId' => intval($parentTrackingId),
            'token' => '',
            'referrer' => '',
            'ipClient' => '',
        ), [ 'id' ]);
        
        return true;
    }
    
    public function navigationHistory($parentId) {
        // Récupérer toutes les informations de navigation d'un utilisateur
        // Retourne toutes les informations du parentTracking ainsi que l'ensemble des routes visitées dans l'array $track['target']
        
        $track = $this->fetchOneById('userTracking', intval($parentId));
        if ( empty($track) ) return false;
        $track['target'] = [ $track['target'] ];
        
        $userJourney = $this->fetchAllByKeysEqual('userTracking', array(
            'parentTrackingId' => intval($parentId)
        ));
        foreach ( $userJourney as $row ) {
            array_push($track['target'], $row['target']);
        }
        
        return $track;
    }
    
    public function countNavigationsByKeysEqual($data) {
        // Retourne le nombre de visites différentes enregistrées ainsi que leurs informations
        $rows = $this->fetchAllByKeysEqual('userTracking', $data);
        
        return array(
            'total' => count($rows),
            'data' => $rows
        );
    }

    private function getReferrerName($httpReferer) {
        // Compare le http_referer avec chaque élément de $this->referrers
        // Si celui-ci provient d'un partenaire alors on retoune son nom, sinon vide
        // $httpReferer => $_SERVER['HTTP_REFERER']
        $referrers = $this->referrers;
        for ($i = 0; $i < count($referrers); $i++) {
            if ( strpos($httpReferer, $referrers[$i]) !== false ) {
                $startPos = strpos($httpReferer, $referrers[$i]);
                $referrerName = substr($httpReferer, $startPos, strlen($referrers[$i]));
                break 1;
            }
        }
        return ( $referrerName ) ? $referrerName : '';
    }
    
    private function token() {
        // Créer un token unique chiffré en md5
        // Celui-ci est stocké dans le field `parentTrackingId` et stocké chez l'utilisateur dans le sessionStorage tXhr
        return md5(uniqid('', true));
    }
    
}
/*
Script Javascript à intégrer par le partenaire
VERSION MINIFIED
<script>function t_main(e,t){var s=new RegExp(e);(sessionStorage.getItem("tOrigin")||s.test(document.referrer))&&sessionStorage.getItem("tOrigin")!=document.referrer&&(sessionStorage.getItem("tXhr")?t_sendRequest(t,"tXhr="+sessionStorage.getItem("tXhr")):t_getIpClient("https://api.ipify.org?format=json",t,t_sendRequest),sessionStorage.setItem("tOrigin",document.referrer))}function t_getIpClient(e,t,s){var r=new XMLHttpRequest;r.open("GET",e,!0),r.send(),r.onreadystatechange=function(){if(4==this.readyState&&200==this.status){var e=JSON.parse(this.responseText);resData={ip:e.ip,origin:document.referrer},s(t,"ipClient="+resData.ip+"&&tOrigin="+resData.origin)}}}function t_sendRequest(e,t){var s=new XMLHttpRequest;s.open("POST",e,!0),s.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),s.send(t),s.onreadystatechange=function(){if(4==this.readyState&&200==this.status){var e=JSON.parse(this.responseText);e&&e.tXhr&&sessionStorage.setItem("tXhr",e.tXhr)}}}t_main("jeandeportal.fr", "https://jeandeportal.imi-framework.io/tracking");</script>

VERSION LONGUE
<script>
    t_main("jeandeportal.fr", "https://jeandeportal.imi-framework.io/tracking"); //([nom de domaine du site d'origine de l'utilisateur], [URL du traitement du tracking (insertion en BDD)])
    function t_main(referrer, routeRequest) {
        var rex = new RegExp(referrer);
        if ( (sessionStorage.getItem("tOrigin") || (rex.test(document.referrer))) && (sessionStorage.getItem("tOrigin")!=document.referrer) ) {
            if ( ! sessionStorage.getItem("tXhr") ) {
                t_getIpClient("https://api.ipify.org?format=json", routeRequest, t_sendRequest);
            } else {
                t_sendRequest(routeRequest, "tXhr=" + sessionStorage.getItem("tXhr"));
            }
            sessionStorage.setItem("tOrigin", document.referrer);
        }
    }
    function t_getIpClient(url, t_url, callback) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", url, true);
        xhttp.send();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var res = JSON.parse(this.responseText);
                resData = {
                    ip: res.ip,
                    origin: document.referrer
                };
                callback(t_url, "ipClient=" + resData.ip + "&&tOrigin=" + resData.origin);
            }
        };
    }

    function t_sendRequest(url, data) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhttp.send(data);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var res = JSON.parse(this.responseText);
                if ( res && res.tXhr ) {
                    sessionStorage.setItem("tXhr", res.tXhr);
                }
            }
        };
    }
</script>
*/
