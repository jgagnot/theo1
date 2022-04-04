<?php
/**
 * Created by PhpStorm.
 * User: qchampenois
 * Date: 01/04/2019
 * Time: 11:00
 */

namespace service\mandrill;

use controller\developmentController;
use core\imi;

class mandrillManager extends imi
{

    public function getUser($f3)
    {
        // Recupere les infos de l'utilisateur mandrill
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
        );
        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/users/info.json', $jsondata);
        return ($result);
    }

    public function getSenders($f3)
    {
        // Recupere chaque comptes mails utilisés pour envoyer des mails depuis mandrill
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
        );
        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/users/senders.json', $jsondata);
        return ($result);
    }

    public function sendMail($f3, Array $message, Array $receiver)
    {
        // Envoyer un mail à un destinataire

        // Params :
        // message: Array
        //      - html: String => full HTML content to be sent
        //      - text: String => OPTIONAL full text content to be sent
        //      - subject: String => message subject
        //      - from_name: String => OPTIONAL Name sender
        //      - optionals: Array => OPTIONAL rajouter des champs optionnels dans l'array Message
        //          - tags: Array de Strings, images: Array de structs, attachments: Array de structs, etc...

        // receiver: Array - Infos du destinataire
        //      - email: String
        //      - name: String
        //      - type: String => Default 'to'

        // ERRORS :
        // - Rejected_reason : Unsigned => domaine d'envoi avec DKIM & SPF non validés


        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'message' => array(
                'html' => (!empty($message['html'])) ? $message['html'] : '',
                'text' => (!empty($message['text'])) ? $message['text'] : '',
                'subject' => $message['subject'],
                'from_email' => $f3->get('mandrillMail'),
                'from_name' => (!empty($message['from_name'])) ? $message['from_name'] : '',
                'to' => [$receiver]
            )
        );

        // Rajouter des options supplémentaires dans l'array message
        if (!empty($message['optionals'])) {
            foreach ($message['optionals'] as $key => $value) {
                $data["message"][$key] = $value;
            }
        }
        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/messages/send.json', $jsondata);
        return ($result);
    }

    public function sendContactMail($f3, $subject, $mail, $from, $message){
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'message' => array(
                'subject' => (empty($subject))? 'contact' : $subject,
                'from_email' => 'jeremygagnot@imi-creative.fr',
                'from_name' => $from,
                'to' => array(
                    array(
                        'email' => $f3->get('contactMail')
                    )
                ),
                'global_merge_vars' => array(
                    array(
                        'name' => 'name',
                        'content' => $from
                    ),
                    array(
                        'name' => 'email',
                        'content' => $mail
                    ),
                    array(
                        'name' => 'message',
                        'content' => $message
                    )
                )
            ),
            'template_name' => 'contact_mail',
            'template_content' =>''
        );


        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/messages/send-template.json', $jsondata);
        return ($result);
    }

    // TEMPLATES
    public function sendMailTemplate($f3, $subject, $userId, Array $template)
    {
        // Envoyer un mail avec template à un destinataire

        // Params :
        // subject:- subject du mail...

        // userId: id du user ciblé

        // template: Array - Infos du template
        //      - template_name: String => Préconiser le slug, le name est aussi valide
        //      - template_content: Array de structs (1 struct par bloc editable dans le template )
        //          - name: String => Nom du bloc editable du template
        //          - content: String => Contenu du bloc editable ciblé
        //      - global_merge_vars: Array de structs => OPTIONAL variables globales qui s'appliquent pour tous les destinataires (exemple : un lien vers une offre promo )
        //          - name: String => Nom variable globale
        //          - content: String
        //      - merge_vars: Array de structs => OPTIONAL variables locales pour s'adapter à chaque destinataire (exemple : Nom, montant, etc )
        //          - rcpt: String => email du destinaire
        //          - vars: Array de structs
        //              - name: String => Nom variable locale
        //              - content: String
        $user = $this->fetchOneById('user', intval($userId));

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'message' => array(
                'subject' => $subject,
                'from_email' => $f3->get('mandrillMail'),
                'from_name' => $f3->get('mandrillSender'),
                'to' => array(
                    array(
                        'email' => $user['email']
                    )
                ),
                'global_merge_vars' => $template['global_merge_vars'],
                'merge_vars' => $template['merge_vars'],
            ),
            'template_name' => $template['template_name'],
            'template_content' => $template['template_content']
        );

        // Rajouter des options supplémentaires dans l'array message
        if (!empty($message['optionals'])) {
            foreach ($message['optionals'] as $key => $value) {
                $data["message"][$key] = $value;
            }
        }

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/messages/send-template.json', $jsondata);
        return ($result);
    }

    public function getMail($f3, $id_mail)
    {
        // Récupérer un mail
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'id' => $id_mail
        );

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/messages/info.json', $jsondata);
        return ($result);
    }

    public function createAttachment($mimetype, $name, $path)
    {
        //  Créer une structure pour intégrer une image dans le mail ou rajouter une pièce jointe
        // Params
        // format: String => format de l'image 'png', 'jpg', etc
        // name: String =>
        //      - Pour créer un attachement, renseigner le nom du fichier
        // path: String => chemin du fichier

        // ATTENTION, a priori l'image ne s'affiche qu'une fois dans le mail et non pas dans une préview du mail (cf : https://stackoverflow.com/questions/33730115/mandrill-embed-image-cannot-get-interpreted-properly )

        $content = base64_encode(file_get_contents($path));
        return array(
            'type' => $mimetype,
            'name' => $name,
            'content' => $content
        );
    }

    public function getListTemplates($f3, $label = '')
    {
        // Récupérer la liste des templates sur le compte

        // Params :
        // label: String => OPTIONAL Filtrer les templates par label

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'label' => $label
        );

        $jsondata = json_encode($data);


        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/templates/list.json', $jsondata);
        return ($result);
    }

    public function getHistoryTemplate($f3, $name)
    {
        // Récupère l'historique d'un template pour les 30 derniers jours

        // Params :
        // name: String => nom d'un template


        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'name' => $name
        );

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/templates/time-series.json', $jsondata);
        return ($result);
    }

    // SUBACCOUNTS => Passer par le BackOffice Mandrill
    public function getListSubAccounts($f3, $prefix = '')
    {
        // Recupere la liste des subaccounts

        // Arg : prefix: String (OPTIONAL) => filtrer par prefix
        // ERRORS :

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'q' => $prefix
        );
        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/subaccounts/list.json', $jsondata);
        return ($result);
    }

    public function getSubaccount($f3, $id_subaccount)
    {
        // Récupère les infos d'un subaccount en fonction de son id

        // Arg : id: String => id du nom du subaccount
        // ERRORS :

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'id' => $id_subaccount
        );
        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/subaccounts/info.json', $jsondata);
        return ($result);
    }

    public function addSubAccount($f3, $id, $name = '', $notes = '')
    {
        // Créer un subaccount
        // Params :
        // - id : String -> un seul mot / number : example "devTest"
        // - name: String  (OPTIONAL)- Detail du nom du subaccount : "Dev Test Subaccount"
        // - notes: String (OPTIONAL)
        // ERRORS :

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'id' => $id,
            'name' => $name,
            'notes' => $notes
        );
        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/subaccounts/add.json', $jsondata);
        return ($result);
    }

    // TAGS => Plus simple par le backoffice Mandrill
    public function getListTags($f3)
    {
        // Liste de tous les tags

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
        );

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/tags/list.json', $jsondata);
        return ($result);
    }

    public function getTag($f3, $tag_name)
    {
        // Récupérer un seul tag

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'tag' => $tag_name
        );

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/tags/info.json', $jsondata);
        return ($result);
    }

    // WEBHOOKS
    public function getListWebhooks($f3)
    {
        // Récupère les infos d'un webhook
        $data = array(
            'key' => $f3->get('mandrillApiKey')
        );

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/webhooks/list.json', $jsondata);

        // Si le $result n'est pas encodé, retourne "Array"
        return json_encode($result);
    }

    public function getWebhook($f3, $id_webhook)
    {
        // Récupérer les infos d'un webhook

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'tag' => $id_webhook
        );

        $jsondata = json_encode($data);

        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/webhooks/info.json', $jsondata);
        return ($result);
    }

    public function addWebhook($f3, $url, $description = '', Array $events = [])
    {
        // Créer un nouveau webhook
        // url: String => url POST du webhook pour recevoir les events

        // ATTENTION: Pour la validation d'url, mandrill envoi d'abord une requête GET dessus en attendant un code 200, sinon une requête POST, ensuite s'il n'y a pas de réponse une erreur de validation est levée
        //https://mandrill.zendesk.com/hc/en-us/articles/205583227-Why-can-t-my-webhook-or-inbound-route-URL-be-verified-
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'url' => $url,
            'description' => $description,
            'events' => $events
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/webhooks/add.json', $jsondata);
        return json_encode($result);
    }


    public function updateWebhook($f3, $id, $url, $description = '', Array $events = [])
    {
        // Update un webhook

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'id' => $id,
            'url' => $url,
            'description' => $description,
            'events' => $events
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/webhooks/update.json', $jsondata);
        return json_encode($result);
    }

    public function deleteWebhook($f3, $id)
    {
        // Supprime un webhook

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'id' => $id
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/webhooks/delete.json', $jsondata);
        return json_encode($result);
    }


    // INBOUND => L'objectif n'est pas de remplacer une boite mail, mais permet de recevoir des mails des users de l'appli et de les envoyés en POST sur les webhooks en place
    // NOTE: Ce système de mails entrants nécessite un webhook actif

    public function getListInbound($f3)
    {
        // List des inbound

        $data = array(
            'key' => $f3->get('mandrillApiKey')
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/inbound/domains.json', $jsondata);
        return json_encode($result);
    }

    public function addInbound($f3, $domain)
    {
        // Ajoute une adresse entrante


        // domain: String => domaine de l'adresse mail
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            "domain" => $domain
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/inbound/add-domain.json', $jsondata);
        return json_encode($result);
    }

    public function checkDomainInbound($f3, $domain)
    {
        // Verifie le status mx d'un domaine


        // domain: String => domaine de l'adresse mail
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            "domain" => $domain
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/inbound/check-domain.json', $jsondata);
        return json_encode($result);
    }

    public function deleteInbound($f3, $domain)
    {
        // Verifie le status mx d'un domaine


        // domain: String => domaine de l'adresse mail
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            "domain" => $domain
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/inbound/delete-domain.json', $jsondata);
        return json_encode($result);
    }

    public function getRoutesInbound($f3, $domain)
    {
        // List the mailbox routes defined for an inbound domain

        // domain: String => domaine de l'adresse mail
        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            "domain" => $domain
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/inbound/routes.json', $jsondata);
        return json_encode($result);

    }

    public function addRouteInbound($f3, $domain, $pattern, $url_webhook)
    {
        // Add a new mailbox route to an inbound domain
        // domain: String => domaine de l'adresse mail
        // pattern: String => Le pattern que le nom de la boite mail doit matcher
        // url_webhook: String => L'url du webhook où les messages doivent s'afficher

        $data = array(
            'key' => $f3->get('mandrillApiKey'),
            'domain' => $domain,
            'pattern' => $pattern,
            'url' => $url_webhook
        );

        $jsondata = json_encode($data);
        $result = $this->curl_requests('https://mandrillapp.com/api/1.0/inbound/add-route.json', $jsondata);
        return json_encode($result);

    }

    public function setTemplate($f3, $template_name, Array $links_n_colors, Array $order_details = [], Array $attachments = [])
    {
        // template_name: String => Permet de définir le contenu de l'array Message ( Subject, tags, et autres )
        // links_n_colors: Array => Liens et couleurs du templates
        //      - links: Array => Liens utiles
        //          - link_validation: String => lien du button
        //          - link_support: String => Lien de support en cas de problème
        //      - colors: Array => Couleurs du template
        //          - main: String
        //          - secondary: String

        // receiver: Array - Infos du destinataire
        //      - email: String
        //      - name: String
        //      - type: String => Default 'to'
        // receiver: Array - Couleurs primaires et secondaires du template
        //      - main: String
        //      - secondary: String
        // order_details: Array => OPTIONAL Information de la commande
        //      - products: Array de structs - Produits à afficher dans le template
        //          - image_url: String
        //          - name: String
        //          - description: String
        // attachments: Array de structs => OPTIONAL, Les pieces jointes peuvent être faites avec la méthode createAttachment()
        $message = array(
            'subject' => 'reconfirmation de souscription',

        );

        $message = $this->templateSetParams($f3, $template_name, $attachments);

                if ( ! $message ) {
                    return false;
                }



        $template = [
            'template_name' => $template_name,

            'global_merge_vars' => [
                array(
                    // Copyright Footer
                    'name' => 'year',
                    'content' => 2019
                ),
                array(
                    // Logo Header
                    'name' => 'logo',
                    'content' => $f3->get('path') . 'project/' . $f3->get('projectName') . '/images/' . $f3->get('logo')
                ),
                array(
                    'name' => 'company',
                    'content' => $f3->get('projectName')
                ),
                array(
                    // Définir la couleur de la charte graphique
                    'name' => 'main_color',
                    'content' => $links_n_colors['colors']['main']
                ),
                array(
                    // Définir la couleur de la charte graphique
                    'name' => 'secondary_color',
                    'content' => $links_n_colors['colors']['secondary']
                ),
                array(
                    'name' => 'link_validation',
                    'content' => $links_n_colors['links']['link_validation']
                ),
                array(
                    'name' => 'link_support',
                    'content' => $links_n_colors['links']['link_support']
                ),
                array(
                    'name' => 'time',
                    'content' => $order_details["date"]
                ),
                array(
                    'name' => 'products',
                    'content' => $order_details["products"]
                ),
                array(
                    'name' => 'total',
                    'content' => $order_details["total"]
                )
            ]
        ];

        return array(
            'message' => $message,
            'template' => $template
        );
    }

    public function templateSetParams($f3, $template_name, Array $attachments = [])
    {
        // Définir le subject et les tags d'un template, pour chaque nouveau template, définir son case avec son nom
        // template_name: String
        switch ($template_name) {
            case 'recuperation-motdepasse':
                $message = array(
                    'subject' => 'Récupération mot de passe ' . $f3->get('projectName'),
                    'optionals' => [
                        'tags' => [
                            'forgot_password',
                        ],
                        'merge_language' => 'handlebars'
                    ]
                );
                break;
            case 'confirmation-inscription':
                $message = array(
                    'subject' => 'Confirmation inscription ' . $f3->get('projectName'),
                    'optionals' => [
                        'tags' => [
                            'confirmation_inscription',
                        ],
                        'merge_language' => 'handlebars'
                    ]
                );
                break;
            case 'confirmation-achat':
                $message = array(
                    'subject' => 'Confirmation d\'achat ' . $f3->get('projectName'),
                    'optionals' => [
                        'tags' => [
                            'confirmation_achat',
                        ],
                        'merge_language' => 'handlebars'
                    ]
                );
                break;
            case 'panier-abandonne':
                $message = array(
                    'subject' => 'Panier sur ' . $f3->get('projectName') . ' toujours disponible',
                    'optionals' => [
                        'tags' => [
                            'abandonned_cart',
                            'panier-abandonne',
                        ],
                        'merge_language' => 'handlebars'
                    ]
                );
                break;
            default:
                return false;
                break;
        }

        if (!empty($attachments)) {
            $message['optionals']['attachments'] = $attachments;
        }
        return $message;
    }

    public function curl_requests($path, $jsondata)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        return json_decode(curl_exec($ch), true);
    }

}
