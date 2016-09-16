<?php

/*
 * Copyright (C) 2016 amit
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Multiple\Frontend\Controllers;

/**
 * Controller for Cart
 * @author amit
 */
class CartController extends FrontendController {

    /**
     * Initializing Cart Controller
     * Initializing layout and GUI elements
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index Action for Cart Details
     */
    public function indexAction() {
        $this->tag->setTitle('Booking Cart');
    }

    /**
     * Cart remove action
     * Item will remove from session cart
     * @param string $cartId
     */
    public function removeAction($cartId = '') {
        if ($cartId == '') {
            $this->cart->clear();
        } else {
            $this->cart->removeItem($cartId);
            $this->flash->success("Tour has been removed from your cart.");
        }
        $this->response->redirect('/cart');
    }

    /**
     * Action for cart custmer information
     */
    public function customerinfoAction() {
        //Prevent direct access to this action
        //Redirect to the cart page is not item exists in cart
        if ($this->cart->isEmpty()) {
            $this->response->redirect('/cart');
        } else {
            //Redirect member to the login page if not logged in
            if (!$this->member->isLoggedIn()) {
                $this->response->redirect('/auth/login?_rt=' . base64_encode($this->router->getRewriteUri()));
            } else {
                //$member = $this->member->getResource();
                $member = $this->member->refresh();
                $customerInfo = $this->cart->getCustomerInfo();
                if (isset($customerInfo['first_name']) && !empty($customerInfo['first_name'])) {
                    $member->firstName = $customerInfo['first_name'];
                }
                if (isset($customerInfo['last_name']) && !empty($customerInfo['last_name'])) {
                    $member->lastName = $customerInfo['last_name'];
                }
                if (isset($customerInfo['address_1']) && !empty($customerInfo['address_1'])) {
                    $member->addressOne = $customerInfo['address_1'];
                }
                if (isset($customerInfo['address_2']) && !empty($customerInfo['address_2'])) {
                    $member->addressTwo = $customerInfo['address_2'];
                }
                if (isset($customerInfo['city']) && !empty($customerInfo['city'])) {
                    $member->city = $customerInfo['city'];
                }
                if (isset($customerInfo['zip_code']) && !empty($customerInfo['zip_code'])) {
                    $member->zipCode = $customerInfo['zip_code'];
                }
                if (isset($customerInfo['state']) && !empty($customerInfo['state'])) {
                    $member->stateId = $customerInfo['state'];
                }
                if (isset($customerInfo['country']) && !empty($customerInfo['country'])) {
                    $member->countryId = $customerInfo['country'];
                }
                if (isset($customerInfo['email_address']) && !empty($customerInfo['email_address'])) {
                    $member->emailAddress = $customerInfo['email_address'];
                }
                if (isset($customerInfo['phone']) && !empty($customerInfo['phone'])) {
                    $member->phone = $customerInfo['phone'];
                }
                if (!empty($this->cart->getPaymentOption())) {
                    \Phalcon\Tag::setDefaults(array(
                        'payment' => $this->cart->getPaymentOption(),
                    ));
                }
                $form = new \Multiple\Frontend\Forms\MemberProfileForm($member, array('edit' => true));
                if ($this->request->isPost()) {
                    $valid = $form->isValid($this->request->getPost());
                    echo $valid;
                    
                    $pOpt = $this->request->getPost('payment');
                    if (!$pOpt || $pOpt == '') {
                        $this->flash->error((string) 'Please select your payment method');
                        $valid = FALSE && $valid;
                    }
                    
                    /////// by tarun////////
                    $address_1 = $this->request->getPost('address_1');
                    $country = $this->request->getPost('country');
                    $state = $this->request->getPost('state');
                    $city = $this->request->getPost('city');
                    
                    
                    if (!$address_1 || $address_1 == '') {
                        $this->flash->error((string) 'Please provide Address.');
                        $valid = FALSE && $valid;
                    }
                    elseif (!$country || $country == '') {
                        $this->flash->error((string) 'Please select your Country');
                        $valid = FALSE && $valid;
                    }                    
                    elseif (!$state || $state == '') {
                        $this->flash->error((string) 'Please select your state');
                        $valid = FALSE && $valid;
                    }                    
                    elseif (!$city || $city == '') {
                        $this->flash->error((string) 'Please select your city');
                        $valid = FALSE && $valid;
                    }
                    else{
                        $valid= true;
                    }
                    ////////by tarun////////////
                                     
                    if ($valid) {
                       //die("kk");  
                        $this->cart->setCustomerInfo(array(
                            'first_name' => $this->request->getPost('first_name'),
                            'last_name' => $this->request->getPost('last_name'),
                            'address_1' => $this->request->getPost('address_1'),
                            'address_2' => $this->request->getPost('address_2'),
                            'city' => $this->request->getPost('city'),
                            'zip_code' => $this->request->getPost('zip_code'),
                            'state' => $this->request->getPost('state'),
                            'country' => $this->request->getPost('country'),
                            'email_address' => $this->request->getPost('email_address'),
                            'phone' => $this->request->getPost('phone'),
                        ));
                        $paymentOption = $this->request->getPost('payment');
                        $this->cart->setPaymentOption($paymentOption);
//                        echo"<br>ppp";
//                        echo $paymentOption;
//                        die("kk");
                        switch ($paymentOption) {
                            case 'paypal':
                                $this->response->redirect('/payment/processPayment');
                                break;
                            case 'credit_card':
                                $this->response->redirect('/cart/cardpayment');
                                break;
                        }
                    } else {
                        foreach ($form->getMessages() as $formMessage) {
                            $this->flash->error((string) $formMessage);
                        }
                    }
                }
                $this->tag->setTitle('Customer Information');
                $this->view->form = $form;
            }
        }
    }

