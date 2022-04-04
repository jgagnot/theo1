<?php
/**
 * Created by PhpStorm.
 * User: nicolasdelourme
 * Date: 24/05/2017
 * Time: 23:11
 */

namespace service\payment;

use core\imi;
use entity\abonnement;
use entity\abonnementBillingScheme;
use entity\abonnementPlan;
use entity\paymentMeanPaymentPerUser;
use entity\user;
use entity\paymentStripeSource;
use entity\paymentStripeCharge;
use Respect\Validation\Rules\Date;
use service\shopping\shoppingManager;

class stripeManager extends imi
{

    private $_privateKeyStripe = 'sk_test_QDoOo3633bwQOIskvETnyw2x';
    private $_privateWebHookKey = 'whsec_Spwzf60eZwxhaNPSrXMmC2SNBXYCjZLH';

    public function createCustomer($token, $saveMeanPayment = true)
    {

        //cette méthode créée un customer et retourne la reponse Stripe

        if (($this->getF3("SESSION.userId") == -1) || ($this->getF3("SESSION.userId") == '')) {
            return -1;
        }

        $user = new user($this->fetchOneById('stripeManager.phpuser', $this->getF3("SESSION.userId")));

        //on créé la source chez Stripe
        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);
        $source = \Stripe\Source::create(array(
            "type" => "card",
            "currency" => "eur",
            "owner" => array(
                "email" => $user->getEmail(),
            ),
            'token' => $token
        ));

