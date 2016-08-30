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

namespace Multiple\Vendor\Controllers;

/**
 * Description of BookingController
 * @author amit
 */
class BookingController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for Booking Controller
     */
    public function indexAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        $modelBind = [];
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        if ($this->request->isPost()) {
            $queryString = '';
            $invoiceNumber = $this->request->getPost('invn');
            $bookingFrom = $this->request->getPost('bkf');
            $bookingTo = $this->request->getPost('bkt');
            $bookingAgent = $this->request->getPost('ba');
            $bookingEmployee = $this->request->getPost('be');
            $redirectTo = $this->url->get('/vendor/booking');
            if ($invoiceNumber != '') {
                $queryString .= 'invn=' . urlencode($invoiceNumber) . '&';
            }
            if ($bookingFrom != '') {
                $queryString .= 'bkf=' . urlencode($bookingFrom) . '&';
            }
            if ($bookingTo != '') {
                $queryString .= 'bkt=' . urlencode($bookingTo) . '&';
            }
            if ($this->vendors->getVendorData()->isParent()) {
                if ($bookingAgent != '') {
                    if ($bookingAgent != '[all]') {
                        $queryString .= 'ba=' . $bookingAgent . '&';
                    }
                }
                if ($bookingEmployee != '') {
                    if ($bookingEmployee != '[all]') {
                        $queryString .= 'be=' . $bookingEmployee . '&';
                    }
                }
            }
            if ($queryString) {
                $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                $redirectTo = $redirectTo . '?' . $queryString;
            }
            $this->response->redirect($redirectTo);
        }
        $booking = \Tourpage\Models\BookingTours::query();
        $booking->where("\Tourpage\Models\BookingTours.vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $vendorId;
        if (!$this->vendors->getVendorData()->isParent()) {
            $booking->leftJoin('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\BookingTours.tourId', 'vt');
            $booking->addWhere("vt.createBy = :create_by:");
            $modelBind['create_by'] = $this->vendors->getId();
        }
        if ($this->request->hasQuery('bkf') && $this->request->hasQuery('bkt')) {
            $defaultValues['bkf'] = urldecode($this->request->getQuery('bkf'));
            $modelBind['fbookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkf']);
            $defaultValues['bkt'] = urldecode($this->request->getQuery('bkt'));
            $modelBind['tbookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkt']);
            $booking->leftJoin('\Tourpage\Models\Booking', 'bbkf.bookingId = \Tourpage\Models\BookingTours.bookingId', 'bbkf');
            $booking->addWhere("bbkf.bookedOn >= :fbookedOn: AND bbkf.bookedOn <= :tbookedOn:");
        } else {
            if ($this->request->hasQuery('bkf')) {
                $defaultValues['bkf'] = urldecode($this->request->getQuery('bkf'));
                $modelBind['bookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkf']);
                $booking->leftJoin('\Tourpage\Models\Booking', 'bbkf.bookingId = \Tourpage\Models\BookingTours.bookingId', 'bbkf');
                $booking->addWhere("bbkf.bookedOn >= :bookedOn:");
            }
            if ($this->request->hasQuery('bkt')) {
                $defaultValues['bkt'] = urldecode($this->request->getQuery('bkt'));
                $modelBind['bookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkt']);
                $booking->join('\Tourpage\Models\Booking', 'bbkt.bookingId = \Tourpage\Models\BookingTours.bookingId', 'bbkt');
                $booking->addWhere("bbkt.bookedOn <= :bookedOn:");
            }
        }
        if ($this->request->hasQuery('invn')) {
            $defaultValues['invn'] = urldecode($this->request->getQuery('invn'));
            $modelBind['invoiceNumber'] = $defaultValues['invn'];
            $booking->join('\Tourpage\Models\Booking', 'binvn.bookingId = \Tourpage\Models\BookingTours.bookingId', 'binvn');
            $booking->addWhere("binvn.invoiceNumber = :invoiceNumber:");
        }
        if ($this->vendors->getVendorData()->isParent()) {
            if ($this->request->hasQuery('ba')) {
                $defaultValues['ba'] = $this->request->getQuery('ba');
                $modelBind['bookingAgent'] = $defaultValues['ba'];
                $booking->join('\Tourpage\Models\Booking', 'bbm.bookingId = \Tourpage\Models\BookingTours.bookingId', 'bbm');
                $booking->addWhere("bbm.agentId = :bookingAgent:");
            }
            if ($this->request->hasQuery('be')) {
                $defaultValues['be'] = $this->request->getQuery('be');
                $modelBind['bookingEmployee'] = $defaultValues['be'];
                $booking->addWhere("\Tourpage\Models\BookingTours.employeeId = :bookingEmployee:");
            }
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $booking->bind($modelBind);
        }

        $booking->order("\Tourpage\Models\BookingTours.bookingId DESC");
        $booking->groupBy("\Tourpage\Models\BookingTours.bookingId");
        $bookings = $booking->execute();

        $bookedByAgents = [];
        if ($this->vendors->getVendorData()->isParent()) {
            $agents = \Tourpage\Models\VendorsRegisteredAgents::find(array(
                        'conditions' => 'vendorId = :vendor_id: AND requestStatus = :request_status:',
                        'bind' => array(
                            'vendor_id' => $vendorId,
                            'request_status' => \Tourpage\Models\VendorsRegisteredAgents::AGENT_APPROVED_STATUS_CODE,
                        ),
                        'order' => 'vragentId DESC'
            ));
            if ($agents && $agents->count() > 0) {
                foreach ($agents as $agent) {
                    $bookedByAgents[$agent->memberId] = $agent->member->getName();
                }
            }
        }

        $bookingEmployees = [];
        if ($this->vendors->getVendorData()->isParent()) {
            $employeeQuery = \Tourpage\Models\Vendors::query();
            $employeeQuery->where('parentId = :parent_id:');
            $employeeQuery->bind(array(
                'parent_id' => $vendorId
            ));
            $employeeQuery->order("vendorId DESC");
            $employees = $employeeQuery->execute();
            if ($employees && $employees->count() > 0) {
                foreach ($employees as $employee) {
                    $bookingEmployees[$employee->vendorId] = $employee->getName();
                }
            }
        }

        $pager = new \Tourpage\Library\Pager(array(
            "data" => $bookings,
            "page" => $page,
        ));
        $this->tag->setTitle('Booking');
        $this->view->setVars(array(
            'pager' => $pager,
            'defaultValues' => $defaultValues,
            'bookedByAgents' => $bookedByAgents,
            'bookingEmployees' => $bookingEmployees,
        ));
    }

    /**
     * Action for view Booking Detials
     * @param int $bookingId
     * @return boolean
     */
    public function detailsAction($bookingId) {
        if (!preg_match_all('/[0-9]+/', $bookingId, $matches)) {
            return false;
        }
        $booking = \Tourpage\Models\Booking::findFirst($bookingId);
        if (!$booking) {
            return false;
        }
        if ($this->request->isPost()) {
            $booking_payment_status = $this->request->getPost('booking_payment_status');
            $booking->paymentStatus = $booking_payment_status;
            $booking->save();
            $this->flash->success('Payment Status for this booking has been changed to "Paid"');
            $this->response->redirect($this->router->getRewriteUri());
        }

        $this->tag->setTitle('Booking Details');
        $this->view->setVar('booking', $booking);
    }

    /**
     * Add New Booking
     */
    public function addAction($step = 1) {
        if (!preg_match_all('/[0-9]+/', $step, $matches)) {
            return false;
        }
        switch ($step) {
            case '1':
                if ($this->request->isPost()) {
                    $tourId = $this->request->getPost('tid');
                    $tourCriteria = array(
                        'conditions' => 'tourId = :tour_id:',
                        'bind' => array('tour_id' => $tourId),
                    );
                    $tour = \Tourpage\Models\Tours::findFirst($tourCriteria);
                    $journeyStart = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('jstd'));
                    if ($tour->tourDuration->lengthType == \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE) {
                        $journeyEnd = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('jedd'));
                    }
                    if ($tour->tourDuration->lengthType == \Tourpage\Models\Tours::LENGTH_HOURLY) {
                        $timeSlot = $this->request->getPost('tour_time_slot');
                    }
                    $tourOptions = $this->request->getPost('tour_opt');
                    $totalAmount = $this->request->getPost('total_amount');
                    $bookingData = [];
                    $bookingData['tour_id'] = $tourId;
                    $bookingData['vendor_id'] = $tour->tourVendor->vendorId;
                    $bookingData['departure_on'] = $journeyStart;
                    if (isset($journeyEnd)) {
                        $bookingData['arival_on'] = $journeyEnd;
                    }
                    if (isset($timeSlot)) {
                        $bookingData['time_slot'] = unserialize(base64_decode($timeSlot));
                    }
                    $fullAmount = 0;
                    switch ($tour->tourPrice->data->priceType) {
                        case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE:
                            $bookingData['head_count'] = [];
                            $headCount = $this->request->getPost('head_count');
                            if (count($headCount) > 0) {
                                foreach ($headCount as $ag => $c) {
                                    if ($c > 0) {
                                        if (!isset($bookingData['head_count'][$ag])) {
                                            $bookingData['head_count'][$ag] = [];
                                        }
                                        $bookingData['head_count'][$ag] = [
                                            'count' => $c,
                                            'unit_price' => $tour->tourPrice->data->priceGroup[$ag]['price'],
                                            'amount' => $tour->tourPrice->data->priceGroup[$ag]['price'] * $c,
                                        ];
                                    }
                                }
                            }
                            if (count($headCount) > 0) {
                                foreach ($headCount as $ag => $c) {
                                    $fullAmount += $tour->tourPrice->data->priceGroup[$ag]['price'] * $c;
                                }
                            }
                            break;
                        case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE:
                            $bookingData['group_head_count'] = $this->request->getPost('group_head_count');
                            $fullAmount += $tour->tourPrice->data->priceDefault;
                            break;
                    }

                    if (count($tourOptions) > 0) {
                        $bookingData['tour_opt'] = [];
                        foreach ($tourOptions as $optId => $optAmt) {
                            if (!isset($bookingData['tour_opt'][$optId])) {
                                $bookingData['tour_opt'][$optId] = [];
                            }
                            $optMdl = \Tourpage\Models\ToursOptions::findFirst($optId);
                            if ($optMdl) {
                                $bookingData['tour_opt'][$optId] = [
                                    'name' => $optMdl->optionName,
                                    'type' => $optMdl->optionPriceType,
                                    'unit_price' => $optAmt,
                                    'amount' => 0
                                ];
                                switch ($optMdl->optionPriceType) {
                                    case \Tourpage\Models\ToursOptions::OPTION_PER_PERSON_CODE:
                                        switch ($tour->tourPrice->data->priceType) {
                                            case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE:
                                                $bookingData['tour_opt'][$optId]['head_count'] = 0;
                                                foreach ($headCount as $ag => $c) {
                                                    $fullAmount += $optAmt * $c;
                                                    $bookingData['tour_opt'][$optId]['head_count'] += $c;
                                                    $bookingData['tour_opt'][$optId]['amount'] += $optAmt * $c;
                                                }
                                                break;
                                            case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE:
                                                $c = $bookingData['group_head_count'];
                                                $fullAmount += $optAmt * $c;
                                                $bookingData['tour_opt'][$optId]['head_count'] = $c;
                                                $bookingData['tour_opt'][$optId]['amount'] = $optAmt * $c;
                                                break;
                                        }
                                        break;
                                    case \Tourpage\Models\ToursOptions::OPTION_PER_GROUP_CODE:
                                        $fullAmount += $optAmt;
                                        $bookingData['tour_opt'][$optId]['amount'] += $optAmt;
                                        break;
                                }
                            }
                        }
                    }
                    $bookingData['original_amount'] = $fullAmount;
                    $bookingData['final_amount'] = $totalAmount;
                    if ($tour->tourPrice->hasDiscount()) {
                        $bookingData['discount'] = $tour->tourPrice->data->discount->price;
                        $bookingData['save_amount'] = (($fullAmount * $tour->tourPrice->data->discount->price) / 100);
                        //$fullAmount = $fullAmount - (($fullAmount * $tour->tourPrice->data->discount->price) / 100);
                    }
                    if ($tour->tourPrice->hasMultiPurchesDiscount()) {
                        $hdCnt = 0;
                        switch ($tour->tourPrice->data->priceType) {
                            case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE:
                                if (isset($bookingData['head_count']) > 0) {
                                    foreach ($bookingData['head_count'] as $ag => $c) {
                                        if (isset($c['count']) && $c['count'] > 0) {
                                            $hdCnt += $c['count'];
                                        }
                                    }
                                }
                                break;
                            case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE:
                                if (isset($bookingData['group_head_count']) > 0) {
                                    $hdCnt = $bookingData['group_head_count'];
                                }
                                break;
                        }
                        if ($hdCnt >= $tour->tourPrice->data->discount->multiplePurchase->count) {
                            $bookingData['multi_purches_discount'] = $tour->tourPrice->data->discount->multiplePurchase->percentage;
                            $bookingData['multi_purches_save_amount'] = (($fullAmount * $tour->tourPrice->data->discount->multiplePurchase->percentage) / 100);
                        }
                    }
                    $customerType = $this->request->getPost('user_type');
                    if (!isset($bookingData['customer_info'])) {
                        $bookingData['customer_info'] = [];
                    }
                    $bookingData['customer_info']['type'] = $customerType;
                    if ($customerType == 'e') {
                        $existingMemberId = $this->request->getPost('ex_member_id');
                        if (!empty($existingMemberId)) {
                            $member = \Tourpage\Models\Members::findFirst($existingMemberId);
                            if ($member && $member->count() > 0) {
                                $bookingData['customer_info']['id'] = $member->getMemberId();
                                $bookingData['customer_info']['first_name'] = $member->getFirstName();
                                $bookingData['customer_info']['last_name'] = $member->getLastName();
                                $bookingData['customer_info']['address_1'] = $member->getAddressOne();
                                $bookingData['customer_info']['address_2'] = $member->getAddressTwo();
                                $bookingData['customer_info']['city'] = $member->getCity();
                                $bookingData['customer_info']['zip_code'] = $member->getZipCode();
                                $bookingData['customer_info']['state'] = $member->getStateId();
                                $bookingData['customer_info']['country'] = $member->getCountryId();
                                $bookingData['customer_info']['email_address'] = $member->getEmailAddress();
                                $bookingData['customer_info']['phone'] = $member->getPhone();
                            }
                        }
                    }
                    if (!empty($bookingData)) {
                        $this->session->set('booking_data', $bookingData);
                    }
                    $this->response->redirect('/vendor/booking/add/2?ack=bk&_t=' . base64_encode($tourId));
                }
                $modelBind = $tours = [];
                $vendorTours = \Tourpage\Models\VendorsTours::query();
                $vendorTours->where("vendorId = :vendor_id:");
                $modelBind['vendor_id'] = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
                if (!$this->vendors->getVendorData()->isParent()) {
                    $vendorTours->andWhere("createBy = :create_by:");
                    $modelBind['create_by'] = $this->vendors->getId();
                }
                if (count($modelBind) > 0) {
                    $vendorTours->bind($modelBind);
                }
                $vendorTours->order("vendorTourId DESC");
                $vendorTourExecute = $vendorTours->execute();
                if ($vendorTourExecute && $vendorTourExecute->count() > 0) {
                    foreach ($vendorTourExecute as $tourExecute) {
                        if ($tourExecute->tour->isBookingActive()) {
                            $tours[$tourExecute->tourId] = $tourExecute->tour->tourTitle;
                        }
                    }
                }

                $customersName = [];
                $members = \Tourpage\Models\Members::find(array(
                            'conditions' => 'status = :status: AND isAgent = :agent_status:',
                            'bind' => array(
                                'status' => \Tourpage\Models\Members::ACTIVE_STATUS_CODE,
                                'agent_status' => \Tourpage\Models\Members::IS_NOT_AGENT_STATUS_CODE,
                            )
                ));
                if ($members && $members->count() > 0) {
                    foreach ($members as $member) {
                        $customersName[] = [
                            'label' => $member->getFirstName() . ' ' . $member->getLastName(),
                            'value' => $member->getFirstName() . ' ' . $member->getLastName(),
                            'memberId' => $member->getMemberId(),
                            'emailAddress' => $member->getEmailAddress(),
                            'phone' => $member->getPhone(),
                            'addressOne' => $member->getAddressOne(),
                            'addressTwo' => $member->getAddressTwo(),
                            'city' => $member->getCity(),
                            'state' => $member->getState() ? $member->getState()->getName() : '',
                            'country' => $member->getCountry() ? $member->getCountry()->getName() : '',
                            'zipCode' => $member->getZipCode(),
                            'avatar' => $member->getAvatarUri(\Tourpage\Models\Members::AVATAR_THUMB),
                        ];
                    }
                }
                $this->tag->setTitle('New Booking');
                $this->view->setVar('tours', $tours);
                $this->view->setVar('customersName', $customersName);
                $this->view->pick('booking/add/step_1');
                break;
            case '2':
                $bookingData = $this->session->get('booking_data');
                if (empty($bookingData)) {
                    $this->response->redirect('/vendor/booking/add');
                }
                if ($this->request->isPost()) {
                    $errors = [];
                    $customerType = $bookingData['customer_info']['type'];
                    $emailAddress = $this->request->getPost('email_address');
                    if ($customerType == 'n') {
                        $memberCriteria = [
                            'conditions' => 'emailAddress = :email:',
                            'bind' => [
                                'email' => $emailAddress
                            ]
                        ];
                        $memberCount = \Tourpage\Models\Members::count($memberCriteria);
                        if ($memberCount == 0) {
                            $errors[] = 'User exists with given email address';
                        }
                    }

                    if (count($errors) == 0) {
                        $bookingData['customer_info']['first_name'] = $this->request->getPost('first_name');
                        $bookingData['customer_info']['last_name'] = $this->request->getPost('last_name');
                        $bookingData['customer_info']['address_1'] = $this->request->getPost('address_1');
                        $bookingData['customer_info']['address_2'] = $this->request->getPost('address_2');
                        $bookingData['customer_info']['city'] = $this->request->getPost('city');
                        $bookingData['customer_info']['zip_code'] = $this->request->getPost('zip_code');
                        $bookingData['customer_info']['state'] = $this->request->getPost('state');
                        $bookingData['customer_info']['country'] = $this->request->getPost('country');
                        $bookingData['customer_info']['email_address'] = $emailAddress;
                        $bookingData['customer_info']['phone'] = $this->request->getPost('phone');

                        $bookingData['payment_options'] = $this->request->getPost('payment_opt');
                        if ($bookingData['payment_options'] === 'credit_card') {
                            $saveCard = $this->request->getPost('card');
                            if ($saveCard) {
                                $cardDetails = \Tourpage\Models\MembersCards::findFirst($saveCard);
                                $bookingData['card_info'] = array(
                                    'card_type' => $cardDetails->cardType,
                                    'card_number' => $cardDetails->cardNumber,
                                    'card_cvv' => $cardDetails->cardCvv,
                                    'card_exp_month' => $cardDetails->expiryMonth,
                                    'card_exp_year' => $cardDetails->expiryYear,
                                    'card_name' => $cardDetails->cardName,
                                );
                            } else {
                                $bookingData['card_info'] = array(
                                    'card_type' => $this->request->getPost('card_type'),
                                    'card_number' => $this->request->getPost('card_number'),
                                    'card_cvv' => $this->request->getPost('card_cvv'),
                                    'card_exp_month' => $this->request->getPost('card_exp_month'),
                                    'card_exp_year' => $this->request->getPost('card_exp_year'),
                                    'card_name' => $this->request->getPost('card_name'),
                                );
                            }
                        }
                        if ($bookingData['payment_options'] === 'bank_transfer') {
                            $bookingData['bank_info'] = array(
                                'account_name' => $this->request->getPost('bank_acc_name'),
                                'account_number' => $this->request->getPost('bank_acc_no'),
                                'account_ifsc' => $this->request->getPost('bank_acc_ifsc'),
                            );
                        }
                        if (!empty($bookingData)) {
                            $this->session->set('booking_data', $bookingData);
                        }
                        $this->response->redirect('/vendor/booking/paymentProcess');
                    } else {
                        foreach ($errors as $error) {
                            $this->flash->error($error);
                        }
                    }
                }
                $modelBind = $tours = [];
                $vendorTours = \Tourpage\Models\VendorsTours::query();
                $vendorTours->where("vendorId = :vendor_id:");
                $modelBind['vendor_id'] = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
                if (!$this->vendors->getVendorData()->isParent()) {
                    $vendorTours->andWhere("createBy = :create_by:");
                    $modelBind['create_by'] = $this->vendors->getId();
                }
                if (count($modelBind) > 0) {
                    $vendorTours->bind($modelBind);
                }
                $vendorTours->order("vendorTourId DESC");
                $vendorTourExecute = $vendorTours->execute();
                if ($vendorTourExecute && $vendorTourExecute->count() > 0) {
                    foreach ($vendorTourExecute as $tourExecute) {
                        if ($tourExecute->tour->isBookingActive()) {
                            $tours[$tourExecute->tourId] = $tourExecute->tour->tourTitle;
                        }
                    }
                }
                $countries = \Tourpage\Models\Country::find(array(
                            'conditions' => 'status = :status:',
                            'bind' => array('status' => \Tourpage\Models\Country::ACTIVE_STATUS_CODE)
                ));

                $states = $memberCards = [];
                if ($bookingData['customer_info']['type'] == 'e') {
                    $states = \Tourpage\Models\State::find(array(
                                'conditions' => 'countryId = :country_id: AND status = :status:',
                                'bind' => array(
                                    'country_id' => $bookingData['customer_info']['country'],
                                    'status' => \Tourpage\Models\State::ACTIVE_STATUS_CODE
                                )
                    ));
                    $memberSaveCards = \Tourpage\Models\MembersCards::find(array(
                                'conditions' => 'memberId = :member_id: AND status = :status:',
                                'bind' => array(
                                    'member_id' => $bookingData['customer_info']['id'],
                                    'status' => \Tourpage\Models\MembersCards::ACTIVE_STATUS_CODE
                                )
                    ));
                    if ($memberSaveCards && $memberSaveCards->count() > 0) {
                        foreach ($memberSaveCards as $memberSaveCard) {
                            $memberCards[$memberSaveCard->cardId] = $memberSaveCard->cardNumber . ' ( ' . $memberSaveCard->cardName . ' )';
                        }
                        $memberCards['o'] = 'Others';
                    }
                } else {
                    if ($bookingData['customer_info']['type'] == 'n') {
                        if ($this->request->isPost()) {
                            $countryId = $this->request->getPost('country');
                            $states = \Tourpage\Models\State::find(array(
                                        'conditions' => 'countryId = :country_id: AND status = :status:',
                                        'bind' => array(
                                            'country_id' => $countryId,
                                            'status' => \Tourpage\Models\State::ACTIVE_STATUS_CODE
                                        )
                            ));
                        }
                    }
                }

                \Phalcon\Tag::setDefaults($bookingData['customer_info']);
                $this->tag->setTitle('New Booking');
                $this->view->setVar('tours', $tours);
                $this->view->setVar('bookingData', $bookingData);
                $this->view->setVar('countries', $countries);
                $this->view->setVar('states', $states);
                $this->view->setVar('memberCards', $memberCards);
                $this->view->pick('booking/add/step_2');
                break;
        }
    }

    /**
     * Action PayPal Payment Processing
     */
    public function paymentProcessAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        $bookingData = $this->session->get('booking_data');

        $paymentOption = $bookingData['payment_options'];
        $error = [];
        if ($paymentOption == 'credit_card') {
            $paypal = new \Tourpage\Library\Paypal();
            $apiContext = $paypal->connect();

            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod($paymentOption);

            $cardInfo = $bookingData['card_info'];
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

            $itemData = \Tourpage\Models\Tours::findFirstByTourId($bookingData['tour_id']);
            $item = new \PayPal\Api\Item();
            $item->setName(htmlspecialchars($itemData->tourTitle))
                    ->setCurrency('USD')
                    ->setQuantity(1)
                    ->setPrice($bookingData['final_amount']);
            $itemList = new \PayPal\Api\ItemList();
            $itemList->setItems(array($item));

            $amount = new \PayPal\Api\Amount();
            $amount->setCurrency("USD")->setTotal($bookingData['final_amount']);

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription("Payment for Tourpage Booking")
                    ->setInvoiceNumber(uniqid());

            $payment = new \PayPal\Api\Payment();
            $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setTransactions(array($transaction));

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
        }
        if (count($error) > 0) {
            foreach ($error as $er) {
                $this->flash->error((string) $er);
            }
            $this->response->redirect('/vendor/booking/add/2?ack=bk&_t=' . base64_encode($bookingData['tour_id']));
        } else {
            $this->response->redirect('/vendor/booking/paymentReturn?paymentId=' . ($paymentOption == 'credit_card' ? $payment->getId() : str_replace('.', '', strtoupper(uniqid('PAY-', true)))));
        }
    }

    /**
     * Payment Return Action.
     * This action for create new booking
     */
    public function paymentReturnAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        $bookingData = $this->session->get('booking_data');
        $reditectTo = '/vendor/booking';

        $paymentId = $this->request->getQuery('paymentId');
        $token = $this->request->getQuery('token');
        $payerId = $this->request->getQuery('PayerID');
        $transactionId = strtoupper(uniqid());

        $paymentOption = $bookingData['payment_options'];
        $error = [];

        if ($paymentOption == 'credit_card') {
            $paypal = new \Tourpage\Library\Paypal();
            $apiContext = $paypal->connect();

            try {
                $paymentDetails = \PayPal\Api\Payment::get($paymentId, $apiContext);
                $transactions = $paymentDetails->getTransactions();
                $relatedResources = $transactions[0]->getRelatedResources();
                $transactionId = $relatedResources[0]->getSale()->getId();
            } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                $error[] = print_r(json_decode($ex->getData()));
            } catch (\Exception $ex) {
                $error[] = $ex->getMessage();
            }
        }

        if (count($error) > 0) {
            foreach ($error as $er) {
                $this->flash->error((string) $er);
            }
            $reditectTo = '/vendor/booking/add/2?ack=bk&_t=' . base64_encode($bookingData['tour_id']);
        } else {
            $memberId = 0;
            if ($bookingData['customer_info']['type'] == 'n') {
                $member = new \Tourpage\Models\Members();
                $member->firstName = $bookingData['customer_info']['first_name'];
                $member->lastName = $bookingData['customer_info']['last_name'];
                $member->nickName = $bookingData['customer_info']['first_name'] . time();
                $member->emailAddress = $bookingData['customer_info']['email_address'];
                $member->passWord = \Tourpage\Helpers\Utils::encryptPassword('tourpage');
                $member->phone = $bookingData['customer_info']['phone'];
                $member->addressOne = $bookingData['customer_info']['address_1'];
                $member->addressTwo = $bookingData['customer_info']['address_2'];
                $member->city = $bookingData['customer_info']['city'];
                $member->stateId = $bookingData['customer_info']['state'];
                $member->countryId = $bookingData['customer_info']['country'];
                $member->zipCode = $bookingData['customer_info']['zip_code'];
                $member->status = \Tourpage\Models\Members::ACTIVE_STATUS_CODE;
                $member->createdOn = \Tourpage\Helpers\Utils::currentDate();
                if ($member->save()) {
                    $memberId = $member->memberId;
                } else {
                    foreach ($member->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
            if ($bookingData['customer_info']['type'] == 'e') {
                $memberId = $bookingData['customer_info']['id'];
                $member = \Tourpage\Models\Members::findFirst($memberId);
            }

            if ($memberId > 0) {
                $bookingPaymentStatus = \Tourpage\Models\Booking::PAYMENT_PAID_STATUS_CODE;
                if ($paymentOption == 'bank_transfer') {
                    $bookingPaymentStatus = \Tourpage\Models\Booking::PAYMENT_PENDING_STATUS_CODE;
                }
                $booking = new \Tourpage\Models\Booking();
                $booking->transactionId = $transactionId;
                $booking->paymentId = $paymentId;
                $booking->token = $token;
                $booking->payerId = $payerId;
                $booking->paymentOption = $paymentOption;
                $booking->memberId = $memberId;
                if ($member->isAgent()) {
                    $booking->agentId = $member->memberId;
                }
                $booking->bookingAmount = $bookingData['final_amount'];
                $booking->bookedOn = \Tourpage\Helpers\Utils::currentDate();
                $booking->bookingStatus = \Tourpage\Models\Booking::COMPLETE_STATUS_CODE;
                $booking->paymentStatus = $bookingPaymentStatus;
                if ($booking->save()) {
                    $invoiceNumber = 'TPINV/' . date('Y/m/d/', strtotime($booking->bookedOn)) . date('H/i/', time()) . $booking->bookingId;
                    $booking->invoiceNumber = $invoiceNumber;
                    $booking->save();
                    $headCount = 0;
                    $tourData = [];
                    if (isset($bookingData['head_count']) && count($bookingData['head_count']) > 0) {
                        foreach ($bookingData['head_count'] as $hc) {
                            $headCount += $hc['count'];
                        }
                        $tourData['head_count'] = $bookingData['head_count'];
                    }
                    if (isset($bookingData['group_head_count'])) {
                        $headCount += $bookingData['group_head_count'];
                        $tourData['group_head_count'] = $bookingData['group_head_count'];
                    }
                    if (isset($bookingData['tour_opt']) && count($bookingData['tour_opt']) > 0) {
                        $tourData['tour_opt'] = $bookingData['tour_opt'];
                    }
                    if (isset($bookingData['time_slot'])) {
                        $tourData['time_slot'] = $bookingData['time_slot'];
                    }
                    if (isset($bookingData['multi_purches_discount'])) {
                        $tourData['multi_purches_discount'] = $bookingData['multi_purches_discount'];
                        $tourData['multi_purches_save_amount'] = $bookingData['multi_purches_save_amount'];
                    }
                    $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();

                    $bookingTour = new \Tourpage\Models\BookingTours();
                    $bookingTour->bookingId = $booking->bookingId;
                    $bookingTour->tourId = $bookingData['tour_id'];
                    $bookingTour->vendorId = $vendorId;
                    if (!$this->vendors->getVendorData()->isParent()) {
                        $bookingTour->employeeId = $this->vendors->getId();
                    }
                    $bookingTour->originalAmount = $bookingData['original_amount'];
                    $bookingTour->finalAmount = $bookingData['final_amount'];
                    $bookingTour->discount = isset($bookingData['discount']) ? $bookingData['discount'] : 0;
                    $bookingTour->tourData = serialize($tourData);
                    $bookingTour->departureOn = $bookingData['departure_on'];
                    $bookingTour->arivalOn = isset($bookingData['arival_on']) ? $bookingData['arival_on'] : NULL;
                    $bookingTour->headCount = $headCount;
                    $bookingTour->tourConductStatus = \Tourpage\Models\BookingTours::TOUR_CONDUCT_PENDING_STATUS_CODE;
                    if (!$bookingTour->save()) {
                        foreach ($bookingTour->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }

                    $customerInfo = $bookingData['customer_info'];
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
                    if (!$bookingAddress->save()) {
                        foreach ($bookingAddress->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }

                    //Sending Mail Confirmation To Customer About Booking Confirmation
                    $mail = new \Tourpage\Library\Mail();
                    $mail->setTo($member->getEmailAddress(), $member->getFirstName() . ' ' . $member->getLastName());
                    $mail->setSubject('Tourpage booking confirmation');
                    $mail->setBody($this->getTemplateForBooking($booking));
                    $mail->send();

                    //Sending Mail Notification To Vendor About Booking Recieved
                    $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
                    if ($vendor && $vendor->count() > 0) {
                        $memberToVendor = \Tourpage\Models\MembersToVendor::findFirst(array(
                                    'conditions' => 'memberId = :member_id: AND vendorId = :vendor_id:',
                                    'bind' => array(
                                        'member_id' => $memberId,
                                        'vendor_id' => $vendor->vendorId
                                    )
                        ));
                        if (!$memberToVendor) {
                            $memberToVendorCreate = new \Tourpage\Models\MembersToVendor();
                            $memberToVendorCreate->memberId = $memberId;
                            $memberToVendorCreate->vendorId = $vendor->vendorId;
                            $memberToVendorCreate->save();
                        }

                        $mail = new \Tourpage\Library\Mail();
                        $mail->setTo($vendor->emailAddress, $vendor->firstName . ' ' . $vendor->lastName);
                        $mail->setSubject('Tourpage booking recived - Vendor');
                        $mail->setBody($this->getTemplateForBooking($booking, 'vendor_booking', $vendorId));
                        $mail->send();
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
                    $this->session->remove('booking_data');
                    $this->flash->success("Order has been placed successfully.");
                    if ($this->vendors->isAllowed('booking', 'details')) {
                        $reditectTo = '/vendor/booking/details/' . $booking->bookingId;
                    }
                } else {
                    foreach ($booking->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
            $this->response->redirect($reditectTo);
        }
    }

    /**
     * Getting Mailing Template
     * @param \Tourpage\Models\Booking $booking
     * @param string $template
     * @param int $vendorId
     * @return string
     */
    private function getTemplateForBooking(\Tourpage\Models\Booking $booking, $template = 'customer_booking', $vendorId = 0) {
        $emailTemplate = clone $this->view;
        $emailTemplate->start();
        $emailTemplate->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $emailTemplate->booking = $booking;
        if ($vendorId > 0) {
            $emailTemplate->vendorId = $vendorId;
        }
        $bookingData = $this->session->get('booking_data');
        $emailTemplate->bookingData = $bookingData;
        $emailTemplate->render('booking', 'mail/' . $template);
        $emailTemplate->finish();
        $content = $emailTemplate->getContent();
        return $content;
    }

}