    /**
     * Payment action for credit card
     */
    public function cardpaymentAction() {
       
        if ($this->cart->isEmpty()) {
            
            $this->response->redirect('/cart');
        } else {
             
            if (!$this->member->isLoggedIn()) {
                 
                $this->response->redirect('/auth/login?_rt=' . base64_encode($this->router->getRewriteUri()));
            } else {
               
                $form = new \Multiple\Frontend\Forms\CardPaymentForm();
                if ($this->request->isPost()) {
                    
                    $saveCard = $this->request->getPost('card');
                    \Phalcon\Tag::setDefault("comment", $this->request->getPost('comment'));
                    \Phalcon\Tag::setDefault("card", $saveCard);
                    $valid = TRUE;
                    if ($saveCard == 'o') {
                        $valid = $form->isValid($this->request->getPost());
                    }
                    if ($valid) {
                        echo $saveCard;
                        //die("<br>saved card");
                        if ($saveCard == 'o') {
                            //die("kk");
                            $this->cart->setCardDetails(array(
                                'card_type' => $this->request->getPost('card_type'),
                                'card_number' => $this->request->getPost('card_number'),
                                'card_cvv' => $this->request->getPost('card_cvv'),
                                'card_exp_month' => $this->request->getPost('card_exp_month'),
                                'card_exp_year' => $this->request->getPost('card_exp_year'),
                                'card_name' => $this->request->getPost('card_name'),
                            ));
                            
                            // save new cc
                            $member = \Tourpage\Models\Members::findByMemberId($this->member->getId());
                            $member = $member[0];
                            $membercards = \Tourpage\Models\MembersCards::find([
                                'conditions' => 'cardNumber = :card_number:',
                                'bind' => ['card_number' => $this->request->getPost('card_number')]
                            ]);
                            if ((int)$membercards->count() == 0) {
                                $newcard = new \Tourpage\Models\MembersCards();
                                $newcard->memberId = $this->member->getId();
                                $newcard->firstName = $member->firstName;
                                $newcard->lastName = $member->lastName;
                                $newcard->addressOne = $member->addressOne;
                                $newcard->addressTwo = $member->addressTwo;
                                $newcard->phone = $member->phone;
                                $newcard->countryId = $member->countryId;
                                $newcard->stateId = $member->stateId;
                                $newcard->city = $member->city;
                                $newcard->zipCode = $member->zipCode;
                                $newcard->cardType = $this->request->getPost('card_type');
                                $newcard->cardNumber = $this->request->getPost('card_number');
                                $newcard->cardName = $this->request->getPost('card_name');
                                $newcard->cardCvv = $this->request->getPost('card_cvv');
                                $newcard->expiryMonth = $this->request->getPost('card_exp_month');
                                $newcard->expiryYear = $this->request->getPost('card_exp_year');
                                $newcard->status = 1;
                                $newcard->addedOn = \Tourpage\Helpers\Utils::currentDate();
                                $newcard->save();
                            }
                        } else {
                            
                            $cardDetails = \Tourpage\Models\MembersCards::findFirst($saveCard);
                            $this->cart->setCardDetails(array(
                                'card_type' => $cardDetails->cardType,
                                'card_number' => $cardDetails->cardNumber,
                                'card_cvv' => $cardDetails->cardCvv,
                                'card_exp_month' => $cardDetails->expiryMonth,
                                'card_exp_year' => $cardDetails->expiryYear,
                                'card_name' => $cardDetails->cardName,
                            ));
                        }
                       // die("cart");
                       $this->response->redirect('/payment/processPayment');                        
                    }
                }
//                 die("pp");
                $memberCards = [];
                $memberSaveCards = \Tourpage\Models\MembersCards::find(array(
                    'conditions' => 'memberId = :member_id: AND status = :status:',
                    'bind' => array(
                        'member_id' => $this->member->getId(),
                        'status' => \Tourpage\Models\MembersCards::ACTIVE_STATUS_CODE
                    )
                ));
                if ($memberSaveCards && $memberSaveCards->count() > 0) {
                    foreach ($memberSaveCards as $memberSaveCard) {
                        $memberCards[$memberSaveCard->cardId] = $memberSaveCard->cardNumber . ' ( ' . $memberSaveCard->cardName . ' )';
                    }
                    $memberCards['o'] = 'Others';
                }
                $this->tag->setTitle('Credit Card Payemnt');
                $this->view->form = $form;
                $this->view->memberCards = $memberCards;
            }
        }
    }

    public function bookingAction($status = '', $transactionId = '') {
       // echo $transactionId;
        //die("hi");
        switch ($status) {
            case 'complete':
                if (empty($transactionId)) {
                    return FALSE;
                }
                
                $booking = \Tourpage\Models\Booking::findFirstByTransactionId($transactionId);
                if (!$booking) {
                    return FALSE;
                }
                $this->tag->setTitle('Booking Complete');
                $this->view->booking = $booking;
                $this->view->pick('cart/booking/complete');
                break;
        }
    }
    
    public function thankyouAction() {
        $this->tag->setTitle('Payment Completed');
    }

}
