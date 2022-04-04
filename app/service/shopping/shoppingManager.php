<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 24/04/2019
 * Time: 13:21
 */

namespace service\shopping;

use core\imi;
use entity\abonnementBillingScheme;
use entity\abonnementPlan;
use entity\abonnementReferencePerAbonnement;
use entity\shoppingStore;
use entity\shoppingItemPerStore;
use entity\shoppingItem;
use entity\shoppingReferencePerItem;
use entity\shoppingProductPerReference;
use entity\shoppingProduct;
use entity\shoppingPricePerReference;
use entity\shoppingCoupon;
use repository\shoppingStoreRepository;
use service\payment\paypalManager;
use service\payment\stripeManager;
use entity\paymentMeanPaymentPerUser;
use service\mandrill\mandrillManager;
use entity\abonnement;

class shoppingManager extends imi
{
    /*
     *      BOUTIQUE
     */

    public function getStore($f3, $storeId)
    {
        $store = new shoppingStore($this->fetchOneById('shoppingStore', $storeId));

        return $store;
    }

    public function getFullStoreArray($f3, $storeId = null)
    {
        $store = $this->fetchOneById('shoppingStore', $storeId);
        $store['item_array'] = $this->getItemArray($f3, new shoppingStore($store));
        $store['abonnement_array'] = $this->getAbonnementArray($f3, new shoppingStore($store));

        return $store;
    }

    public function getFullCatalog($f3)
    {

        $catalog['item_array'] = $this->getItemCatalog($f3);
        $catalog['abonnement_array'] = $this->getAbonnementCatalog($f3);

        return $catalog;
    }

    private function getItemCatalog($f3)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();
        $item_array = $this->fetchAllByKeysEqual('shoppingItem', array('actived' => true));

        foreach ($item_array as $itemKey => $item) {

            $reference_array = $shoppingStoreRepository->fetchReferencePerItem($f3, $item['id']);

            foreach ($reference_array as $referenceKey => $reference) {
                $product_array = $this->fetchAllCrossTableEqualKeys('shoppingProductPerReference', 'shoppingProduct', 'productId', 'id', array('shoppingProductPerReference.referenceId' => $reference['id']));
                $reference_array[$referenceKey]['product_array'] = $this->productArrayEnrichment($product_array);
                $reference_array[$referenceKey]['price_array'] = $this->fetchAllByKeysLike('shoppingPricePerReference', array('referenceId' => $reference['id']));
                $reference_array[$referenceKey]['image_array'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerReference', 'id', 'imageId', array('referenceId' => $reference['id']));
                $reference_array[$referenceKey]['stock'] = $this->stockCalculation($f3, $product_array);
            }
            $item_array[$itemKey]['store_array'] = $this->fetchAllCrossTableEqualKeys('shoppingItemPerStore', 'shoppingStore', 'storeId', 'id', array('actived' => 'true', 'itemId' => intval($item['id'])));
            $item_array[$itemKey]['reference_array'] = $reference_array;
            $item_array[$itemKey]['feature_array'] = $this->fetchAllCrossTableEqualKeys('shoppingFeature', 'shoppingFeaturePerEntity', 'id', 'itemId', array('itemId' => $item['itemId'], 'itemFeature' => true));
            $item_array[$itemKey]['image_array'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerItem', 'id', 'imageId', array('itemId' => $item['itemId']));
        }
        return $item_array;
    }

    public function getItemArray($f3, shoppingStore $store)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();

        if ($store->getId() >= 0)
            $item_array = $shoppingStoreRepository->fetchItemStore($store->getId());

        return $this->itemArrayEnrichment($f3, $item_array, $store);
    }

    public function getAllProduct($f3, $storeId = null)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();
        $product_array = ($storeId === null) ? $this->fetchAll('shoppingProduct') : $shoppingStoreRepository->fetchProductPerStore($f3, intval($storeId));
        if (isset ($product_array))
            foreach ($product_array as $key => $value)
                $product_array[$key]['productId'] = $product_array[$key]['id'];

