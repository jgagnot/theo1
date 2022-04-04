<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 21/02/2018
 * Time: 10:47
 */

namespace service\mailchimp;

use core\imi;
use entity\user;
use entity\shoppingItem;
use service\user\authentificationManager;

class mailchimpManager extends imi
{

    public function fetchAllList($f3)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = json_decode(curl_exec($ch), true);

        return ($result);
    }

    public function registerStore($f3, $name)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'id' => $f3->get('mailchimpStoreId'),
            'list_id' => $f3->get('mailchimpListId'),
            'name' => $name,
            'currency_code' => 'EUR'
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch));
        return ($result);
    }

    public function getListMember($f3)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/members');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = json_decode(curl_exec($ch), true);

        return ($result);
    }

    public function deleteMember($f3, $mail)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/members/' . hash('MD5', $mail));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = json_decode(curl_exec($ch), true);

        return ($result);
    }

    public function registerMember($f3, $update, user $user)
    {

        $this->registerSegmentMemberAllSegmentMember($f3, $user);

        /*
        * this function insert a new subscriber in your list.
        * if status === "pending", an email will automatically be sent to the user (double optin)
        * if update === true, datas will be updated if user already exists
        */
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'members' => array(
                array('email_address' => $user->getEmail(),
                    'email_type' => 'html',
                    'status' => 'pending',
                    'merge_fields' => (array(
                        'TOKEN' => strval($user->getToken())

                    ))
                )
            ),
            'update_existing' => $update
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
        $result = json_decode(curl_exec($ch), true);

        if (isset($result['new_members']) || isset($result['updated_members']))
            return true;
        return false;
    }

    public function registerCustomer($f3, $update, user $user)
    {

        $this->registerSegmentMemberAllSegmentMember($f3, $user);

        /*
        * automatic call to parent function. If a store extist, this method will update the member's status (and the customer status)
        * This WON'T overwrite the member status (will be set in the parent function), even if the opt_in status is "false".
        * This will send the list's opt-in mail.
        * Be carefull => the method does NOT check if all the fields are correctly setted.
        */
        if ($f3->get('mailchimpMember') == true) {
            if ($this->registerMember($f3, $update, $user) == false)
                return false;
        }

        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'id' => $user->getId(),
            'email_address' => $user->getEmail(),
            'opt_in_status' => true
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/'
            . $f3->get('mailchimpStoreId'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset ($result['id']))
            return true;
        return false;
    }

    public function unsubscribeUser($f3, $mail, $listId = null)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        if ($listId === null) {
            $url = 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/members/' . hash('MD5', $mail);
        } else {
            $url = 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . hash('MD5', $mail);
        }
        $data = array(
            'status' => 'unsubscribed'
        );

        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
        $result = json_decode(curl_exec($ch), true);
        return $result;
    }

    public function checkStatus($f3, $mail)
    {
        /*
         * returns user's status. You'll need to use this function if the user IS in the database with "unsubscribed" status.
         * this method will tell you if the doubleoptin has been validated.
         */

        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' .
            $f3->get('mailchimpListId') . '/members/' . hash('MD5', $mail) . '?fields=status');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);

        return ($result['status']);
    }

    public function checkStatusPending($f3, $seniority)
    {

        //Cette méthode vérifie chez Mailchimp le statut de tous les status pending des $seniority derniers jours :
        //- si le statut est toujours pending le mail de doubleOptin est renvoyé
        //- sinon le statut est updaté en base

        $userRepository = new userRepository;
        $pending_array = $userRepository->fetchAllDoubleOptinPending($f3, $seniority);

        foreach ($pending_array as $key => $value) {
            $user = new user($value);

            if ($this->checkStatus($f3, $user->getEmail()) === 'subscribed') {
                $this->updateByArrayById('user', array('doubleOptin' => 'subscribed'), $user->getId());
                echo '<br>' . $user->getId() . ' -> subscribed';
            } else {
                $authentificationManager = new authentificationManager();
                $authentificationManager->mailDoubleOptin($f3, $user);
                echo '<br>' . $user->getId() . ' -> doubleOptin';
            }

        }

    }

    public function triggerEvent($f3, $mail, $trigger)
    {
        /*
         * this method is not used yet. This is a tool for managing events and automaticaly sending e-mails.
         * You can use this method with a non-Customer user.
         */

        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $ch = curl_init();
        $data = array(
            'merge_fields' => array(
                'EVENT' => $trigger
            )
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' .
            $f3->get('mailchimpListId') . '/members/' . hash('MD5', $mail) . '?fields=merge_fields');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if ($result['merge_fields']['EVENT'] == $trigger)
            return (true);
        return false;
    }

    /*
     * fonction qui enregistre un nouvel item dans mailchimp et le lie au store utilisé
     */

    public function registerProduct($f3, shoppingItem $item)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'id' => $item->getId(),
            'title' => $item->getName(),
            'description' => $item->getDescription(),
            'image_url' => $item->getImage(),
            'variants' => array(
                array(
                    'id' => '-1',
                    'title' => $item->getName(),
                    'visibility' => false
                )
            )
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/'
            . $f3->get('mailchimpStoreId') . '/products');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return true;
        return false;
    }

    public function updateProduct($f3, shoppingItem $item)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'id' => $item->getId(),
            'title' => $item->getName(),
            'description' => $item->getDescription(),
            'variants' => array(
                array(
                    'id' => $item->getId(),
                    'title' => $item->getName()
                )
            )
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/'
            . $f3->get('mailchimpStoreId') . '/products/' . $item->getId());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return true;
        return false;
    }

    public function updateProductVariant($f3, shoppingItem $item, shoppingVariant $variant)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'id' => $variant->getId(),
            'title' => $variant->getName(),
            'price' => $variant->getPrice(),
            'visibility' => true
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/' .
            $f3->get('mailchimpStoreId') . '/products/' . $item->getId() . '/variants/' . $variant->getId());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return true;
        return false;
    }

    public function updateVariantVisibility($f3, shoppingVariantPerItem $variantPerItem)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array('visbility' => true);
        if ($variantPerItem->getSaleStart() >= $variantPerItem->getSaleStop())
            $data = array('visbility' => false);
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/'
            . $f3->get('mailchimpStoreId') . '/products/' . $variantPerItem->getItemId() . '/variants/' . $variantPerItem->getVariantId());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return true;
        return false;
    }

    public function createOrder($f3, $basket, user $user)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $line = 0;
        foreach ($basket as $key => $value) {
            $lines[] = array(
                'id' => $line,
                'product_id' => $value['itemId'],
                'product_variant_id' => $value['productVariantId'],
                'quantity' => $value['quantity'],
                'price' => $value['price'],
                'discount' => $value['price'] - $value['discountPrice']);
            $line++;
        }

        $data = array(
            'id' => $basket->getId(),
            'customer' => $user->getClassArray(),
            'financial_status' => 'pending',
            'currency_code' => 'EUR',
            'order_total' => $basket['discountTotal'],
            'discount_total' => $basket['Total'] - $basket['discountTotal'],
            'tax_total' => $basket['tax'],
            'lines' => $lines
        );

        $jsondata = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/' . $f3->get('mailchimpStoreId') . '/orders');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (!isset($result['id']))
            return (false);
        return (true);
    }

    public function updateOrderStatus($f3, $basket, $financial_status, $fulfillment_status)
    {
        $auth = base64_encode("user:" . $f3->get('mailchimpKey'));
        $data = array(
            'financial_status' => $financial_status,
            'fulfillment_status' => $fulfillment_status
        );

        $jsondata = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/ecommerce/stores/' .
            $f3->get('mailchimpStoreId') . 'orders/' . $basket->getId() . '?fields=id,financial_status,fulfillment_status');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (!isset($result['id']))
            return (false);
        return (true);
    }

    public function createSegment($f3, $groupId, $groupName)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array(
            'name' => $groupId . '_' . $groupName,
            'static_segment' => array()
        );
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/segments');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id'])) {
            $this->updateByArrayById('userGroup', array('segmentId' => $result['id']), $groupId);
            return true;
        }
        return $result;
    }

    private function registerSegmentMemberAllSegmentMember($f3, $user)
    {
        //cette méthode (re)inscrit le $user dans tous les segments dans lesquels ils doit figurer

        $userGroup_array = $this->fetchAllCrossTableEqualKeys('userGroup', 'userPerGroup', 'id', 'groupId', array('userId' => $user->getId()));
        foreach ($userGroup_array as $key => $value) {
            $this->registerSegmentMember($f3, $value['segmentId'], $user->getEmail());
        }
    }

    public function registerSegmentMember($f3, $segmentId, $userMail)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array('members_to_add' => array(
            $userMail
        ));
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/segments/' . $segmentId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return ($result['id']);
        return false;
    }

    public function getSegmentMember($f3, $segmentId)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/segments/' . $segmentId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return $result;
        return false;
    }

    public function unregisterSegmentMember($f3, $segmentId, $userMail)
    {
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));
        $data = array('members_to_remove' => array(
            $userMail
        ));
        $jsondata = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/segments/' . $segmentId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['id']))
            return ($result['id']);
        return false;
    }

    public function getAllSegmentMemberInfo($f3, $segmentId)
    {
        /*
         * renvoie la liste de tous membres du segment. Ainsi que les informations les concernant.
         *
         */
        $auth = base64_encode('user:' . $f3->get('mailchimpApiKey'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $f3->get('mailchimpServerKey') . '.api.mailchimp.com/3.0/lists/' . $f3->get('mailchimpListId') . '/segments/' . $segmentId . '/members');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch), true);
        if (isset($result['members']))
            return $result;
        return false;
    }

    public function subscribeUser($f3, $postData)
    {
        if ($postData['type'] === 'subscribe') {
            $user = new user($this->fetchOneByKeysLike('user', array('email' => $postData['data']['email'], 'doubleOptin' => 'pending')));
            $this->updateByArrayById('user', array('doubleOptin' => 'subscribed'), $user->getId());
        }
    }

    /***************** WEBHOOKS  *************/

    public function webhookHandler($f3, $data)
    {

        $user = new user($this->fetchOneByKeysLike('user', array('email' => $data['data']['email'])));
        if ($user->getId() <= 0) {
            $user->setEmail($data['data']['email']);
            $user->setId($this->insertByArray('user', $user->getClassArray(), ['id', 'createdOn']));
        }
        $userConnect = $this->fetchOneByKeysLike('userConnect', array('userId' => intval($user->getId()), 'project' => $f3->get('project')));
        if (empty($userConnect)) {
            $this->insertByArray('userConnect', array(
                'userId' => $user->getId(),
                'project' => $f3->get('project'),
                'optinStatus' => ($data['type'] === 'subscribe') ? 'subscribed' : 'pending'
            ), ['id', 'timestamp']);
        }
        $status = $this->checkStatus($f3, $data['data']['email']);

        if ($data['type'] === 'subscribe')
            $this->updateByArrayById('userConnect', array('optinStatus' => $status), intval($userConnect['id']));
        else if ($data['type'] === 'unsubscribe')
            $this->updateByArrayById('userConnect', array('optinStatus' => 'unsubscribe'), intval($userConnect['id']));

        if ($status === 'subscribed'){
            $group_array = $this->fetchAllByKeysLike('userPerGroup', array('userId' => intval($user['id'])));

            foreach($group_array as $key => $value){
                $group = $this->fetchOneById('userGroup', $value['groupId']);
                if (!empty($group['segmentId'])){
                    $this->registerSegmentMember($f3, $group['segmentId'], $user['email']);
                }
            }
        }
    }
}