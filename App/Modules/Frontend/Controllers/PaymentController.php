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
 * Payment Handling Controller
 * @author amit
 */
class PaymentController extends FrontendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
    }

    /**
     * Processing Payment through PayPal
     */
    public function processPaymentAction() {
        
        $error = [];
        $paypal = new \Tourpage\Library\Paypal();
        $apiContext = $paypal->connect();

        $paymentOption = $this->cart->getPaymentOption();
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod($paymentOption);

        if ($paymentOption == 'credit_card') {
            
            $cardInfo = $this->cart->getCardDetails();
            $card = new \PayPal\Api\CreditCard();
            $nameString = $cardInfo['card_name'];
            $namePart = explode(' ', $nameString);
            $firstName = $lastName = '';
            if (count($namePart) > 1) {
                $lastName = array_pop($namePart);
                $firstName = implode(' ', $namePart);
            } else {
                $firstName = $nameString;
            }
            $formField = array(
                'first_name' => $firstName,
                'last_name' => $lastName,
            );

            $card->setType(strtolower($cardInfo['card_type']))
                    ->setNumber($cardInfo['card_number'])
                    ->setExpireMonth($cardInfo['card_exp_month'])
                    ->setExpireYear($cardInfo['card_exp_year'])
                    ->setCvv2($cardInfo['card_cvv'])
                    ->setFirstName($formField['first_name'])
                    ->setLastName($formField['last_name']);

            $fundingInstrument = new \PayPal\Api\FundingInstrument();
            $fundingInstrument->setCreditCard($card);

            $payer->setFundingInstruments(array($fundingInstrument));
        }

        $items = [];
        foreach ($this->cart->getItems() as $cartItem) {
            $itemData = \Tourpage\Models\Tours::findFirstByTourId($cartItem['tour_id']);
            $item = new \PayPal\Api\Item();
            $item->setName(htmlspecialchars($itemData->tourTitle))
                    ->setCurrency('USD')
                    ->setQuantity(1)
                    ->setPrice($cartItem['final_amount']);
            $items[] = $item;
        }

        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems($items);

        $amount = new \PayPal\Api\Amount();
        $amount->setCurrency("USD")
                ->setTotal($this->cart->totalAmount);

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("Payment for Tourpage Booking")
                ->setInvoiceNumber(uniqid());

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setTransactions(array($transaction));

        if ($paymentOption == 'paypal') {
            //die("inside paypal");
            $redirectUrls = new \PayPal\Api\RedirectUrls();
            $redirectUrls->setReturnUrl($this->url->get('/payment/return'))
                    ->setCancelUrl($this->url->get('/payment/cancel'));
            $payment->setRedirectUrls($redirectUrls);
        }

        try {
            $payment->create($apiContext);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            //$errorConnectionException = json_decode($ex->getData());
            if ($paymentOption == 'credit_card') {
                $error[] = 'Invalid Credit/Debit Card Details';
            }
        } catch (\Exception $e) {
            $error[] = $e->getMessage();
        }
        //echo print_r($error);
        //die("from process pay 3333");

        if (count($error) > 100) {  //// for test pourpose only. it have to initilize at 0;
            //die("ppp");
            foreach ($error as $er) {
                $this->flash->error((string) $er);
            }
            if ($paymentOption == 'paypal') {
                $this->response->redirect('/cart');
            }
            if ($paymentOption == 'credit_card') {
                $this->response->redirect('/cart/cardpayment');
            }
        } else {
             //die("no error block");
            if ($paymentOption == 'paypal') {
                
                $approvalUrl = $payment->getApprovalLink();
               // echo $approvalUrl;        // error, this is not printing
               //die("no error block paypal"); 
                ///$this->response->redirect($approvalUrl, true);  // code by amitda.
               $this->response->redirect('/payment/return?paymentId=5');  /// given by Tarun for testing
            }
            if ($paymentOption == 'credit_card') {
                    // print_r($payment);
                    //echo"<br>hehehe<br>";
                    //echo $payment->getId();
                    //die("kkkk1111");  
                //$this->response->redirect('/payment/return?paymentId=' . $payment->getId());  /// have to open
                ////for test porpose///
                $this->response->redirect('/payment/return?paymentId=10');
                ////for test porpose///
            }
        }
    }

    /**
     * Action for Return Url From PayPal
     */
    public function returnAction() {
       //die("Inside return block");
        $paymentId = $this->request->getQuery('paymentId');
        $token = $this->request->getQuery('token');
        $payerId = $this->request->getQuery('PayerID');

        $paymentOption = $this->cart->getPaymentOption();

        $error = [];
        $paypal = new \Tourpage\Library\Paypal();
        $apiContext = $paypal->connect();

        if ($paymentOption == 'paypal') {
            //die("Inside paypal block");
            $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($payerId);

            $amount = new \PayPal\Api\Amount();
            $amount->setCurrency('USD');
            $amount->setTotal($this->cart->totalAmount);

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount);
            $execution->addTransaction($transaction);
            try {
                $payment->execute($execution, $apiContext);
            } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                $error[] = print_r(json_decode($ex->getData()));
            } catch (\Exception $ex) {
                $error[] = $ex->getMessage();
            }
        }
        

        try {
            $paymentDetails = \PayPal\Api\Payment::get($paymentId, $apiContext);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            $error[] = print_r(json_decode($ex->getData()));
        } catch (\Exception $ex) {
            $error[] = $ex->getMessage();
        }
        //$error=0; //// for test pourpose only
         if (count($error) > 100) {   //// for test pourpose only. it have to initilize at 0;
//            print_r($error);
//         die("Inside error block");
            foreach ($error as $er) {
                $this->flash->error((string) $er);
            }
            $this->response->redirect('/cart');
        } else {
             //die("Inside No error block again");
            //$transactions = $paymentDetails->getTransactions(); //// for test pourpose only. it have to open
            //$relatedResources = $transactions[0]->getRelatedResources(); //// for test pourpose only. it have to open

            $booking = new \Tourpage\Models\Booking();
           //$booking->transactionId = $relatedResources[0]->getSale()->getId(); //// for test pourpose only. it have to open
            ////////for test pourpose only////
            $booking->transactionId = "tarun". date('H-i-sa-', time())."ss";
            //echo $booking->transactionId;
            //die("<br>hfhfhfg block");
            /////////////
            $booking->paymentId = $paymentId;
            $booking->token = $token;
            $booking->payerId = $payerId;
            $booking->paymentOption = $paymentOption;
            $booking->memberId = $this->member->getId();
            if ($this->member->getResource()->isAgent()) {
                $booking->agentId = $this->member->getId();
            }
            $booking->bookingAmount = $this->cart->totalAmount;
            $booking->bookedOn = \Tourpage\Helpers\Utils::currentDate();
            $booking->bookingStatus = \Tourpage\Models\Booking::COMPLETE_STATUS_CODE;
            $booking->paymentStatus = \Tourpage\Models\Booking::PAYMENT_PAID_STATUS_CODE;
            $booking->bookingComments = $this->request->getPost('comment', array('string', 'striptags'));
            if ($booking->save()) {
                 //die("save block");
                $invoiceNumber = 'TPINV/' . date('Y/m/d/', strtotime($booking->bookedOn)) . date('H/i/', time()) . $booking->bookingId;
                $booking->invoiceNumber = $invoiceNumber;
                $sql = "update booking set `invoiceNumber`='$invoiceNumber' where  `bookingId`='".$booking->bookingId."'";
		$this->db->query($sql);
                //echo $booking->invoiceNumber;
                //echo"kkk<br>";
                //echo $booking->bookingId;
                //die("<br>save block");
                if (!$booking->save()) {
                    foreach ($booking->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
                $vendorIds = [];
                foreach ($this->cart->getItems() as $cartItem) {
                    $headCount = 0;
                    $tourData = [];
                    if (isset($cartItem['head_count']) && count($cartItem['head_count']) > 0) {
                        foreach ($cartItem['head_count'] as $hc) {
                            $headCount += $hc['count'];
                        }
                        $tourData['head_count'] = $cartItem['head_count'];
                    }
                    if (isset($cartItem['group_head_count'])) {
                        $headCount += $cartItem['group_head_count'];
                        $tourData['group_head_count'] = $cartItem['group_head_count'];
                    }
                    if (isset($cartItem['tour_opt']) && count($cartItem['tour_opt']) > 0) {
                        $tourData['tour_opt'] = $cartItem['tour_opt'];
                    }
                    if (isset($cartItem['time_slot'])) {
                        $tourData['time_slot'] = $cartItem['time_slot'];
                    }
                    if (isset($cartItem['multi_purches_discount'])) {
                        $tourData['multi_purches_discount'] = $cartItem['multi_purches_discount'];
                        $tourData['multi_purches_save_amount'] = $cartItem['multi_purches_save_amount'];
                    }
                    if (!in_array($cartItem['vendor_id'], $vendorIds)) {
                        $vendorIds[] = $cartItem['vendor_id'];
                    }
                    $bookingTour = new \Tourpage\Models\BookingTours();
                    $bookingTour->bookingId = $booking->bookingId;
                    $bookingTour->tourId = $cartItem['tour_id'];
                    $bookingTour->vendorId = $cartItem['vendor_id'];
                    $bookingTour->originalAmount = $cartItem['original_amount'];
                    $bookingTour->finalAmount = $cartItem['final_amount'];
                    $bookingTour->discount = isset($cartItem['discount']) ? $cartItem['discount'] : 0;
                    $bookingTour->tourData = serialize($tourData);
                    $bookingTour->departureOn = $cartItem['departure_on'];
                    $bookingTour->arivalOn = isset($cartItem['arival_on']) ? $cartItem['arival_on'] : NULL;
                    $bookingTour->headCount = $headCount;
                    $bookingTour->tourConductStatus = \Tourpage\Models\BookingTours::TOUR_CONDUCT_PENDING_STATUS_CODE;
                    if ($bookingTour->save()) {
                        if ($bookingTour->tour->tourCapacity > 0) {
                            $tourCapacity = $bookingTour->tour->tourCapacity;
                            $reduceCapacity = $tourCapacity - $headCount;
                            if ($reduceCapacity < 0) {
                                $reduceCapacity = 0;
                            }
                            //$bookingTour->tour->tourCapacity = $reduceCapacity;
                            //$bookingTour->tour->save();
                        }
                    }
                }
                $customerInfo = $this->cart->getCustomerInfo();
                $bookingAddress = new \Tourpage\Models\BookingAddress();
                $bookingAddress->bookingId = $booking->bookingId;
                $bookingAddress->billingFirstName = $customerInfo['first_name'];
                $bookingAddress->billingLastName = $customerInfo['last_name'];
                $bookingAddress->billingEmailAddress = $customerInfo['email_address'];
                $bookingAddress->billingPhone = (isset($customerInfo['phone']) && !empty($customerInfo['phone'])) ? $customerInfo['phone'] : NULL;
                $bookingAddress->billingAddressOne = $customerInfo['address_1'];
                $bookingAddress->billingAddressTwo = (isset($customerInfo['address_2']) && !empty($customerInfo['address_2'])) ? $customerInfo['address_2'] : NULL;
                $bookingAddress->billingCity = $customerInfo['city'];
                $bookingAddress->billingState = $customerInfo['state'];
                $bookingAddress->billingCountry = $customerInfo['country'];
                $bookingAddress->billingZipCode = (isset($customerInfo['zip_code']) && !empty($customerInfo['zip_code'])) ? $customerInfo['zip_code'] : NULL;
                $bookingAddress->save();

                //Sending Mail Confirmation To Customer About Booking Confirmation
                $mail = new \Tourpage\Library\Mail();
                $mail->setTo($this->member->getEmail(), $this->member->getFullName());
                $mail->setSubject('Tourpage booking confirmation');
                $mail->setBody($this->getTemplateForBooking($booking));
                $mail->send();

                //Sending Mail Notification To Vendor About Booking Recieved
                if (count($vendorIds) > 0) {
                    foreach ($vendorIds as $vendorId) {
                        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
                        if ($vendor && $vendor->count() > 0) {
                            $memberToVendor = \Tourpage\Models\MembersToVendor::findFirst(array(
                                        'conditions' => 'memberId = :member_id: AND vendorId = :vendor_id:',
                                        'bind' => array(
                                            'member_id' => $this->member->getId(),
                                            'vendor_id' => $vendor->vendorId
                                        )
                            ));
                            if (!$memberToVendor) {
                                $memberToVendorCreate = new \Tourpage\Models\MembersToVendor();
                                $memberToVendorCreate->memberId = $this->member->getId();
                                $memberToVendorCreate->vendorId = $vendor->vendorId;
                                $memberToVendorCreate->save();
                            }

                            $mail = new \Tourpage\Library\Mail();
                            $mail->setTo($vendor->emailAddress, $vendor->firstName . ' ' . $vendor->lastName);
                            $mail->setSubject('Tourpage booking recived - Vendor');
                            $mail->setBody($this->getTemplateForBooking($booking, 'vendor_booking', $vendorId));
                            $mail->send();
                        }
                    }
                }

                //Sending Mail Notification To Admin About Booking Recieved
                $admin = \Tourpage\Models\Admins::findFirstByUserName('admin');
                if ($admin && $admin->count() > 0) {
                    $mail = new \Tourpage\Library\Mail();
                    $mail->setTo($admin->emailAddress, $admin->firstName . ' ' . $admin->lastName);
                    $mail->setSubject('Tourpage booking recived - Admin');
                    $mail->setBody($this->getTemplateForBooking($booking, 'admin_booking'));
                    $mail->send();
                }

                $this->cart->clear();
                $this->flash->success("Order has been placed successfully.");
                $this->response->redirect('/cart/booking/complete/' . $booking->transactionId);
            } else {
                die("last error block");
                foreach ($booking->getMessages() as $message) {
                    $this->flash->error((string) $message);
                }
            }
        }
    }

    private function getTemplateForBooking(\Tourpage\Models\Booking $booking, $template = 'customer_booking', $vendorId = 0) {
        $emailTemplate = clone $this->view;
        $emailTemplate->start();
        $emailTemplate->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $emailTemplate->booking = $booking;
        if ($vendorId > 0) {
            $emailTemplate->vendorId = $vendorId;
        }
        $emailTemplate->render('cart', 'mail/' . $template);
        $emailTemplate->finish();
        $content = $emailTemplate->getContent();
        return $content;
    }

    /**
     * Action for Cancel Return Url From PayPal
     */
    public function cancelAction() {
        $token = $this->request->getQuery('token');
        $this->cart->clear();
        $this->flash->warning("Order has been canceled.");
        $this->response->redirect('/cart');
    }

}