        //on créé le customer chez Stripe
        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);
        $customer = \Stripe\Customer::create(array(
            'description' => $user->getId(),
            'email' => $user->getEmail(),
            'source' => $source['id']
        ));

        //on insère la source en base
        $paymentStripeSourceId = $this->insertByArray('paymentStripeSource', array(
            'stripeId' => $customer['sources']['data'][0]['id'],
            'stripeCustomerId' => $customer['id'],
            'currency' => $customer['sources']['data'][0]['currency'],
            'created' => $customer['sources']['data'][0]['created']
        ), []);

        $paymentMeanPaymentPerUserId = $this->insertByArray('paymentMeanPaymentPerUser', array(
            'userId' => $this->getF3("SESSION.userId"),
            'meanPaymentType' => 'StripeSource',
            'meanPaymentId' => $paymentStripeSourceId,
            'actived' => boolval($saveMeanPayment),
            'first' => 1
        ), []);

        return $paymentMeanPaymentPerUserId;

    }

    public function createSource($token, $customerId, $saveMeanPayment = true)
    {

        //cette méthode créée une nouvelle source pour le customer passé en argument et la soureId de Stripe

        if (($this->getF3("SESSION.userId") == -1) || ($this->getF3("SESSION.userId") == '')) {
            return -1;
        }

        $user = new user($this->fetchOneById('user', $this->getF3("SESSION.userId")));

        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);
        $source = \Stripe\PaymentIntent::create(array(
            "type" => "card",
            "currency" => "eur",
            "owner" => array(
                "email" => $user->getEmail(),
            ),
            'token' => $token
        ));

        //on attache la source au customer

        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);
        $customer = \Stripe\Customer::retrieve($customerId);
        $customer->sources->create(array("source" => $source['id']));

        //on passe la source du customer en "first"=false
        $firstPaymentMeanPaymentPerUserId = $this->fetchOneByKeysEqual('paymentMeanPaymentPerUser', array('userId' => $this->getF3("SESSION.userId"), 'first' => true))['id'];
        if (isset($firstPaymentMeanPaymentPerUserId)) $this->updateByArrayById('paymentMeanPaymentPerUser', array('first' => false), $firstPaymentMeanPaymentPerUserId);

        //on insère la source en base
        $paymentStripeSourceId = $this->insertByArray('paymentStripeSource', array(
            'stripeId' => $source['id'],
            'stripeCustomerId' => $customerId,
            'currency' => $source['currency'],
            'created' => $source['created']
        ), []);

        $paymentMeanPaymentPerUserId = $this->insertByArray('paymentMeanPaymentPerUser', array(
            'userId' => $this->getF3("SESSION.userId"),
            'meanPaymentType' => 'StripeSource',
            'meanPaymentId' => $paymentStripeSourceId,
            'actived' => boolval($saveMeanPayment),
            'first' => 1
        ), []);

        return $paymentMeanPaymentPerUserId;
    }


    public function paymentTimetable($f3, $paymentTimetable_array, $paymentMeanPaymentPerUser)
    {

        $paymentSuccess = false;

        $paymentNow = false;
        if (count($paymentTimetable_array) !== 1) {
            return $this->createbillingPlanFromTimetable($f3, $paymentTimetable_array, $paymentMeanPaymentPerUser);
        } else {
            foreach ($paymentTimetable_array as $key => $value) {

                $currency = NULL;
                $paymentStripeChargeId = NULL;

                if ($value['now'] == 1) {

                    $paymentNow = true;
                    $paymentStripeCharge = $this->chargeSource($paymentMeanPaymentPerUser->getMeanPaymentId(), $value['amount'], $this->getF3("SESSION.orderId"));

                    $currency = $paymentStripeCharge->getCurrency();
                    $paymentStripeChargeId = $paymentStripeCharge->getId();
                    if ($paymentStripeCharge->getPaid()) {
                        //si le paiement now est réussit, on retourne un succès
                        $status = 'PAID';
                        $paymentSuccess = true;
                    } else {
                        $status = 'FAIL';
                    }
                } else {
                    //s'il y a eu premier paiement qui a échoué alors on CANCelled les paiements futurs (sinon on les PENDing)
                    if ($paymentNow) {
                        if ($paymentSuccess) {
                            $status = 'PEND';
                        } else {
                            $status = 'CANC';
                        }
                    } else {
                        //si tous les paiements sont PENDing, ont retourne un success par défault
                        $status = 'PEND';
                        $paymentSuccess = true;
                    }
                }

                //on insère le payment en base de données
                $id = $this->insertByArray('payment', array(
                    'userId' => $this->getF3("SESSION.userId"),
                    'paymentOriginType' => 'SHOP',
                    'paymentOriginId' => $this->getF3("SESSION.orderId"),
                    'amount' => $value['amount'],
                    'currency' => strtoupper($currency),
                    'execution' => $value['date']->format('Y-m-d H:i:s'),
                    'status' => $status,
                    'meanChargeType' => 'StripeCharge',
                    'meanChargeId' => $paymentStripeChargeId
                ), []);

            }
        }
        if ($paymentSuccess === true)
            return array('success', array('payment_array' => $this->fetchOneById('payment', intval($id))));
        else
            return array('error', 'unexpected_error');
    }

    public function abonnementTimetable($f3, $paymentTimetable_array, $paymentMeanPaymentPerUser)
    {
        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);
        $paymentSuccess = false;
        $trialName = '';
        $regularName = '';

        $stripeSource = $this->fetchOneById('paymentStripeSource', $paymentMeanPaymentPerUser->getMeanPaymentId());

        //on définit les intervalles entre deux paiements…

        //… du TRIAL


        if ($paymentTimetable_array[0] !== null) {
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'D') $trialInterval = 'day';
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'M') $trialInterval = 'month';
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'Y') $trialInterval = 'year';
            $trialFrequencyInterval = substr($paymentTimetable_array[0]['interval'], 1, (strlen($paymentTimetable_array[0]['interval']) - 2));
            $trialName = 'T' . $paymentTimetable_array[0]['cycle'] . '_' . substr($trialInterval, 0, 1) . $trialFrequencyInterval . '_' . $paymentTimetable_array[0]['amount'];
        }
        //… du REGULAR

        if (substr($paymentTimetable_array[1]['interval'], (strlen($paymentTimetable_array[1]['interval']) - 1), 1) == 'D') $regularInterval = 'day';
        if (substr($paymentTimetable_array[1]['interval'], (strlen($paymentTimetable_array[1]['interval']) - 1), 1) == 'M') $regularInterval = 'month';
        if (substr($paymentTimetable_array[1]['interval'], (strlen($paymentTimetable_array[1]['interval']) - 1), 1) == 'Y') $regularInterval = 'year';
        $regularFrequencyInterval = substr($paymentTimetable_array[1]['interval'], 1, (strlen($paymentTimetable_array[1]['interval']) - 2));
        $regularName = 'R0' . '_' . substr($regularInterval, 0, 1) . $regularFrequencyInterval . '_' . $paymentTimetable_array[1]['amount'];

        if ($paymentTimetable_array[0] !== null && $paymentTimetable_array[0]['amount'] !== 0)
            $planName = $trialName . '-' . $regularName;
        else
            $planName = $regularName;

        if ($trialName !== null) {
            if ($paymentTimetable_array[0]['amount'] !== 0) {
                $trial = $this->fetchOneByKeysEqual('paymentStripeSubscription', array('name' => $trialName));
                if ($trial === null)
                    $this->createPlan($f3, $trialName, $paymentTimetable_array[0]['amount'], $trialInterval, $trialFrequencyInterval);
                $this->createSubscription($f3, $stripeSource['stripeCustomerId'], $trialName, array_slice($paymentTimetable_array, 0, 1), 'ABON');
            }
            $trialEnd = $paymentTimetable_array[0]['date']->format('Y-m-d H:i:s');
        }

        $plan = $this->fetchOneByKeysEqual('paymentStripeSubscription', array('name' => $planName));

        if ($plan === null)
            $this->createPlan($f3, $planName, $paymentTimetable_array[1]['amount'], $regularInterval, $regularFrequencyInterval);
        $this->createSubscription($f3, $stripeSource['stripeCustomerId'], $planName, $paymentTimetable_array, 'ABON', $trialEnd);

        return $paymentSuccess;
    }

    private function chargeSource($id, $amount, $orderId)
    {

        $paymentStripeSource = new paymentStripeSource($this->fetchOneById('paymentStripeSource', intval($id)));

        \Stripe\Stripe::setApiKey("$this->_privateKeyStripe");
        $charge = \Stripe\Charge::create(array(
            "amount" => $amount,
            "currency" => $paymentStripeSource->getCurrency(),
            "source" => $paymentStripeSource->getStripeId(), // obtained with Stripe.js
            "customer" => $paymentStripeSource->getStripeCustomerId(),
            "description" => $orderId
        ));

        $id = $this->insertByArray('paymentStripeCharge', array(
            'stripeChargeId' => $charge['id'],
            'amount' => $charge['amount'],
            'currency' => $charge['currency'],
            'paid' => $charge['paid'],
            'failureCode' => $charge['failureCode'],
            'created' => $charge['created']
        ), []);

        $paymentStripeCharge = new paymentStripeCharge($this->fetchOneById('paymentStripeCharge', intval($id)));

        return $paymentStripeCharge;
    }


    public function expressCheckout($token)
    {

        //cette méthode créée une charge sur le customer passé en paramètre et retourne la reponse Stripe de la charge

        if (($this->getF3("SESSION.userId") == -1) || ($this->getF3("SESSION.userId") == '')) {
            return false;
        }

        \Stripe\Stripe::setApiKey("$this->_privateKeyStripe");

        echo $token;

        // Charge the user's card:
        $charge = \Stripe\Charge::create(array(
            "amount" => 1000,
            "currency" => "eur",
            "description" => "Example charge",
            "source" => $token,
        ));
    }

    public function updateCustomer($token, $id, $key, $value)
    {

        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);

        $customer = \Stripe\Customer::retrieve($id);
        $customer->currency = $value;
        $customer->source = $token; // obtained with Stripe.js
        $customer->save();
    }

    public function retrieveSource($f3, $id)
    {
        //cette méthode retourne les source du customer chez Stripe
        //seule les sources dont le status=="chargeable" peuvent être utilisées
        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);
        $source = \Stripe\Source::retrieve($id);

        return $source;
    }

    /***************************************************/
    /*                                                 */
    /*           subscription API                      */
    /*                                                 */
    /***************************************************/

    public function createbillingPlanFromTimetable($f3, $paymentTimetable_array, paymentMeanPaymentPerUser $paymentMeanPaymentPerUser)
    {
        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);

        $stripeSource = $this->fetchOneById('paymentStripeSource', $paymentMeanPaymentPerUser->getMeanPaymentId());

        if ($paymentTimetable_array[0]['amount'] == $paymentTimetable_array[1]['amount']) {
            $isEqualAmount = true;
        } else {
            $isEqualAmount = false;
        }
        //on regarde si l'intervalle entre deux paiements est toujours le même
        if ($paymentTimetable_array[0]['interval'] == $paymentTimetable_array[1]['interval']) {
            $isEqualFrequecyInterval = true;
        } else {
            $isEqualFrequecyInterval = false;
        }
        //on définit les intervalles entre deux paiements…

        //… du TRIAL
        if (substr($paymentTimetable_array[0]['interval'], 0, 3) == 'FIX') {
            $trialInterval = 'month';
            $trialFrequencyInterval = 1;
        } else {
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'D') $trialInterval = 'day';
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'M') $trialInterval = 'month';
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'Y') $trialInterval = 'year';
            $trialFrequencyInterval = substr($paymentTimetable_array[0]['interval'], 1, (strlen($paymentTimetable_array[0]['interval']) - 2));
        }
        //… du REGULAR
        if (substr($paymentTimetable_array[1]['interval'], 0, 3) == 'FIX') {
            $regularInterval = 'month';
            $regularFrequencyInterval = 1;
        } else {
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'D') $regularInterval = 'day';
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'M') $regularInterval = 'month';
            if (substr($paymentTimetable_array[0]['interval'], (strlen($paymentTimetable_array[0]['interval']) - 1), 1) == 'Y') $regularInterval = 'year';
            $regularFrequencyInterval = substr($paymentTimetable_array[0]['interval'], 1, (strlen($paymentTimetable_array[0]['interval']) - 2));
        }

        if ($isEqualAmount && $isEqualFrequecyInterval) {
            $planName = 'R' . count($paymentTimetable_array) . '_' . substr($regularInterval, 0, 1) . $regularFrequencyInterval . '_' . $paymentTimetable_array[0]['amount'];
            $trialName = null;
            $trialTimestamp = null;
        } else {
            $planName = 'T1' . '_' . substr($trialInterval, 0, 1) . $trialFrequencyInterval . '_' . $paymentTimetable_array[0]['amount'] . '-R' . (count($paymentTimetable_array) - 1) . '_' . substr($regularInterval, 0, 1) . $regularFrequencyInterval . '_' . $paymentTimetable_array[1]['amount'];
            $trialName = 'T1' . '_' . substr($trialInterval, 0, 1) . $trialFrequencyInterval . '_' . $paymentTimetable_array[0]['amount'];
        }

        if ($trialName !== null) {
            $trial = $this->fetchOneByKeysEqual('paymentStripeSubscription', array('name' => $trialName));
            if ($trial === null)
                $this->createPlan($f3, $trialName, $paymentTimetable_array[0]['amount'], $trialInterval, $trialFrequencyInterval);
            $this->createSubscription($f3, $stripeSource['stripeCustomerId'], $trialName, array_slice($paymentTimetable_array, 0, 1), 'SHOP');
            $trialEnd = $paymentTimetable_array[1]['date']->format('Y-m-d H:i:s');
            $paymentTimetable_array = array_slice($paymentTimetable_array, 1);
        }

        $plan = $this->fetchOneByKeysEqual('paymentStripeSubscription', array('name' => $planName));
        if ($plan === null)
            $this->createPlan($f3, $planName, $paymentTimetable_array[1]['amount'], $regularInterval, $regularFrequencyInterval);
        $this->createSubscription($f3, $stripeSource['stripeCustomerId'], $planName, $paymentTimetable_array, 'SHOP', $trialEnd);

        return true;
    }

    private function createPlan($f3, $planName, $amount, $interval, $intervalCount)
    {
        \Stripe\Stripe::setApiKey($this->_privateKeyStripe);

        try {
            \Stripe\Plan::create(array(
                "amount" => $amount,
                "interval" => $interval,
                "interval_count" => $intervalCount,
                "product" => array(
                    "name" => $planName
                ),
                "currency" => "eur",
                "id" => $planName
            ));

        } catch (\Stripe\Exception\ApiErrorException $e) {
            //   echo ($e->getMessage());
            return false;
        }
        $this->insertByArray('paymentStripeSubscription', array('name' => $planName), []);
        return true;
    }

    private function createSubscription($f3, $stripeCustomerId, $planName, $paymentTimeTable_array, $source, $trial_end = null)
    {

        try {
            $subscription = \Stripe\Subscription::create(array(
                "customer" => $stripeCustomerId,
                "items" => array(
                    array(
                        "plan" => $planName
                    )
                ),
                //'trial_end'=>strtotime($trial_end)
            ));

            if ($trial_end)
                $subscription->offsetSet('trial_end', strtotime($trial_end));
            $paymentStripeSubscriptionId = $this->insertByArray('paymentStripeSubscription', array('name' => $planName, 'stripeSubscriptionId' => $subscription['id']), []);

            foreach ($paymentTimeTable_array as $key => $value) {
                $this->insertByArray('payment', array(
                    'userId' => $this->getF3("SESSION.userId"),
                    'paymentOriginType' => $source,
                    'paymentOriginId' => $this->getF3("SESSION.orderId"),
                    'amount' => $value['amount'],
                    'currency' => 'EUR',
                    'execution' => $value['date']->format('Y-m-d H:i:s'),
                    'status' => 'PEND',
                    'meanChargeType' => 'StripeSubscription',
                    'meanChargeId' => $paymentStripeSubscriptionId
                ), []);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo($e->getMessage());
            return false;
        }
        return true;
    }

    /***************************************************/
    /*                                                 */
    /*           webhooks handlers                     */
    /*                                                 */
    /***************************************************/

    private function handlePaymentIntentSucceeded($f3, $paymentIntent)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        $this->sessionClear('orderId');

        $intent = $this->fetchOneByKeysEqual('paymentStripePaymentIntent', array('intentId' => $paymentIntent->id));
        $payment = $this->fetchOneById('payment', intval($intent['paymentId']));

        if (!empty($intent)) {
            $this->updateByArrayById('paymentStripePaymentIntent', array('status' => $paymentIntent->status, 'amountReceived'=> $paymentIntent->amount_received), intval($intent['id']));
        }
        $paymentMethod = $this->fetchOneByKeysEqual('paymentStripePaymentMethod', array('paymentMethodId' => $paymentIntent->payment_method));
        $customer = $this->fetchOneByKeysEqual('paymentStripeCustomer', array('customerId' => $paymentIntent->customer));

        if (empty($customer)) {
            try {
                $customer = \Stripe\Customer::create([
                    'payment_method' => $paymentIntent->payment_method

                ]);
                $customerId = $this->insertByArray('paymentStripeCustomer', array('userId' => $intent['userId'], 'customerId' => $customer['id']), []);
                $meanPaymentId = $this->insertByArray('paymentStripePaymentMethod', array('customerId' => $customerId, 'paymentMethodId' => $paymentIntent->payment_method, 'projectId'=>intval($f3->get('projectId')), 'offSessionUsage' => true), []);
                $this->insertByArray('paymentMeanPaymentPerUser', array('userId' => $intent['userId'], 'meanPaymentType' => 'stripe', 'meanPaymentId' => intval($meanPaymentId), 'actived' => true), []);
                $this->updateByArrayById('paymentStripePaymentIntent', array('paymentId' => $payment['id']), intval($intent['id']));
                //return array('success', array('paymentIntent' => $this->fetchOneById('paymentStripePaymentIntent', intval($intent['id']))) );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return array('error', $e->getMessage());
            }

        } else {
            if (empty($paymentMethod)) {
                try {
                    $payment_method = \Stripe\PaymentMethod::retrieve($paymentIntent->payment_method);
                    $payment_method->attach(['customer' => $customer['customerId']]);

                    $meanPaymentId = $this->insertByArray('paymentStripePaymentMethod', array('customerId' => $customer['id'], 'paymentMethodId' => $paymentIntent->payment_method, 'projectId'=>intval($f3->get('projectId')) , 'offSessionUsage' => true), []);
                    $this->insertByArray('paymentMeanPaymentPerUser', array('userId' => $intent['userId'], 'meanPaymentType' => 'stripe', 'meanPaymentId' => intval($meanPaymentId), 'actived' => true), []);
                    $this->updateByArrayById('paymentStripePaymentIntent', array('paymentId' => $payment['id'], 'amountReceived'=> $paymentIntent->amount_received ), intval($intent['id']));
                    // return array('success', array('paymentIntent' => $this->fetchOneById('paymentStripePaymentIntent', intval($intent['id']))) );
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return array('error', $e->getMessage());
                }
            } else {
                $meanPaymentId = $paymentMethod['id'];
                $this->updateByArrayById('paymentStripePaymentIntent', array('paymentId' => $payment['id'],  'amountReceived'=> $paymentIntent->amount_received), intval($intent['id']));
            }
        }


        if ($payment['paymentOriginType'] == 'SHOP'){
            $bill = $this->fetchOneByKeysEqual('bill', array('sourceType' => 'order', 'sourceId' => $payment['paymentOriginId']));
            if (empty($bill)){
                $billId = $this->insertByArray('bill', array('sourceType' => 'order', 'sourceId' => $payment['paymentOriginId']), []);
                $bill = $this->fetchOneById('bill', intval($billId));
            }
            $this->updateByArrayById('payment', array('billId' => intval($bill['id'])), intval($payment['id']));
        }


        $this->updateByArrayById('payment', array('status' => 'paid', 'meanChargeId' => intval($meanPaymentId)), intval($payment['id']));
        $this->updateFuturePayments($f3, $payment['id']);

        return array('success', array('paymentIntent' => $this->fetchOneById('paymentStripePaymentIntent', intval($intent['id']))));
    }

    private function handleInvoiceSucceeded($f3, $invoice)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        $subscription = $this->fetchOneByKeysEqual('abonnementSubscription', array('subscriptionId' => $invoice->subscription));

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
            $customer = $this->fetchOneByKeysEqual('paymentStripeCustomer', array('customerId' => $paymentIntent->customer));
            $billId = $this->insertByArray('bill', array('sourceType' => 'subscription', 'sourceId' => intval($subscription['id'])), []);

            $intent = $this->fetchOneByKeysEqual('paymentStripePaymentIntent', array('intentId' => $paymentIntent->id));

            $paymentMethod = $this->fetchOneByKeysEqual('paymentStripePaymentMethod', array('paymentMethodId' => $paymentIntent->payment_method));
            if (empty($paymentMethod)){
                $paymentMethodId = $this->insertByArray('paymentStripePaymentMethod', array('customerId'=>$customer['userId'], 'projectId'=>intval($f3->get('projectId')), 'paymentMethodId' => $paymentIntent->payment_method ), []);
            }else{
                $paymentMethodId = $paymentMethod['id'];
            }

            if (!empty($intent)) {
                $this->updateByArrayById('paymentStripePaymentIntent', array('status' => $invoice->status,'amountReceived' => $invoice->amount_paid,), intval($intent['id']));
                $this->updateByArrayById('payment', array('status' => 'paid','meanChargeId' => $paymentMethodId, 'billId'=> $billId ), intval($intent['paymentId']));
            }else{
                $paymentId = $this->insertByArray('payment',
                    array(
                        'status' => 'paid',
                        'meanChargeId' => $paymentMethodId,
                        'userId' => intval($customer['userId']),
                        'paymentOriginType' => 'ABON',
                        'billId'=> $billId ,
                        'paymentOriginId' => intval($subscription['id']),
                        'amount'=> $invoice->amount_due,
                        'currency' => 'eur',
                        'meanChargeType'=> 'stripePayment',
                        ),[]);
                $intentId = $this->insertByArray('paymentStripePaymentIntent', array('status' =>$invoice->status,'intentId'=>$invoice->payment_intent , 'amount' => $invoice->amount_due,   'amountReceived' => $invoice->amount_paid, 'userId' => intval($customer['userId']), 'paymentId'=> intval($paymentId)), []);
            }
            $stripeSubscription = \Stripe\Subscription::retrieve($invoice->subscription);

            $this->updateByArrayById('abonnementSubscription', array('status' => $stripeSubscription->status), intval($subscription['id']));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    private function handleInvoiceFailed($f3, $invoice)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        $subscription = $this->fetchOneByKeysEqual('abonnementSubscription', array('subscriptionId' => $invoice->subscription));
        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
            $customer = $this->fetchOneByKeysEqual('paymentStripeCustomer', array('customerId' => $paymentIntent->customer));

            $intent = $this->fetchOneByKeysEqual('paymentStripePaymentIntent', array('intentId' => $paymentIntent->id));

            $paymentMethod = $this->fetchOneByKeysEqual('paymentStripePaymentMethod', array('paymentMethodId' => $paymentIntent->payment_method));
            if (empty($paymentMethod)){
                $paymentMethodId = $this->insertByArray('paymentStripePaymentMethod', array('customerId'=>$customer['userId'], 'projectId'=>intval($f3->get('projectId')), 'paymentMethodId' => $paymentIntent->payment_method ), []);
            }else{
                $paymentMethodId = $paymentMethod['id'];
            }

            if (!empty($intent)) {
                $this->updateByArrayById('paymentStripePaymentIntent', array('status' => $invoice->status), intval($intent['id']));
                $this->updateByArrayById('payment', array('status' => 'failed','meanChargeId' => $paymentMethodId ), intval($intent['paymentId']));
            }else{
                $paymentId = $this->insertByArray('payment',
                    array(
                        'status' => 'failed',
                        'meanChargeId' => $paymentMethodId,
                        'userId' => intval($customer['userId']),
                        'paymentOriginType' => 'ABON',
                        'paymentOriginId' => intval($subscription['id']),
                        'amount'=> $invoice->amount_due,
                        'currency' => 'eur',
                        'meanChargeType'=> 'stripePayment',
                    ),[]);
                $intentId = $this->insertByArray('paymentStripePaymentIntent', array('status' =>$invoice->status, 'amount' => $invoice->amount_due, 'userId' => intval($customer['userId']), 'paymentId'=> intval($paymentId)), []);
            }
            $stripeSubscription = \Stripe\Subscription::retrieve($invoice->subscription);

            $this->updateByArrayById('abonnementSubscription', array('status' => $stripeSubscription->status), intval($subscription['id']));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    private function handleSubscriptionUpdate($f3, $event){
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        $subscription = $this->fetchOneByKeysEqual('abonnementSubscription', array('subscriptionId' => $event->id));

        $this->updateByArrayById('abonnementSubscription', array('status' => $event->status), intval($subscription['id']));
        if ($event->status == 'canceled'){
            $today = new \DateTime();
            $this->updateByArrayById('abonnementSubscription', array('dateStop' => $today->format('Y-m-d H:i:s')), intval($subscription['id']));
        }
    }

    public function webhookHandler($f3)
    {
        $endpoint_secret = $f3->get('StripeWebhookKey');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            exit();
        }


        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentIntentSucceeded($f3, $paymentIntent);
                break;
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $this->handleInvoiceSucceeded($f3, $invoice);
                break;
            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $this->handleInvoiceFailed($f3, $invoice);
                break;
            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdate($f3, $subscription);
                break;
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdate($f3, $subscription);
                break;
            case 'customer.subscription.created':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdate($f3, $subscription);
                break;
            default:
                http_response_code(400);
                exit();
        }

        http_response_code(200);
    }

    /***************************************************/
    /*                                                 */
    /*           payment intent api                    */
    /*                                                 */
    /***************************************************/


    public function createIntent($f3, $userId, $amount, $paymentMethodId, $paymentId)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        try {
            if ($userId <= 0){
                return array('error', 'user_doesnt_exist');
            }
            $customer = $this->fetchOneByKeysEqual('paymentStripeCustomer', array('userId' => intval($userId)));

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'setup_future_usage' => 'off_session',
                'customer' => $customer['customerId'],
                'metadata' => array("paymentMethodId" => $paymentMethodId,
                    "orderId" => $f3->get("SESSION.orderId"))
            ]);
            $this->insertByArray('paymentStripePaymentIntent', array('intentId' => $intent->id, 'amount' => $amount, 'userId' => intval($userId), 'status' => $intent->status, 'paymentId' => intval($paymentId)), []);

            return $intent;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }


    public function retrieveIntent($f3, $paymentId)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        $intent_array = $this->fetchOneByKeysEqual('paymentStripePaymentIntent', array('paymentId' => intval($paymentId)));

        try {
            $intent = \Stripe\PaymentIntent::retrieve($intent_array['intentId']);

            return $intent;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    public function fetchStripeCustomer($f3, $userId)
    {
        $paymentMethod_array = [];
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        if ($userId <= 0){
            return array('error', 'user_doesnt_exist');
        }
        $customer = $this->fetchOneByKeysEqual('paymentStripeCustomer', array('userId' => intval($userId)));

        if (!empty($customer)) {
            $customer['paymentMethod_array'] = $this->fetchAllByKeysEqual('paymentStripePaymentMethod', array('customerId' => intval($customer['id']), 'projectId'=>intval($f3->get('projectId')), 'offSessionUsage' => true));
        }

        if (!(empty($customer['paymentMethod_array']))) {

            foreach ($customer['paymentMethod_array'] as $key => $paymentMethod) {

                try {
                    $paymentMethod_array[] = \Stripe\PaymentMethod::retrieve($paymentMethod['paymentMethodId']);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return array('error', $e->getMessage());
                }
            }
        }
        return $paymentMethod_array;
    }

    public function execPaymentIntent($f3, $paymentId)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        try {
            $payment = $this->fetchOneById('payment', intval($paymentId));
            $paymentMethod = $this->fetchOneById('paymentStripePaymentMethod', intval($payment['meanChargeId']));
            $customer = $this->fetchOneById('paymentStripeCustomer', intval($paymentMethod['customerId']));

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $payment['amount'],
                'currency' => 'eur',
                'customer' => $customer['customerId'],
                'payment_method' => $paymentMethod['paymentMethodId'],
                'off_session' => true,
                'confirm' => true,
            ]);

            $this->insertByArray('paymentStripePaymentIntent',
                array('intentId' => $intent->id,
                    'userId' => intval($payment['userId']),
                    'amount' => $payment['amount'],
                    'status' => $intent->status,
                    'paymentId' => intval($paymentId)
                ), []);


        } catch (\Stripe\Exception\CardException $e) {
            $errCode = $e->getError()->code;

            $user = $this->fetchOneById('user', intval($payment['userId']));
            if ($user['id'] <= 0){
                return array('error', 'user_doesnt_exist');
            }
            $this->insertByArray('paymentStripePaymentIntent',
                array('intentId' => $e->getError()->payment_intent->id,
                    'userId' => intval($payment['userId']),
                    'amount' => $payment['amount'],
                    'status' => $errCode,
                    'paymentId' => intval($paymentId)
                ), []);

            if ($errCode === 'authentication_required') {
                $link = $f3->get('path') . 'payment/reconfirm' . '?id=' . $paymentId;
                $message = "Votre prélèvement automatique pour la commande" . $payment['paymentOriginId'] . " a échoué. Veuillez valider à nouveau votre moyen de paiement en <a href='$link'>cliquant ici</a> ou sur le lien ci-dessous :<br><br>" . $link;
                $this->sendMailF3($f3, 'erreur de paiement', $message, $user['name'], $user['email']);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    public function createStripeCustomer($f3, $userId){
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        if ($userId <= 0){
            return array('error', 'user_doesnt_exist');
        }

        try {
            $user = $this->fetchOneById('user', intval($userId));

            $stripeCustomer = \Stripe\Customer::create(['email' =>$user['email'] ]);

            $customerId = $this->insertByArray('paymentStripeCustomer', array('userId' => intval($userId), 'customerId' => $stripeCustomer->id, 'delinquent' => $stripeCustomer->delinquent), []);
            return $this->fetchOneById('paymentStripeCustomer', intval($customerId));

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }

    }

    public function createPaymentMethod($f3, $paymentMethodId, $userId){
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        if ($userId <= 0){
            return array('error', 'user_doesnt_exist');
        }

        $customer = $this->fetchOneByKeysEqual('paymentStripeCustomer', array('userId' => intval($userId)));

        if (empty($customer)){
            $customer = $this->createStripeCustomer($f3, $userId);
        }

        try {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach([ 'customer' => $customer['customerId']]);

            $paymentMethodId = $this->insertByArray('paymentStripePaymentMethod', array('customerId' => intval($customer['id']), 'projectId'=>intval($f3->get('projectId')), 'type' => $paymentMethod->type, 'paymentMethodId' => $paymentMethod->id, 'offSessionUsage' => true ),[]);
            return array('success', array('paymentMethod_array' => $this->fetchOneById('paymentStripePaymentMethod', intval($paymentMethodId))));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    private function updateFuturePayments($f3, $paymentId)
    {
        $payment = $this->fetchOneById('payment', intval($paymentId));

        $query = "UPDATE `payment` SET `meanChargeId` = '" . $payment['meanChargeId'] . "' WHERE 
        `paymentOriginType` = '" . $payment['paymentOriginType'] . "' AND 
        `paymentOriginId` = '" . $payment['paymentOriginId'] . "' AND
        `status` = 'pending' AND
        `execution` > '" . $payment['execution'] . "'";

        $this->execCustomRequest($query);
    }

    /***************************************************/
    /*                                                 */
    /*             subscription api                    */
    /*                                                 */
    /***************************************************/

    public function createAbonnement($f3, abonnement $abonnement)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        try {
            $stripeProduct = \Stripe\Product::create([
                'name' => $abonnement->getName(),
                'type' => 'service'
            ]);

            $abonnement->setStripeAbonnementId($stripeProduct->id);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    public function createAbonnementPlan($f3, abonnementPlan $plan, array $scheme_array)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));
        $stripeInterval = $this->createStripeIntervalFromDateInterval($plan->getRegularInterval());
        $abonnement = new abonnement($this->fetchOneById('abonnement', intval($plan->getAbonnementId())));


        $abonnementData = array(
            'currency' => $plan->getCurrency(),
            'interval' => $stripeInterval['interval'],
            'interval_count' => $stripeInterval['interval_count'],
            'product' => $abonnement->getStripeAbonnementId(),
            'trial_period_days' => $plan->getTrialDays(),
            'usage_type' => (boolval($plan->getLicensedUsage()) === true) ? 'licensed' : 'metered',
            'billing_scheme' => (boolval($plan->getTieredScheme()) === true) ? 'tiered' : 'per_unit',
            'aggregate_usage' => $plan->getAggregateMethod()
        );

        if ($abonnementData['billing_scheme'] === 'per_unit') {
            if (count($scheme_array) > 1) {
                return array('error', 'multiple price_tiers and per_unit billing_scheme');
            }
            $abonnementData['amount'] = $scheme_array[0]->getPrice();
            if (intval($scheme_array[0]->getQuantityDivider()) > 1) {
                $abonnementData['transform_usage'] = array(
                    'divide_by' => intval($scheme_array[0]->getQuantityDivider()),
                    'round' => (boolval($scheme_array[0]->getRoundUp()) === true) ? 'up' : 'down'
                );
            } else {
                $scheme_array[0]->setQuantityDivider(1);
            }
        } else if ($abonnementData['billing_scheme'] === 'tiered') {
            $abonnementData['tiers_mode'] = (boolval($plan->getGraduated()) === true) ? 'graduated' : 'volume';

            foreach ($scheme_array as $scheme) {
                $abonnementData['tiers'][] = array(
                    'flat_amount' => $scheme->getFlatPrice(),
                    'unit_amount' => $scheme->getTierPrice(),
                    'up_to' => (intval($scheme->getMaxQuantity()) === -1) ? 'inf' : $scheme->getMaxQuantity()
                );
            }
        }

        try {
            $stripePlan = \Stripe\Plan::create($abonnementData);

            $plan->setStripePlanId($stripePlan->id);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    private function createStripeIntervalFromDateInterval($dateInterval)
    {
        $return = [];

        switch (substr($dateInterval, -1)) {
            case 'D':
                $return['interval'] = 'day';
                break;
            case 'M':
                $return['interval'] = 'month';
                break;
            case 'Y':
                $return['interval'] = 'year';
                break;
        }
        $return['interval_count'] = substr($dateInterval, 1, -1);
        return $return;
    }

    public function subscribeCustomer($f3, $subscriptionId, $paymentMethod, $backDate = null)
    {
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, "fr_FR");
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));


        $subscription = $this->fetchOneById('abonnementSubscription', intval($subscriptionId));
        $plan = $this->fetchOneById('abonnementPlan', intval($subscription['planId']));
        $card = $this->fetchOneByKeysEqual('paymentStripePaymentMethod', array('paymentMethodId' => $paymentMethod));
        $customer = $this->fetchOneById('paymentStripeCustomer', intval($card['customerId']));

        try {
            $startDate =  new \DateTime($backDate);

            if ($backDate !== null){
                $backStart =  new \DateTime($backDate);

                if ($startDate > $backStart){
                    return array('error', 'invalid_backDate');
                }
                $startDate = $backStart;
            }

            $today = new \DateTime();
            $billingAnchor = new \DateTime ($today->format($plan['billingAnchor']));

            if (strtotime($billingAnchor->format('Y-m-d H:i:s')) < strtotime($today->format('Y-m-d H:i:s')) && $plan['billingAnchor'] !== 'Y-m-d H:i:s') {
                $billingAnchor->add(new \DateInterval($plan['regularInterval']));
            }

            if($plan['intervalNumber'] > 0){
                $firstBill = clone $startDate;
                $cancel_at = ($firstBill->add(new \DateInterval('P'.($plan['intervalNumber'] * substr($plan['regularInterval'], 1, -1)).substr($plan['regularInterval'], -1))));
                $cancel_at = $cancel_at->format('Y-m-d H:i:s');
            }
            else {
                $cancel_at = null;
            }


            $subscriptionData= array(
                'customer' => $customer['customerId'],
                'items' => [['plan' => $plan['stripePlanId']]],
                'default_payment_method' => $paymentMethod,
                'proration_behavior' => 'create_prorations',
                'cancel_at' => ($cancel_at === null)? null: strtotime($cancel_at),
                'backdate_start_date' => ($backDate !== null)? strtotime($startDate->format('Y-m-d H:i:s')) :null
            );

            if ($plan['billingAnchor'] !== 'Y-m-d H:i:s'){
                $subscriptionData['billing_cycle_anchor'] =  strtotime($billingAnchor->format('Y-m-d H:i:s'));
            }


            $result = \Stripe\Subscription::create($subscriptionData);
            $this->updateByArrayById('abonnementSubscription', array(
                'status' => $result['status'],
                'subscriptionId' => $result['id'],
                'dateStart' => $startDate->format('Y-m-d H:i:s'),
                'dateStop' => ($cancel_at === null)? '2999-12-12 23:59:59': $cancel_at), intval($subscriptionId));
            return ($this->fetchOneById('abonnementSubscription', intval($subscriptionId)));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }

    }

    public function unsubscribeCustomer($f3, $subscriptionId){
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));


        $subscription = $this->fetchOneById('abonnementSubscription', intval($subscriptionId));
        try{
            $today = new \DateTime();

            $subscription = \Stripe\Subscription::retrieve(
                $subscription['subscriptionId']
            );
            $subscription->delete();

            $this->updateByArrayById('abonnementSubscription', array('status' => $subscription->status, 'dateStop' => $today->format('Y-m-d H:i:s')), intval($subscriptionId));
            return ($this->fetchOneById('abonnementSubscription', intval($subscriptionId)));
        }catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

    public function completeSubscription($f3, $subscription)
    {
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        try {
            $result = \Stripe\Subscription::retrieve($subscription['subscriptionId']);

            $invoice = \Stripe\Invoice::retrieve($result->latest_invoice);
            $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);

            return $paymentIntent;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }


    public function setSubscriptionUsage($f3, $subscriptionId, $usageQuantity, $timestamp){
        \Stripe\Stripe::setApiKey($f3->get('StripeSecretKey'));

        $subscription = $this->fetchOneById('abonnementSubscription', intval($subscriptionId));

        try{
            $subscription = \Stripe\Subscription::retrieve($subscription['subscriptionId']);

           $result =  \Stripe\SubscriptionItem::createUsageRecord($subscription->items->data[0]->id,
                array('quantity' => $usageQuantity,
                    'timestamp' => $timestamp,
                    'action' => 'set'));
           return $result;
        } catch(\Stripe\Exception\ApiErrorException $e) {
            return array('error', $e->getMessage());
        }
    }

}