        return $this->productArrayEnrichment($product_array);
    }

    public function getProduct($f3, $productId)
    {
        $product_array = $this->fetchOneById('shoppingProduct', intval($productId));

        if (isset ($product_array))
            $product_array['productId'] = $product_array['id'];

        return $this->productArrayEnrichment(array(0 => $product_array));
    }

    public function getItemByKeysLike($f3, $storeId, $data)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();

        if (($store = new shoppingStore($this->fetchOneById('shoppingStore', intval($storeId)))) === null)
            return "error: store_doesn't_exists";
        return $this->itemArrayEnrichment($f3, $shoppingStoreRepository->fetchItemStore($store->getId(), $data), $store);
    }

    public function addReference($f3, $storeId, $referenceId, $quantity, $priceId, $release, $couponId = -1)
    {
        $basket = new \Basket();
        if (($reference = new shoppingReferencePerItem($this->fetchOneById('shoppingReferencePerItem', intval($referenceId)))) === null)
            return "error: reference_doesn't_exist";
        if (($item = new shoppingItem($this->fetchOneById('shoppingItem', intval($reference->getItemId())))) === null)
            return "error: item_doesn't_exist";
        if (($price = new shoppingPricePerReference($this->fetchOneById('shoppingPricePerReference', intval($priceId)))) === null)
            return "error: price_doesn't_exist";

        if (count($basket->find('referenceId', $referenceId))) {
            if (($f3->get('shoppingBasketQuantityAdd')) && ($couponId == -1)) {
                $basket->load('referenceId', $referenceId);
                $quantity = $quantity + $basket->get('quantity');
                $basket->set('quantity', intval($quantity));
                $basket->save();
                $basket->reset();
            }
        } else {
            $basket->set('itemId', $item->getId());
            $basket->set('referenceId', $referenceId);
            $basket->set('reference', $reference->getReference());
            $basket->set('name', $item->getName());
            $basket->set('priceId', $price->getId());
            $basket->set('price', $price->getPrice());
            $basket->set('HTPrice', $price->getHTPrice());
            $basket->set('discountPrice', $price->getDiscountPrice());
            $basket->set('HTDiscount', $price->getHTDiscount());
            $basket->set('vat', $price->getVat());
            $basket->set('currency', $price->getCurrency());
            $basket->set('quantity', intval($quantity));
            $basket->set('couponId', intval($couponId));
            $basket->set('release', $release);
            $basket->set('storeId', $storeId);
            $basket->save();
            $basket->reset();
        }
        return "basket_success";
    }

    public function changeQuantityReference($f3, $referenceId, $quantity, $couponCode = NULL)
    {

        $basket = new \Basket();
        if (count($basket->find('referenceId', $referenceId))) {
            $basket->load('referenceId', $referenceId);
            if ($quantity === 0)
                $basket->erase('referenceId', $referenceId);
            else {
                $basket->set('quantity', intval($quantity));
                $basket->save();
                $basket->reset();
            }
            return "basket_success";
        }
        return "error: wrong_reference";
    }

    public function basketArrayReceipt()
    {

        $basket = new \Basket();
        $basket_array = $basket->find();

        $tax = 0;
        $total = 0;
        $discountTotal = 0;
        $referenceNumber = 0;

        foreach ($basket_array as $key => $value) {
            $return['referenceList'][$key]['itemId'] = $value['itemId'];
            $return['referenceList'][$key]['referenceId'] = $value['referenceId'];
            $return['referenceList'][$key]['reference'] = $value['reference'];
            $return['referenceList'][$key]['name'] = $value['name'];
            $return['referenceList'][$key]['priceId'] = $value['priceId'];
            $return['referenceList'][$key]['price'] = $value['price'];
            $return['referenceList'][$key]['HTPrice'] = $value['HTPrice'];
            $return['referenceList'][$key]['discountPrice'] = $value['discountPrice'];
            $return['referenceList'][$key]['HTDiscount'] = $value['HTDiscount'];
            $return['referenceList'][$key]['vat'] = $value['vat'];
            $return['referenceList'][$key]['currency'] = $value['currency'];
            $return['referenceList'][$key]['quantity'] = $value['quantity'];
            $return['referenceList'][$key]['couponId'] = $value['couponId'];
            $return['referenceList'][$key]['storeId'] = $value['storeId'];
            $total = $total + intval($value['price'] * $value['quantity']);
            $discountTotal = $discountTotal + intval($value['discountPrice'] * $value['quantity']);
            $tax = $tax + intval($value['discountPrice'] - $value['HTPrice']);
            $referenceNumber += $value['quantity'];

            $return['referenceList'][$key]['image_array'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerItem', 'id', 'imageId', array('itemId' => $value['itemId']), 'sort', 2, 'ASC', 0, 1)[0];
            $return['referenceList'][$key]['item_array'] = $this->fetchOneByKeysEqual('shoppingItem', array('id' => $value['itemId']));

        }

        $return['length'] = $basket->count();
        $return['receipt']['referenceNumber'] = $referenceNumber;
        $return['receipt']['tax'] = $tax;
        $return['receipt']['total'] = $total;
        $return['receipt']['discountTotal'] = $discountTotal;

        return $return;
    }

    public function dropBasket($f3, $storeId = NULL, $couponCode = NULL)
    {
        $basket = new \Basket();
        $basket->drop();
        $this->sessionClear('orderId');
        $this->sessionClear('paymentTimetable_array');

    }

    public function setShippingPrice($f3, $shippingRuleId)
    {
        $basket = $this->basketArrayReceipt();

        $rule = $this->fetchOneById('shippingRules', intval($shippingRuleId));
        if (empty($basket['length']))
            return array('ruleId' => intval($rule['id']), 'HTPrice' => 0, 'total' => 0);

        if ($rule['rule'] === 'unique') {
            return array('ruleId' => intval($rule['id']), 'HTPrice' => intval($rule['HTPrice']), 'total' => intval($rule['price']));
        } else if ($rule['rule'] === 'step') {

            $step_number = ceil($basket['receipt']['referenceNumber'] / $rule['step']);
            return array('ruleId' => intval($rule['id']), 'HTPrice' => intval($rule['HTPrice']) * $step_number, 'total' => intval($rule['price']) * $step_number);

        }
        return null;

    }


    public function createOrder($f3, $userAdressId, $userId, $shippingRuleId = 0)
    {
        $basket = new \Basket();
        $basket_array = $basket->find();

        //on insère l'order en base
        $orderId = $this->insertByArray('shoppingOrder', array(
                'storeId' => $basket_array[0]['storeId'],
                'userId' => intval($userId),
                'userAdressId' => intval($userAdressId),
                'shippingRuleId' => intval($shippingRuleId),
                'shippingPrice' => $this->setShippingPrice($f3, $shippingRuleId)['total'],
                'status' => 'pending'
            ), []);

            $this->updateByArrayById('shoppingOrder', array('name' => date_format(new \datetime('now', new \DateTimeZone('Europe/Paris')), "Y-m-d") . $orderId), intval($orderId));

        //on insère les lignes de l'order en base
        foreach ($basket_array as $key => $value) {
            $this->insertByArray('shoppingOrderLine', array(
                    'orderId' => $orderId,
                    'itemId' => $value['itemId'],
                    'referenceId' => $value['referenceId'],
                    'priceId' => $value['priceId'],
                    'price' => $value['price'],
                    'vat' => $value['vat'],
                    'discountPrice' => $value['discountPrice'],
                    'quantity' => $value['quantity'],
                    'couponId' => $value['couponId'],
                    'release' => $value['release'])
                , []);
        }
        return $orderId;
    }

    public function updateOrder($f3, $orderId, $userAdressId, $userId, $shippingRuleId = 0)
    {
        $basket = new \Basket();
        $basket_array = $basket->find();

        $this->updateByArrayById('shoppingOrder', array(
            'userId' => intval($userId),
            'userAdressId' => intval($userAdressId),
            'shippingRuleId' => intval($shippingRuleId),
            'shippingPrice' => $this->setShippingPrice($f3, $shippingRuleId)['total'],
        ), intval($orderId));

        $this->deleteByKeysEqual('shoppingOrderLine', array('orderId' => intval($orderId)));
        //on insère les lignes de l'order en base
        foreach ($basket_array as $key => $value) {
            $this->insertByArray('shoppingOrderLine', array(
                    'orderId' => $orderId,
                    'itemId' => $value['itemId'],
                    'referenceId' => $value['referenceId'],
                    'priceId' => $value['priceId'],
                    'price' => $value['price'],
                    'vat' => $value['vat'],
                    'discountPrice' => $value['discountPrice'],
                    'quantity' => $value['quantity'],
                    'couponId' => $value['couponId'],
                    'release' => $value['release'])
                , []);
        }
        return $orderId;
    }

    public function getReferenceArrayPerItem($f3, $itemId)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();

        $reference_array = $shoppingStoreRepository->fetchReferencePerItem($f3, $itemId);

        foreach ($reference_array as $referenceKey => $reference) {
            $product_array = $this->fetchAllCrossTableEqualKeys('shoppingProductPerReference', 'shoppingProduct', 'productId', 'id', array('shoppingProductPerReference.referenceId' => $reference['id']));
            $reference_array[$referenceKey]['product_array'] = $this->productArrayEnrichment($product_array);
            $reference_array[$referenceKey]['price_array'] = $this->fetchAllByKeysLike('shoppingPricePerReference', array('referenceId' => $reference['id']));
            $reference_array[$referenceKey]['stock'] = $this->stockCalculation($f3, $product_array);
        }
        return $reference_array;
    }

    private function itemArrayEnrichment($f3, $item_array, shoppingStore $store)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();

        foreach ($item_array as $itemKey => $item) {
            $reference_array = $shoppingStoreRepository->fetchReferencePerItem($f3, $item['itemId']);

            foreach ($reference_array as $referenceKey => $reference) {
                $product_array = $this->fetchAllCrossTableEqualKeys('shoppingProductPerReference', 'shoppingProduct', 'productId', 'id', array('shoppingProductPerReference.referenceId' => $reference['id']));
                $reference_array[$referenceKey]['product_array'] = $this->productArrayEnrichment($product_array);
                $reference_array[$referenceKey]['price_array'] = $this->fetchAllByKeysLike('shoppingPricePerReference', array('referenceId' => $reference['id']));
                $reference_array[$referenceKey]['image_array'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerReference', 'id', 'imageId', array('referenceId' => $reference['id']));
                $reference_array[$referenceKey]['stock'] = $this->stockCalculation($f3, $product_array);
            }

            $item_array[$itemKey]['reference_array'] = $reference_array;
            $item_array[$itemKey]['feature_array'] = $this->fetchAllCrossTableEqualKeys('shoppingFeature', 'shoppingFeaturePerEntity', 'id', 'itemId', array('itemId' => $item['itemId'], 'itemFeature' => true));
            $item_array[$itemKey]['image_array'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerItem', 'id', 'imageId', array('itemId' => $item['itemId']));

            //si la wishList est activée on vérifie que l'item n'est pas déjà dedans

            if ($store->getWishListActived() == 1) {
                if ($this->existsValue('shoppingWishList', array('userId' => $f3->get('SESSION.userId'), 'itemId' => $item['itemId']))) {
                    $item_array[$itemKey]['wishListActived'] = 0;
                } else {
                    $item_array[$itemKey]['wishListActived'] = 1;
                }
            }
        }
        return $item_array;
    }

    private function productArrayEnrichment($product_array)
    {
        foreach ($product_array as $key => $value) {
            $product_array[$key]['image_array'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerProduct', 'id', 'imageId', array('productId' => $value['productId']));
            $product_array[$key]['feature_array'] = $this->fetchAllCrossTableEqualKeys('shoppingFeature', 'shoppingFeaturePerEntity', 'id', 'featureId', array('productId' => $value['productId'], 'productFeature' => true));

        }
        return $product_array;
    }

    private function stockCalculation($f3, $product_array)
    {
        $stock = null;

        foreach ($product_array as $product) {
            $product['stock'] = $this->fetchLastByKeysEqual('shoppingProductMovement', array('productId' => intval($product['id'])))['newQuantity'];
            if ($stock === null || $stock > floor($product['stock'] / $product['productQuantity']))
                $stock = floor($product['stock'] / $product['productQuantity']);
        }
        return $stock;
    }


    /*
     *      COUPONS
     */


    private function isValidCoupon($code, $storeId)
    {

        //cette méthode contrôle la validité d'un coupon et retourne l'ID du coupon s'il est valide (ou un message commençant par "bad" dans le cas contraire)

        $coupon_array = $this->fetchOneByKeysEqual('shoppingCoupon', array('code' => $code));

        if (count($coupon_array) > 0) {
            $coupon = new shoppingCoupon($coupon_array);
            $now = new \datetime('now', new \DateTimeZone('Europe/Paris'));
            $start = new \datetime($coupon->getStart(), new \DateTimeZone('Europe/Paris'));
            $end = new \datetime($coupon->getEnd(), new \DateTimeZone('Europe/Paris'));

            if ($coupon->getStoreId() == $storeId) {
                if (($start < $now) && ($end > $now)) {
                    if (($coupon->getUserGroupId() == -1) && ($coupon->getUserId() == -1)) {
                        return $coupon->getId();
                    } else {
                        return 'badUser';
                    }
                } else {
                    return 'badTime';
                }
            } else {
                return 'badStore';
            }
        } else {
            return 'badCoupon';
        }

    }

    private function couponCalculation($id, $item_array)
    {

        $coupon = new shoppingCoupon($this->fetchOneById('shoppingCoupon', $id));
        $freeItem_array = explode(' ', $coupon->getFreeItem());

        foreach ($item_array as $key1 => $value1) {

            //si storeUniquePrice alors on applique storeUniquePrice à tous les items dont le price est au moins aussi élevé
            if ($coupon->getStoreUniquePrice() != false) {
                if ($coupon->getStoreUniquePrice() < $item_array[$key1]['price']) {
                    $item_array[$key1]['discountPrice'] = $coupon->getStoreUniquePrice();
                    $item_array[$key1]['couponId'] = $coupon->getId();
                }
            }

            //si storePercentage alors on applique storePercentage à tous les items
            if ($coupon->getStorePercentage() != false) {
                $item_array[$key1]['discountPrice'] = $item_array[$key1]['price'] * (1 - ($coupon->getStorePercentage() * 0.01));
                $item_array[$key1]['couponId'] = $coupon->getId();
                //on fait de même pour les éventuels variants
            }

            //si oneDiscountEachQuantity alors on compte la quantité d'items dans le basket et applique oneDiscountEachPercentage si : (nombre_items+1)/oneDiscountEachQuantity est un entier
            if ($coupon->getOneDiscountEachQuantity() != false) {
                $basket = new \Basket();
                if (is_integer(($basket->count() + 1) / $coupon->getOneDiscountEachQuantity())) {
                    if ($coupon->getStoreUniquePrice()) {
                        $price = 'discountPrice';
                    } else {
                        $price = 'price';
                    }
                    $item_array[$key1]['discountPrice'] = $item_array[$key1][$price] * (1 - ($coupon->getOneDiscountEachPercentage() * 0.01));
                    $item_array[$key1]['couponId'] = $coupon->getId();
                }
            }

            //si oneFreeEachQuantity alors on compte la quantité d'articles dans le panier et applique la gratuité si ce (nombre+1)/oneOfferEachQuantity est un entier
            if ($coupon->getOneFreeEachQuantity() != false) {
                $basket = new \Basket();
                if (is_integer(($basket->count() + 1) / $coupon->getOneFreeEachQuantity())) {
                    $item_array[$key1]['discountPrice'] = 0;
                    $item_array[$key1]['couponId'] = $coupon->getId();
                }
            }

            //si firstDiscountPercentage est vrai et que le basket est vide alors on applique le discount à l'item
            if ($coupon->getFirstDiscountPercentage() != false) {
                $basket = new \Basket();
                if ($basket->count() == 0) {
                    if ($coupon->getStoreUniquePrice()) {
                        $price = 'discountPrice';
                    } else {
                        $price = 'price';
                    }
                    $item_array[$key1]['discountPrice'] = $item_array[$key1][$price] * (1 - ($coupon->getFirstDiscountPercentage() * 0.01));
                    $item_array[$key1]['couponId'] = $coupon->getId();
                }
            }

            //si firstFree est vrai et que le basket est vide alors on applique la gratuité à l'article
            if ($coupon->getFirstFree()) {
                $basket = new \Basket();
                if ($basket->count() == 0) {
                    $item_array[$key1]['discountPrice'] = 0;
                    $item_array[$key1]['couponId'] = $coupon->getId();
                }
            }

            //si getFreeItem est différent de false et que l'item est dans freeItem_array alors on applique la gratuité à l'item
            if ($coupon->getFreeItem() != false) {
                if (in_array($value1['itemId'], $freeItem_array)) {
                    $item_array[$key1]['discountPrice'] = 0;
                    $item_array[$key1]['couponId'] = $coupon->getId();
                }
            }
        }
        return $item_array;
    }

    /******************* CHECKOUT ***********************/

    public function initMeanPayment($f3, $userId, $storeId, $shippingRuleId = 0)
    {
        $basket_array = $this->basketArrayReceipt();
        $shippingPrice = $this->setShippingPrice($f3, $shippingRuleId)['total'];
        $paymentOption_array = $this->fetchAllByKeysEqual('paymentOptionPerStore', array('storeId' => intval($storeId)));
        $paymentTimeTable = [];

        foreach ($paymentOption_array as $key => $value) {
            $paymentTimeTable[] = $this->paymentTimetable($basket_array, $value, $shippingPrice);
        }
        return $paymentTimeTable;
    }

    public function getUserMeanPayment($f3, $userId, $storeId)
    {
        $stripeManager = new stripeManager();

        $meanPaymentStore_array = $this->fetchAllByKeysEqual('paymentOptionPerStore', array('storeId' => intval($storeId)));

        if (array_search('stripe', array_column($meanPaymentStore_array, 'type')) !== false) {
            $meanPaymentUser_array = $stripeManager->fetchStripeCustomer($f3, $userId);

        }
        return $meanPaymentUser_array;
    }

    public function getMeanPaymentPerStore($f3, $storeId)
    {
        return explode(',', $this->fetchOneById('shoppingStore', intval($storeId))['meanPayment']);
    }

    public function payment($f3, $option, $userId, $orderId, $meanChargeId = -1)
    {
        $stripeManager = new stripeManager();
        $today = new \datetime('now', new \DateTimeZone('Europe/Paris'));
        $returnData = null;

        if ($option['optionType'] === 'stripe') {
            foreach ($option['paymentList'] as $key => $payment) {
                $paymentId = $this->insertByArray('payment', array(
                    'userId' => intval($userId),
                    'paymentOriginType' => 'SHOP',
                    'paymentOriginId' => intval($orderId),
                    'amount' => intval($payment['amount']),
                    'currency' => "eur",
                    'execution' => $payment['date']->format('Y-m-d H:i:s'),
                    'meanChargeType' => 'stripePayment',
                    'meanChargeId' => $meanChargeId
                ), []);
                if ($payment['date']->format('Y-m-d') == $today->format('Y-m-d')) {
                    $returnData = $stripeManager->createIntent($f3, $userId, intval($payment['amount']), $option['paymentOptionId'], $paymentId);
                }
            }
        }

        return array('success', $returnData);
    }

    public function abortPayment($f3, $orderId)
    {
        $payment_array = $this->fetchAllByKeysEqual('payment', array('paymentOriginType' => 'SHOP', 'paymentOriginId' => intval($orderId)));

        foreach ($payment_array as $key => $payment) {
            $this->deleteById('payment', intval($payment['id']));
            $this->deleteByKeysEqual('paymentStripePaymentIntent', array('paymentId' => intval($payment['id'])));
        }
    }


    private function paymentTimetable($basket_array, $paymentOption, $shippingPrice)
    {
        $paymentTimetable = [];
        $today = new \datetime('now', new \DateTimeZone('Europe/Paris'));

        //on applique les differés d'envoi (firstShipping et deferredShipping) aux releases
        for ($i = 0; $i < $paymentOption['paymentNumber']; $i++) {

            $paymentAmount = round(($basket_array['receipt']['discountTotal'] + $shippingPrice) / $paymentOption['paymentNumber']);
            //on corrige le dernier paiement pour que le total corresponde au panier
            if ($i == $paymentOption['paymentNumber'] - 1) {
                $paymentAmount = $basket_array['receipt']['discountTotal'] + $shippingPrice - ($paymentAmount * ($paymentOption['paymentNumber'] - 1));
            }

            if ($i == 0) {
                $paymentDate = new \datetime('now', new \DateTimeZone('Europe/Paris'));
            } else {
                $paymentDate = new \datetime($paymentTimetable['paymentList'][$i - 1]['date']->format('Y-m-d H:i:s'), new \DateTimeZone('Europe/Paris'));
                $paymentDate = $paymentDate->add(new \DateInterval($paymentOption['dateInterval']));
            }

            $paymentTimetable['paymentList'][$i]['date'] = $paymentDate;
            $paymentTimetable['paymentList'][$i]['amount'] = $paymentAmount;
            $paymentTimetable['paymentList'][$i]['paymentOptionId'] = $paymentOption['id'];
        }
        $firstAmount = $paymentTimetable['paymentList'][0]['amount'];
        $paymentTimetable['paymentList'][0]['amount'] = $paymentTimetable['paymentList'][$paymentOption['paymentNumber'] - 1]['amount'];
        $paymentTimetable['paymentList'][$paymentOption['paymentNumber'] - 1]['amount'] = $firstAmount;
        $paymentTimetable['paymentOptionId'] = $paymentOption['id'];
        $paymentTimetable['optionType'] = $paymentOption['type'];
        $paymentTimetable['name'] = $paymentOption['name'];
        return $paymentTimetable;
    }


    /********* BACKOFFICE  ******/

    public function createProduct($f3, $name, $description, $immaterial, $physical, $buyPrice, $weight, $width, $height, $depth, $actived = true)
    {
        if ($immaterial === false) {
            if ($width <= 0)
                return array('error', 'invalid_width');
            if ($height <= 0)
                return array('error', 'invalid_height');
            if ($depth <= 0)
                return array('error', 'invalid_depth');
        }

        $productId = $this->insertByArray('shoppingProduct', array(
            'name' => htmlspecialchars($name),
            'description' => htmlspecialchars($description),
            'buyPrice' => floatval($buyPrice) * 100,
            'weight' => floatval($weight),
            'width' => floatval($width),
            'height' => floatval($height),
            'depth' => floatval($depth),
            'immaterial' => boolval($immaterial),
            'physical' => boolval($physical),
            'actived' => boolval($actived)
        ), []);

        if ($productId > 0)
            return array('success', array('product_array' => $this->fetchOneById('shoppingProduct', intval($productId))));
        else
            return array('error', 'unexpected_error');
    }

    public function updateProduct($f3, $productId, $name, $description, $immaterial, $physical, $buyPrice, $weight, $width, $height, $depth, $actived = true)
    {
        if ($immaterial === false) {
            if ($width <= 0)
                return array('error', 'invalid_width');
            if ($height <= 0)
                return array('error', 'invalid_height');
            if ($depth <= 0)
                return array('error', 'invalid_depth');
        }
        $this->updateByArrayById('shoppingProduct', array(
            'name' => htmlspecialchars($name),
            'description' => htmlspecialchars($description),
            'buyPrice' => floatval($buyPrice) * 100,
            'weight' => floatval($weight),
            'width' => floatval($width),
            'height' => floatval($height),
            'depth' => floatval($depth),
            'immaterial' => boolval($immaterial),
            'physical' => boolval($physical),
            'actived' => boolval($actived)
        ), intval($productId));

        return array('success', array('product_array' => $this->fetchOneById('shoppingProduct', intval($productId))));
    }

    public function setProductImage($f3, $productId, $imageId)
    {
        if ($productId <= 0)
            return array('error', 'wrong_product');
        if ($imageId <= 0)
            return array('error', 'wrong_image');

        $oldImage = $this->fetchOneByKeysEqual('imagePerProduct', array('productId' => intval($productId)));

        if (isset($oldImage['id']))
            $this->updateByArrayById('imagePerProduct', array('imageId' => intval($imageId)), intval($oldImage['id']));
        else
            $this->insertByArray('imagePerProduct', array('imageId' => intval($imageId), 'productId' => intval($productId)), []);
        return array('success', array('image_array' => $this->fetchAllCrossTableEqualKeys('imagePerProduct', 'image', 'imageId', 'id', array('productId' => intval($productId)))));
    }

    public function createItem($f3, $name, $subname, $description, $tag, $visible, $seoUrl, $seoTitle, $seoMetaDescription, $view)
    {
        if (count($verif = $this->fetchAllByKeysLike('shoppingItem', array('seoUrl' => htmlspecialchars($seoUrl), 'actived' => true))) !== 0) {
            return array('error', 'seoUrl_used');
        }

        $itemId = $this->insertByArray('shoppingItem', array(
            'name' => htmlspecialchars($name),
            'subName' => htmlspecialchars($subname),
            'description' => $description,
            'tag' => htmlspecialchars($tag),
            'seoUrl' => htmlspecialchars($seoUrl),
            'seoTitle' => htmlspecialchars($seoTitle),
            'visible' => boolval($visible),
            'seoMetaDescription' => htmlspecialchars($seoMetaDescription),
            'view' => htmlspecialchars($view),
        ), []);
        if ($itemId > 0)
            return array('success', array('item_array' => $this->fetchOneById('shoppingItem', intval($itemId))));
        else
            return array('error', 'unexpected_error');
    }

    public function updateItem($f3, $itemId, $name, $subname, $description, $tag, $visible, $seoUrl, $seoTitle, $seoMetaDescription, $view)
    {
        if (count($verif = $this->fetchAllByKeysLike('shoppingItem', array('seoUrl' => htmlspecialchars($seoUrl), 'actived' => true))) !== 0 && intval($verif[0]['id']) !== intval($itemId)) {
            return array('error', 'seoUrl_used');
        }

        $this->updateByArrayById('shoppingItem', array(
            'name' => htmlspecialchars($name),
            'subName' => htmlspecialchars($subname),
            'description' => $description,
            'tag' => htmlspecialchars($tag),
            'seoUrl' => htmlspecialchars($seoUrl),
            'seoTitle' => htmlspecialchars($seoTitle),
            'visible' => boolval($visible),
            'seoMetaDescription' => htmlspecialchars($seoMetaDescription),
            'view' => htmlspecialchars($view),
        ), intval($itemId));


        return array('success', array('item_array' => $this->fetchOneById('shoppingItem', intval($itemId))));
    }

    public function fetchItem($f3, $itemId)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();

        $item_array = $this->fetchOneById('shoppingItem', intval($itemId));
        $item_array['store_array'] = $this->fetchAllByKeysLike('shoppingItemPerStore', array('itemId' => intval($itemId)));
        $item_array['feature_array'] = $this->fetchAllCrossTableEqualKeys('shoppingFeature', 'shoppingFeaturePerEntity', 'id', 'featureId', array('shoppingFeaturePerEntity.itemId' => intval($itemId)));
        $item_array['image_array'] = $this->fetchAllCrossTableEqualKeys('imagePerItem', 'image', 'imageId', 'id', array('itemId' => intval($itemId)), 'sort');
        $reference_array = $shoppingStoreRepository->fetchReferencePerItem($f3, intval($itemId));

        foreach ($reference_array as $key => $value) {
            $reference_array[$key]['price_array'] = $this->fetchAllByKeysLike('shoppingPricePerReference', array('referenceId' => intval($value['id'])));
            $reference_array[$key]['product_array'] = $this->fetchAllCrossTableEqualKeys('shoppingProductPerReference', 'shoppingProduct', 'productId', 'id', array('shoppingProductPerReference.referenceId' => intval($value['id'])));
            $reference_array[$key]['feature_array'] = $this->fetchAllCrossTableEqualKeys('shoppingFeature', 'shoppingFeaturePerEntity', 'id', 'featureId', array('referenceId' => intval($value['id'])));
            $reference_array[$key]['image_array'] = $this->fetchAllCrossTableEqualKeys('imagePerReference', 'image', 'imageId', 'id', array('referenceId' => intval($value['id'])), 'sort');
        }
        $item_array['reference_array'] = $reference_array;

        return $item_array;
    }


    public function createReference($f3, $reference_array, $product_array, $price_array)
    {

        if ($this->existsValue('shoppingReferencePerItem', array('reference' => $reference_array['reference'])))
            return array('error', 'reference_used');
        if ($this->existsValue('shoppingItem', array('id' => intval($reference_array['itemId']))) === false)
            return array('error', 'wrong_item');
        if (count($product_array) <= 0)
            return array('error', 'empty_reference');
        foreach ($product_array as $key => $value) {
            if (!$this->existsValue('shoppingProduct', array('id' => intval($value['productId']))))
                return array('error', 'wrong_product');
        }

        $insertion_array = array(
            'itemId' => $reference_array['itemId'],
            'name' => htmlspecialchars($reference_array['name']),
            'subname' => htmlspecialchars($reference_array['subname']),
            'reference' => htmlspecialchars($reference_array['reference']),
            'description' => htmlspecialchars($reference_array['description']),
            'tag' => htmlspecialchars($reference_array['tag']),
            'overstock' => boolval($reference_array['overstock']),
            'available' => boolval($reference_array['available']),
            'visible' => boolval($reference_array['visible']),
            'actived' => true
        );
        if (isset($reference_array['saleStart']))
            $insertion_array['saleStart'] = $reference_array['saleStart'];
        if (isset($reference_array['saleStop']))
            $insertion_array['saleStop'] = $reference_array['saleStop'];

        $referenceId = $this->insertByArray('shoppingReferencePerItem', $insertion_array, []);

        if (intval($referenceId) <= 0)
            return array('error', 'unexpected_error');
        foreach ($product_array as $key => $value) {
            $this->insertByArray('shoppingProductPerReference', array('productId' => intval($value['productId']), 'referenceId' => intval($referenceId), 'productQuantity' => intval($value['productQuantity'])), []);
        }

        foreach ($price_array as $key => $value) {
            $this->insertByArray('shoppingPricePerReference', array(
                'storeId' => intval($value['storeId']),
                'referenceId' => intval($referenceId),
                'currency' => htmlspecialchars($value['currency']),
                'price' => intval($value['price']) * 100,
                'HTPrice' => round(intval($value['price']) * 100 / (1 + ($value['vat']) / 100)),
                'vat' => floatval($value['vat']),
                'discountPrice' => intval($value['discountPrice']) * 100,
                'HTDiscount' => round(intval($value['discountPrice']) * 100 / (1 + ($value['vat']) / 100))
            ), []);
        }

        return array('success', array('reference_array' => $this->fetchReference($f3, $referenceId)));
    }

    public function updateReference($f3, $reference_array, $product_array, $price_array)
    {
        if (intval($reference_array['id']) === 0) {
            return array('error', 'unexpected_error');
        }
        if (count($verif = $this->fetchAllByKeysEqual('shoppingReferencePerItem', array('reference' => htmlspecialchars($reference_array['reference'])))) !== 0 && intval($verif[0]['id']) !== intval($reference_array['id'])) {
            return array('error', 'reference_used');
        }
        if ($verif[0]['reference'] != $reference_array['reference']) {
            $result = $this->createReference($f3, $reference_array, $product_array, $price_array);
            if ($result[0] === "success")
                $this->updateByArrayById('shoppingReferencePerItem', array('actived' => false), intval($reference_array['id']));
            return $result;
        }

        $cmp = $this->compareProductPerReference($reference_array, $product_array);
        if ($cmp[0] === 'error')
            return $cmp;


        $update_array = [];
        if (isset($reference_array['name']))
            $update_array['name'] = htmlspecialchars($reference_array['name']);
        if (isset($reference_array['subname']))
            $update_array['subname'] = htmlspecialchars($reference_array['subname']);
        if (isset($reference_array['tag']))
            $update_array['tag'] = htmlspecialchars($reference_array['tag']);
        if (isset($reference_array['description']))
            $update_array['description'] = htmlspecialchars($reference_array['description']);
        if (isset($reference_array['actived']))
            $update_array['actived'] = boolval($reference_array['actived']);
        if (isset($reference_array['saleStart']))
            $update_array['saleStart'] = $reference_array['saleStart'];
        if (isset($reference_array['saleStop']))
            $update_array['saleStop'] = $reference_array['saleStop'];
        if (isset($reference_array['visible']))
            $update_array['visible'] = boolval($reference_array['visible']);
        if (isset($reference_array['available']))
            $update_array['available'] = boolval($reference_array['available']);
        if (isset($reference_array['overstock']))
            $update_array['overstock'] = boolval($reference_array['overstock']);


        $this->updateByArrayById('shoppingReferencePerItem', $update_array, intval($reference_array['id']));

        foreach ($price_array as $key => $value) {
            if (($oldPrice = $this->existsValue('shoppingPricePerReference', array('storeId' => $value['storeId'], 'referenceId' => $reference_array['id'], 'currency' => $value['currency'], 'vat' => $value['vat'], 'price' => $value['price'] * 100, 'discountPrice' => $value['discountPrice'] * 100, 'active' => true))) === false) {
                $this->updateByKeysEqual('shoppingPricePerReference', array('active' => false), array('storeId' => $value['storeId'], 'referenceId' => $reference_array['id']));
                $this->insertByArray('shoppingPricePerReference', array(
                    'storeId' => intval($value['storeId']),
                    'referenceId' => intval($reference_array['id']),
                    'currency' => htmlspecialchars($value['currency']),
                    'price' => intval($value['price']) * 100,
                    'HTPrice' => round(intval($value['price']) * 100 / (1 + ($value['vat']) / 100)),
                    'vat' => floatval($value['vat']),
                    'discountPrice' => intval($value['discountPrice']) * 100,
                    'HTDiscount' => round(intval($value['discountPrice']) * 100 / (1 + ($value['vat']) / 100))
                ), []);
            }
        }
        return array('success', array('reference_array' => $this->fetchReference($f3, intval($reference_array['id']))));
    }

    public function fetchReference($f3, $referenceId)
    {
        $reference_array = $this->fetchOneById('shoppingReferencePerItem', intval($referenceId));
        $reference_array['price_array'] = $this->fetchAllByKeysLike('shoppingPricePerReference', array('referenceId' => intval($referenceId)));
        $reference_array['product_array'] = $this->fetchAllCrossTableEqualKeys('shoppingProductPerReference', 'shoppingProduct', 'productId', 'id', array('shoppingProductPerReference.referenceId' => intval($referenceId)));
        $reference_array['feature_array'] = $this->fetchAllCrossTableEqualKeys('shoppingFeature', 'shoppingFeaturePerEntity', 'id', 'featureId', array('referenceId' => intval($referenceId)));
        $reference_array['image_array'] = $this->fetchAllCrossTableEqualKeys('imagePerReference', 'image', 'imageId', 'id', array('referenceId' => intval($referenceId)));
        return ($reference_array);
    }

    private function compareProductPerReference($reference_array, $product_array)
    {
        $oldProduct_array = $this->fetchAllCrossTableEqualKeys('shoppingProductPerReference', 'shoppingProduct', 'productId', 'id', array('shoppingProductPerReference.referenceId' => intval($reference_array['id'])));
        $cmp1 = [];
        $cmp2 = [];
        foreach ($oldProduct_array as $key => $value) {
            $cmp1[intval($value['productId'])] = intval($value['productQuantity']);
        }

        foreach ($product_array as $key => $value) {
            $cmp2[($value['productId'])] = ($value['productQuantity']);
        }
        sort($cmp1);
        sort($cmp2);
        if ($cmp1 == $cmp2)
            return array('success');
        else {
            if ($this->existsValue('shoppingOrderLine', array('referenceId' => intval($reference_array['id']))) > 0)
                return array('error', 'order_exists');
            else if (intval($reference_array['id']) > 0) {
                $this->deleteByKeysEqual('shoppingProductPerReference', array('referenceId' => intval($reference_array['id'])));
                foreach ($product_array as $key => $product) {
                    $this->insertByArray('shoppingProductPerReference', array(
                        'referenceId' => intval($reference_array['id']),
                        'productId' => intval($product['productId']),
                        'productQuantity' => intval($product['productQuantity'])), []);
                }
                return array('success');
            }
        }

    }

    /*********** ENREGISTREMENT DES MOUVEMENTS   ************************/

    public function createProductMovement($f3, $productId, $quantity, $direction, $date, $comment)
    {
        if (!empty($product = $this->fetchOneById('shoppingProduct', intval($productId)))) {

            $movement = intval($quantity) * intval($direction);
            $lastProductMovement = $this->fetchOneByKeysEqual('shoppingProductMovement', array('productId' => intval($productId)), 'timestamp', 'DESC');
            $newQuantity = $lastProductMovement['newQuantity'] + $movement;
            $previousQuantity = $lastProductMovement['newQuantity'];

            $productMouvementId = $this->insertByArray('shoppingProductMovement', array(
                'productId' => intval($productId),
                'date' => $date,
                'previousQuantity' => intval($previousQuantity),
                'movement' => $movement,
                'newQuantity' => intval($newQuantity),
                'comment' => htmlspecialchars($comment)),
                []);

            if (!empty($productMouvementId)) {
                return array('success', array('productMovement_array' => $this->fetchOneByKeysEqual('shoppingProductMovement', array('id' => $productMouvementId))));
            }
            return array('error', 'unexpected_error');
        }
        return array('error', 'wrong_product');
    }

    /***** HISTORIQUE DES VENTES  ****/

    public function fetchSalesByPeriod($f3, \datetime $start, \datetime $end, \dateInterval $interval)
    {
        $result = array();

        $repository = new shoppingStoreRepository();

        while ($start < $end) {
            $result[] = $repository->fetchOrderByPeriod($f3, $start->format('Y-m-d H:i:s'), $start->add($interval)->format('Y-m-d H:i:s'));
        }
        return $result;
    }


    /*** ABONNEMENTS   ****/


    public function getAbonnementArray($f3, shoppingStore $store)
    {
        $abonnement_array = $this->fetchAllCrossTableEqualKeys('abonnementPerStore', 'abonnement', 'abonnementId', 'id', array('abonnementPerStore.actived' => true));

        if (!empty($abonnement_array)) {
            foreach ($abonnement_array as $key => $abonnement) {


                $plan_array = $this->fetchAllByKeysLike('abonnementPlan', array('abonnementId' => $abonnement['abonnementId']));

                if (!empty($plan_array)) {
                    foreach ($plan_array as $id => $plan) {
                        $billingScheme_array = $this->fetchAllByKeysEqual('abonnementBillingScheme', array('planId' => intval($plan['id'])));
                        $plan_array[$id]['billingScheme_array'] = $billingScheme_array;
                    }
                    $abonnement['plan_array'] = $plan_array;

                    $reference_array = $this->fetchAllCrossTableEqualKeys('shoppingReferencePerItem', 'abonnementReferencePerAbonnement', 'id', 'referenceId', array('abonnementId' => intval($abonnement['id']), 'abonnementReferencePerAbonnement.actived' => true));

                    foreach ($reference_array as $entry => $reference) {
                        $reference_array[$entry] = $this->fetchReference($f3, intval($reference['referenceId']));
                    }

                    $abonnement['reference_array'] = $reference_array;
                    $abonnement['store_array'] = $this->fetchAllCrossTableEqualKeys('abonnementPerStore', 'abonnement', 'storeId', 'id', array('actived' => 'true', 'abonnementId' => intval($abonnement['id'])));
                }
                $abonnement_array[$key] = $abonnement;
            }
        }
        return $abonnement_array;
    }

    public function fetchAbonnement($f3, $abonnementId)
    {
        $abonnement = $this->fetchOneById('abonnement', intval($abonnementId));

        if (!empty($abonnement)) {
            $plan_array = $this->fetchAllByKeysLike('abonnementPlan', array('abonnementId' => $abonnement['id']));
            if (!empty($plan_array)) {
                foreach ($plan_array as $id => $plan) {
                    $billingScheme_array = $this->fetchAllByKeysEqual('abonnementBillingScheme', array('planId' => intval($plan['id'])));
                    $plan['billingScheme_array'] = $billingScheme_array;
                    $plan['subscription_array'] = $this->fetchAllByKeysLike('abonnementSubscription', array('planId' => intval($plan['id'])));
                    $plan_array[$id] = $plan;
                }
            }
            $abonnement['plan_array'] = $plan_array;
        }
        $abonnement['reference_array'] = $this->fetchAllCrossTableEqualKeys('shoppingReferencePerItem', 'abonnementReferencePerAbonnement', 'id', 'referenceId', array('abonnementId' => intval($abonnementId), 'abonnementReferencePerAbonnement.actived' => true));
        $abonnement['store_array'] = $this->fetchAllCrossTableEqualKeys('shoppingStore', 'abonnementPerStore', 'id', 'storeId', array('abonnementId' => intval($abonnementId), 'actived' => true));

        return $abonnement;
    }

    public function fetchActiveAbonnement($f3, $abonnementId)
    {
        $abonnement = $this->fetchOneById('abonnement', intval($abonnementId));

        if (!empty($abonnement)) {
            $plan_array = $this->fetchAllByKeysLike('abonnementPlan', array('abonnementId' => $abonnement['id'], 'active' => true));
            if (!empty($plan_array)) {
                foreach ($plan_array as $id => $plan) {
                    $billingScheme_array = $this->fetchAllByKeysEqual('abonnementBillingScheme', array('planId' => intval($plan['id'])));
                    $plan['billingScheme_array'] = $billingScheme_array;
                    $plan['subscription_array'] = $this->fetchAllByKeysLike('abonnementSubscription', array('planId' => intval($plan['id'])));
                    $plan_array[$id] = $plan;
                }
            }
            $abonnement['plan_array'] = $plan_array;
        }
        $abonnement['reference_array'] = $this->fetchAllCrossTableEqualKeys('shoppingReferencePerItem', 'abonnementReferencePerAbonnement', 'id', 'referenceId', array('abonnementId' => intval($abonnementId), 'abonnementReferencePerAbonnement.actived' => true));
        $abonnement['store_array'] = $this->fetchAllCrossTableEqualKeys('shoppingStore', 'abonnementPerStore', 'id', 'storeId', array('abonnementId' => intval($abonnementId), 'actived' => true));

        return $abonnement;
    }

    public function createAbonnement($f3, $abonnement_array)
    {
        $abonnement = new abonnement($abonnement_array);
        $stripeManager = new stripeManager();

        $result = $stripeManager->createAbonnement($f3, $abonnement);

        if (empty($result)) {
            $abonnementId = $this->insertByArray('abonnement', $abonnement->getClassArray(), ['id', 'active', 'timestamp']);
            $abonnement->setId($abonnementId);

            return (array('success', array('abonnement_array' => $abonnement->getClassArray())));
        } else {
            return $result;
        }

    }

    public function updateAbonnement($f3, $abonnementId, $data)
    {
        $abonnement = new abonnement($this->fetchOneById('abonnement', intval($abonnementId)));
        $abonnement->hydrate($data);

        $this->updateByArrayById('abonnement', $abonnement->getClassArray(), intval($abonnement->getId()));
        return (array('success', array('abonnement_array' => $abonnement->getClassArray())));
    }

    public function createAbonnementPlan($f3, $plan_array, $billingScheme_array)
    {
        $abonnementPlan = new abonnementPlan($plan_array);
        $stripeManager = new stripeManager();
        $billingSchemeList = [];

        foreach ($billingScheme_array as $key => $scheme) {
            $billingSchemeList[] = new  abonnementBillingScheme($scheme);
        }

        $error = $stripeManager->createAbonnementPlan($f3, $abonnementPlan, $billingSchemeList);
        $abonnementPlan->getClassArray();

        $planId = $this->insertByArray('abonnementPlan', $abonnementPlan->getClassArray(), ['id', 'active', 'timestamp']);;
        foreach ($billingSchemeList as $key => $scheme) {
            $scheme->setPlanId($planId);

            $this->insertByArray('abonnementBillingScheme', $scheme->getClassArray(), ['id', 'timestamp']);
        }

        return $error;
    }

    public function insertReferencePerAbonnement($f3, $data_array)
    {
        $referencePerAbonnement = new abonnementReferencePerAbonnement($data_array);

        $id = $this->insertByArray('abonnementReferencePerAbonnement', $referencePerAbonnement->getClassArray(), ['timestamp']);

        if ($id > 0) {
            $referencePerAbonnement->setId($id);
            return array('success', array('abonnementReferencePerAbonnement' => $referencePerAbonnement->getClassArray(), 'reference_array' => $this->fetchOneById('shoppingReferencePerItem', intval($referencePerAbonnement->getReferenceId()))));
        } else
            return array('error', 'unexpected_error');
    }

    public function updateReferencePerAbonnement($f3, $referencePerAbonnementId, $data_array)
    {
        $referencePerAbonnement = new abonnementReferencePerAbonnement($this->fetchOneById('abonnementReferencePerAbonnement', intval($referencePerAbonnementId)));

        $referencePerAbonnement->hydrate($data_array);
        $this->updateByArrayById('abonnementReferencePerAbonnement', $referencePerAbonnement->getClassArray(), intval($referencePerAbonnement->getId()));

        return (array('success', array('abonnementReferencePerAbonnement' => $referencePerAbonnement->getClassArray(), 'reference_array' => $this->fetchOneById('shoppingReferencePerItem', intval($referencePerAbonnement->getReferenceId())))));
    }

    public function createSubscription($f3, $storeId, $planId, $userId, $adressId)
    {
        $subscriptionId = $this->insertByArray('abonnementSubscription', array(
                'storeId' => $storeId,
                'userId' => intval($userId),
                'planId' => intval($planId),
                'userAdressId' => intval($adressId),
                'blocked' => false,
                'status' => 'pending'
            )
            , []);
        $this->updateByArrayById('abonnementSubscription', array('name' => date_format(new \datetime('now', new \DateTimeZone('Europe/Paris')), "Y-m-d") . $subscriptionId), intval($subscriptionId));

        return $subscriptionId;
    }

    public function subscriptionPayment($f3, $subscriptionId, $meanPayment, $backDate = null)
    {
        $stripeManager = new stripeManager();
        $returnData = null;

        $subscription = $stripeManager->subscribeCustomer($f3, $subscriptionId, $meanPayment, $backDate);
        if ($subscription['status'] === 'active') {
            return array('success', array('subscription_array' => $subscription));
        } else if ($subscription['status'] === 'incomplete') {
            return array('incomplete', $stripeManager->completeSubscription($f3, $subscription));
        } else
            return array('error', 'subscription_failed');
    }

    public function deleteSubscription($f3, $subscriptionId,$id){

        //cette méthode supprime la souscription, l'annule chez stripe et détruit les paiements
        //en base.

        $stripeManager = new stripeManager();

        $subscription = $this->fetchOneByKeysLike('abonnementSubscription',array('subscriptionId'=>$subscriptionId,'id'=>$id));


        if (empty($subscription))
            return array('error', 'subscription_not_exist');
         $stripeManager->unsubscribeCustomer($f3, $subscription['id']);
         $payment_array = $this->fetchAllByKeysLike('payment', array('paymentOriginType'=> 'ABON', 'paymentOriginId'=> intval($subscription['id'])));
         foreach($payment_array as $key => $value){
             if ($value['id'] > 0) {
                 $this->deleteByKeysEqual('paymentStripePaymentIntent', array('paymentId' => intval($value['id'])));
             }
             $this->deleteById('payment', intval($value['id']));
         }
        $this->deleteByKeysEqual('abonnementUsage',  array('subscriptionId' => intval($subscription['planId'])));
    }

    public function fetchPlan($f3, $planId)
    {
        $plan = $this->fetchOneById('abonnementPlan', intval($planId));

        if (!empty($plan)) {
            $plan['price_array'] = $this->fetchAllByKeysEqual('abonnementBillingScheme', array('planId' => intval($plan['id'])));
        }
        return $plan;
    }

    public function fetchSubscriberListPerAbonnement($f3, $abonnementId)
    {
        $shoppingStoreRepository = new shoppingStoreRepository();
        $abonnement_array = $this->fetchOneById('abonnement', intval($abonnementId));

        $subscription_array = $shoppingStoreRepository->fetchSubscriberPerABonnement($f3, $abonnementId);
        foreach ($subscription_array as $key => $subscription) {
            $subscription_array[$key]['user_array'] = $this->fetchOneById('user', intval($subscription['userId']));
            $subscription_array[$key]['plan_array'] = $this->fetchOneById('abonnementPlan', intval($subscription['planId']));
        }
        return $subscription_array;
    }


    private function getAbonnementCatalog($f3)
    {
        $abonnement_array = $this->fetchAllByKeysEqual('abonnement', array('active' => true));

        if (!empty($abonnement_array)) {
            foreach ($abonnement_array as $key => $abonnement) {
                $plan_array = $this->fetchAllByKeysLike('abonnementPlan', array('abonnementId' => $abonnement['abonnementId']));

                if (!empty($plan_array)) {
                    foreach ($plan_array as $id => $plan) {
                        $billingScheme_array = $this->fetchAllByKeysEqual('abonnementBillingScheme', array('planId' => intval($plan['id'])));
                        $plan_array[$id]['billingScheme_array'] = $billingScheme_array;
                    }
                    $abonnement['plan_array'] = $plan_array;
                }
                $abonnement_array[$key] = $abonnement;
            }
        }
        return $abonnement_array;
    }


    public function getOrderDetails ($f3,$orderId) {
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, "fr_FR");

        $order = $this->fetchOneById('shoppingOrder', intval($orderId));

        $order['lines'] = $this->fetchAllByKeysEqual('shoppingOrderLine', array('orderId' => intval($order['id'])));

        foreach ($order['lines'] as $key => $value) {
            $order['lines'][$key]['item'] = $this->fetchOneById('shoppingItem', intval($value['itemId']));
            $order['lines'][$key]['reference'] = $this->fetchOneById('shoppingReferencePerItem', intval($value['referenceId']));
            $price = $this->fetchOneById('shoppingPricePerReference', intval($value['priceId']));
            $order['lines'][$key]['referenceprice'] = array(
                'price' => number_format($price['price']/100, 2, ',', ' '),
                'htdiscount' => number_format($price['HTDiscount']/100, 2, ',', ' '),
                'discountPrice' => number_format($price['discountPrice']/100, 2, ',', ' ')
            );
            $order['lines'][$key]['image'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerItem', 'id', 'imageId', array('sort' => 0, 'itemId' => $value['itemId']));
            $order['subtotal'] +=  ($price['discountPrice'] * $value['quantity']);
            $order['total']+= ($price['discountPrice'] * $value['quantity']);
            $order['vat']+= (($price['discountPrice'] * $value['quantity']) - ($price['HTDiscount'] * $value['quantity']));
        }
        $order['total'] += $order['shippingPrice'];
        $order['subtotal'] = number_format(($order['subtotal'] - $order['vat'])/100, 2, ',', ' ');
        $order['total'] = number_format($order['total']/100, 2, ',', ' ');
        $order['vat'] = number_format($order['vat']/100, 2, ',', ' ');
        $order['shippingprice'] = number_format($order['shippingPrice']/100, 2, ',', ' ');
        $order['shippingdate'] = strftime("%A %e %B %G", strtotime($order['shippingDate']));
        $order['commercialid'] = 'LAC#'.date('ymd');;
        for ($i = 1; $i <= 6 - strlen($orderId); $i++) {
            $order['commercialid'].='0';
        }
        $order['commercialid'] .= $orderId;

        return $order;
    }

    public function sendOrderConfirmation($f3, $orderId)
    {
        $mandrillManager = new mandrillManager();

        $order= $this->getOrderDetails($f3,$orderId);

        $user = $this->fetchOneById('user', intval($order['userId']));
        $adress = $this->fetchOneById('userAdress', intval(intval($order['userAdressId'])));

        $itemPrescription = $this->fetchAllCrossTableEqualKeys('shoppingItem', 'shoppingItemPerStore', 'id', 'itemId', array('storeId' => intval(1)), 'itemId', 'DESC');
        $subscriptionPrescription = $this->fetchAllCrossTableEqualKeys('shoppingReferencePerItem', 'abonnementReferencePerAbonnement', 'id', 'referenceId', array('abonnementId' => intval(4)), 'id', 'DESC');

        foreach ($itemPrescription as $key => $value) {
            $itemPrescription[$key]['itemimage'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerItem', 'id', 'imageId', array('sort' => 0, 'itemId' => $value['itemId']));
            $itemPrescription[$key]['referenceimage'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerReference', 'id', 'imageId', array('sort' => 0, 'referenceId' => $value['referenceId']));
        }
        foreach ($subscriptionPrescription as $key => $value) {
            $subscriptionPrescription[$key]['itemimage'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerItem', 'id', 'imageId', array('sort' => 0, 'itemId' => $value['itemId']));
            $subscriptionPrescription[$key]['referenceimage'] = $this->fetchAllCrossTableEqualKeys('image', 'imagePerReference', 'id', 'imageId', array('sort' => 0, 'referenceId' => $value['referenceId']));

        }


        $template = array(
            'template_name' => 'les-analyses-cashOrderConfirm',
            'global_merge_vars' => array(
                array(
                    'name' => 'user',
                    'content' => $user
                ),
                array('name' => 'order',
                    'content' => $order
                ),
                array(
                    'name' => 'adress',
                    'content' => $adress
                )
            )
        );

        $mandrillManager->sendMailTemplate($f3, 'confirmation d\'achat', $user['id'], $template);
    }

    public function sendSubscriptionConfirmation($f3, $id)
    {

        $mandrillManager = new mandrillManager();
        $shoppingStoreRepository = new shoppingStoreRepository();
        $subscription = $this->fetchOneById('abonnementSubscription', intval($id));
        $plan = $this->fetchOneById('abonnementPlan', intval($subscription['planId']));
        $user = $this->fetchOneById('user', intval($subscription['userId']));
        $adress = $this->fetchOneById('userAdress', intval(intval($subscription['userAdressId'])));
        $billingscheme = $this->fetchOneByKeysEqual('abonnementBillingScheme', array('planId' => intval($plan['id'])));
        $reference_array = $shoppingStoreRepository->fetchAbonnementReferencePerDate($f3, $plan['abonnementId']);

        if (!empty($reference_array)) {
            $reference = $this->fetchReference($f3, $reference_array['id']);
        }

        if (substr($plan['regularInterval'], -1) === 'D') {
            $interval = 'jours';
        } else if (substr($plan['regularInterval'], -1) === 'M') {
            $interval = 'mois';
        }else if (substr($plan['regularInterval'], -1) === 'Y') {
            $interval = 'an';
        }

        $plan['interval'] = $interval;

        $template = array(
            'template_name' => $f3->get('project') . 'SubscriptionConfirm',
            'global_merge_vars' => array(
               array(
                    'name' => 'user',
                    'content' => $user
                ),
                array(
                    'name' => 'plan',
                    'content' => $plan
                ),
                array(
                    'name' => 'price',
                    'content' => number_format($billingscheme['price']/100, 2, ',', ' ')
                ),
                array(
                    'name' => 'reference',
                    'content' => $reference
                ),
                array(
                    'name' => 'adress',
                    'content' => $adress
                )
            )
        );

        $mandrillManager->sendMailTemplate($f3, 'confirmation d\'abonnement', $user['id'], $template);
    }
}
