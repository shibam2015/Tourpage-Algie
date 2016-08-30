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
 * Controller for ajax request handling
 * @author amit
 */
class AjaxController extends FrontendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
    }

    public function contactVendorAction($vendorId) {
        $response = [];
        if ($this->request->isAjax()) {
            $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
            if ($vendor && $vendor->count() > 0) {
                $message = '';
                $personName = $this->request->getPost('person_name', array('string', 'striptags'));
                $personEmail = $this->request->getPost('person_email', 'email');
                $personSubject = $this->request->getPost('person_subject', array('string', 'striptags'));
                $personMessage = $this->request->getPost('person_message', array('string', 'striptags'));
                $message .= '<h4>Message from ' . $vendor->businessName . '</h4><br />';
                $message .= '<strong>Send By:</strong> ' . $personName . ' [ ' . $personEmail . ' ]<br />';
                $message .= '<strong>Message:</strong><br />';
                $message .= $personMessage;
                $mail = new \Tourpage\Library\Mail();
                $mail->setTo($vendor->emailAddress, $vendor->firstName . ' ' . $vendor->lastName);
                $mail->setFrom($personEmail, $personName);
                $mail->setSubject($personSubject);
                $mail->setBody($message);
                $mail->send();
                $response['success'] = TRUE;
                $response['message'] = 'message send';
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }
	public function updateGroupAction($vendorId) {
        $response = [];
        if ($this->request->isAjax()) {
            $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
            if ($vendor && $vendor->count() > 0) {
                $message = '';
                $personName = $this->request->getPost('person_name', array('string', 'striptags'));
                $personEmail = $this->request->getPost('person_email', 'email');
                $personSubject = $this->request->getPost('person_subject', array('string', 'striptags'));
                $personMessage = $this->request->getPost('person_message', array('string', 'striptags'));
                $message .= '<h4>Message from ' . $vendor->businessName . '</h4><br />';
                $message .= '<strong>Send By:</strong> ' . $personName . ' [ ' . $personEmail . ' ]<br />';
                $message .= '<strong>Message:</strong><br />';
                $message .= $personMessage;
                $mail = new \Tourpage\Library\Mail();
                $mail->setTo($vendor->emailAddress, $vendor->firstName . ' ' . $vendor->lastName);
                $mail->setFrom($personEmail, $personName);
                $mail->setSubject($personSubject);
                $mail->setBody($message);
                $mail->send();
                $response['success'] = TRUE;
                $response['message'] = 'message send';
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function renderStateAction($countryId) {
        $response = [];
        if ($this->request->isAjax()) {
            $states = \Tourpage\Models\State::find(array(
                        "countryId = :country_id: AND status = :state_status:",
                        "bind" => array(
                            ":country_id" => $countryId,
                            ":state_status" => \Tourpage\Models\State::ACTIVE_STATUS_CODE
                        )
            ));
            if ($states && $states->count() > 0) {
                foreach ($states as $state) {
                    $response['states'][] = array(
                        'stateId' => $state->stateId,
                        'stateName' => $state->name
                    );
                }
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }
	public function renderGroupToursAction($vendorId) {
        $response = [];
        if ($this->request->isAjax()) {
			$groupId= $this->request->getPost('groupId');
            $tours = \Tourpage\Models\GroupsTours::find(array(
                        "vendorId = :vendor_id: AND groupId = :group_id: AND groupMapOrder > :group_map_order:",
                        "bind" => array(
                            ":vendor_id" => $vendorId,
                            ":group_id" => $groupId,
							"group_map_order" => 0
                        )
            ));
            if ($tours && $tours->count() > 0) {
                foreach ($tours as $tour) {
                    $response['grpTours'][] = array(
                        'tourId' => $tour->tour->tourId,
                        'tourTitle' => $tour->tour->tourTitle
                    );
                }
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }
	
	public function renderAttractionsAction($stateId) {
        $response = [];
        if ($this->request->isAjax()) {
            $attractions = \Tourpage\Models\PlaceOfAttractions::find(array(
                        "stateId = :state_id: AND status = :attractions_status:",
                        "bind" => array(
                            ":state_id" => $stateId,
                            ":attractions_status" => \Tourpage\Models\PlaceOfAttractions::ACTIVE_STATUS_CODE
                        )
            ));
            if ($attractions && $attractions->count() > 0) {
                foreach ($attractions as $attraction) {
                    $response['attractions'][] = array(
                        'attractionId' => $attraction->attractionId,
                        'attractionName' => $attraction->attractionName
                    );
                }
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function renderCityAction($stateId, $countryId) {
        $response = [];
        if ($this->request->isAjax()) {
            $cities = \Tourpage\Models\City::find(array(
                        "countryId = :country_id: AND stateId = :state_id: AND status = :city_status:",
                        "bind" => array(
                            ":country_id" => $countryId,
                            ":state_id" => $stateId,
                            ":city_status" => \Tourpage\Models\City::ACTIVE_STATUS_CODE
                        )
            ));
            if ($cities && $cities->count() > 0) {
                foreach ($cities as $city) {
                    $response['cities'][] = array(
                        'cityId' => $city->cityId,
                        'cityName' => $city->name
                    );
                }
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function sendFileAction() {
        $response = [];
        if ($this->request->isAjax()) {
            $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/tmp';
            if ($this->request->hasFiles(TRUE)) {
                $filesData = $this->request->getUploadedFiles(TRUE);
                $file = $filesData[0];
                $tourImage = new \Tourpage\Models\ToursImages();
                if ($tourImage->allowImageType($file)) {
                    $imageName = time() . md5($file->getName() . rand(0, 1000)) . '.' . $file->getExtension();
                    if ($file->moveTo($baseLocation . '/' . $imageName)) {
                        $imageFile = new \Phalcon\Image\Adapter\GD($baseLocation . '/' . $imageName);
                        $response['file']['name'] = $imageName;
                        $response['file']['type'] = $file->getType();
                        $response['file']['ext'] = $file->getExtension();
                        $response['file']['size'] = $file->getSize();
                        $response['file']['width'] = $imageFile->getWidth();
                        $response['file']['height'] = $imageFile->getHeight();
                        $response['file']['upload_path'] = $baseLocation;
                        $thumbFile = new \Phalcon\Image\Adapter\GD($baseLocation . '/' . $imageName);
                        $thumbFile->resize(300, 300);
                        if ($thumbFile->save($baseLocation . '/' . 'thumb' . $imageName)) {
                            $response['file']['thumb']['name'] = 'thumb' . $imageName;
                            $response['file']['thumb']['width'] = $thumbFile->getWidth();
                            $response['file']['thumb']['height'] = $thumbFile->getHeight();
                        }
                    } else {
                        $response['file']['error'] = $file->getError();
                    }
                }
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function socialLoginAction() {
        $response = [];
        if ($this->request->isAjax()) {
            $emailAddress = $this->request->getPost('email');
            $nameString = $this->request->getPost('name');
            $targetUri = $this->request->getPost('target_uri');
            $baseUri = $this->request->getPost('base_uri');
            $loginUri = $this->request->getPost('login_uri');
            $registerUri = $this->request->getPost('register_uri');
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
                'email_address' => $emailAddress
            );
            if ($emailAddress) {
                $member = \Tourpage\Models\Members::findFirst(array(
                            'conditions' => 'emailAddress = :email:',
                            'bind' => array('email' => $emailAddress)
                ));
                if (!$member) {
                    $this->flash->notice('Please complete your registration by filling up all require fields.');
                    $response['redirect_uri'] = $registerUri . '?ffd=' . base64_encode(serialize($formField));
                } else {
                    if ($member->status == \Tourpage\Models\Members::ACTIVE_STATUS_CODE) {
                        $this->session->set('member', $member);
                        $response['redirect_uri'] = !empty($targetUri) ? $targetUri : $baseUri;
                    } else {
                        $this->flash->notice('Account is Inactive. Please active your account or contact to Administrator.');
                        $response['redirect_uri'] = $loginUri;
                    }
                }
            } else {
                $this->flash->notice('Please complete your registration by filling up all require fields.');
                $response['redirect_uri'] = $registerUri . '?ffd=' . base64_encode(serialize($formField));
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function checkTourAvailibilityAction($tourId) {
        $response = [];
        $availableForBooking = FALSE;
        $message = '';
        if ($this->request->isAjax()) {
            $tourLengthType = $this->request->getPost('tour_length_type');
            $tourPriceType = $this->request->getPost('tour_price_type');
            $headCount = $this->request->getPost('head_count');
            $selectedDate = $this->request->getPost('selected_date');
            $startDate = \Tourpage\Helpers\Utils::formatDatepickerToMySql($selectedDate['std']);
            if (isset($selectedDate['edd'])) {
                $endDate = \Tourpage\Helpers\Utils::formatDatepickerToMySql($selectedDate['edd']);
            }
            $headCountUnit = 'person(s)';
            if ($tourPriceType == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE) {
                $headCountUnit = ' group(s)';
            }
            $modelBind = $tourTimeSlots = [];
            $tourModel = \Tourpage\Models\Tours::findFirstByTourId($tourId);
            /*if ($tourLengthType == \Tourpage\Models\Tours::LENGTH_HOURLY) {
                foreach ($tourModel->tourDuration->times as $time) {
                    $key = $time->start->hours . $time->start->minutes . $time->end->hours . $time->end->minutes;
                    $tourTimeSlots[$key] = $time;
                }
            }*/
            if ($tourModel->tourCapacity > 0) {
                if ($tourModel->tourCapacity >= $headCount) {
                    $bookingTour = \Tourpage\Models\BookingTours::query();
                    $bookingTour->where('tourId = :tourId:');
                    $bookingTour->andWhere('departureOn = :startDate:');
                    $modelBind['tourId'] = $tourId;
                    $modelBind['startDate'] = $startDate;
                    if (isset($endDate)) {
                        $bookingTour->andWhere('arivalOn = :endDate:');
                        $modelBind['endDate'] = $endDate;
                    }
                    $bookingTour->bind($modelBind);
                    $bookingRecords = $bookingTour->execute();
                    $occupiedCapacity = $remainCapacity = 0;
                    if ($bookingRecords->count() > 0) {
                        if ($tourLengthType == \Tourpage\Models\Tours::LENGTH_HOURLY) {
                            $tourTimeSlots = [];
                            foreach ($tourModel->tourDuration->times as $time) {
                                $key = $time->start->hours . $time->start->minutes . $time->end->hours . $time->end->minutes;
                                $tourTimeSlots[$key] = $time;
                            }
                            $bookedSlots = [];
                            foreach ($bookingRecords as $record) {
                                $timeSlotKey = $record->data->timeSlot->start->hours . $record->data->timeSlot->start->minutes . $record->data->timeSlot->end->hours . $record->data->timeSlot->end->minutes;
                                if (!isset($bookedSlots[$timeSlotKey])) {
                                    $bookedSlots[$timeSlotKey] = 0;
                                }
                                $bookedSlots[$timeSlotKey] += $record->headCount;
                            }
                            foreach ($bookedSlots as $slotKey => $slotCapacity) {
                                $remainCapacity = $tourModel->tourCapacity - $slotCapacity;
                                if ($remainCapacity < 0) {
                                    $remainCapacity = 0;
                                }
                                if ($remainCapacity > 0) {
                                    if ($remainCapacity < $headCount) {
                                        if (isset($tourTimeSlots[$slotKey])) {
                                            unset($tourTimeSlots[$slotKey]);
                                        }
                                    }
                                } else {
                                    if (isset($tourTimeSlots[$slotKey])) {
                                        unset($tourTimeSlots[$slotKey]);
                                    }
                                }
                            }
                            if (count($tourTimeSlots) > 0) {
                                $availableForBooking = TRUE;
                            } else {
                                $message .= 'Booking not available for ' . $selectedDate['std'];
                            }
                        } else {
                            foreach ($bookingRecords as $record) {
                                $occupiedCapacity += $record->headCount;
                            }
                            $remainCapacity = $tourModel->tourCapacity - $occupiedCapacity;
                            if ($remainCapacity < 0) {
                                $remainCapacity = 0;
                            }
                            if ($remainCapacity > 0) {
                                if ($remainCapacity >= $headCount) {
                                    $availableForBooking = TRUE;
                                } else {
                                    $message .= 'Booking not available for ' . $headCount . ' ' . $headCountUnit . '.';
                                    $message .= ' Remaining capacity for maximum ' . $remainCapacity . $headCountUnit;
                                }
                            } else {
                                $message .= 'Booking not available for ' . $selectedDate['std'];
                                if (isset($selectedDate['edd'])) {
                                    $message .= ' to ' . $selectedDate['edd'];
                                }
                            }
                        }
                    } else {
                        if ($tourModel->tourCapacity >= $headCount) {
                            $availableForBooking = TRUE;
                        } else {
                            $message .= 'Booking not available for ' . $headCount . ' ' . $headCountUnit . '.';
                            $message .= ' Remaining capacity for maximum ' . $tourModel->tourCapacity . ' ' . $headCountUnit;
                        }
                    }
                } else {
                    $message .= 'Booking not available for ' . $headCount . ' ' . $headCountUnit . '.';
                    $message .= ' Remaining capacity for maximum ' . $tourModel->tourCapacity . ' ' . $headCountUnit;
                }
            } else {
                if ($tourModel->tourCapacity < 0) {
                    $availableForBooking = TRUE;
                } else {
                    $message .= 'Booking not available as capacity has been reached.';
                }
            }
            if ($availableForBooking) {
                if ($tourLengthType == \Tourpage\Models\Tours::LENGTH_HOURLY) {
                    if (count($tourTimeSlots) > 0) {
                        $timeOptions = '';
                        $sl = 1;
                        $timeOptions .= '<ul class="list-unstyled">';
                        foreach ($tourTimeSlots as $timeSlot) {
                            $timeOptions .= '<li>';
                            $timeOptions .= '<label style="width: 100%">';
                            $timeOptions .= '<input type="radio" name="tour_time_slot" value="' . base64_encode(serialize($timeSlot)) . '"' . ($sl == 1 ? ' checked="checked"' : '') . '> ';
                            $timeOptions .= \Tourpage\Helpers\Utils::padInt($timeSlot->start->hours) . ':' . \Tourpage\Helpers\Utils::padInt($timeSlot->start->minutes) . ' ' . ($timeSlot->start->hours >= 12 ? 'PM' : 'AM');
                            $timeOptions .= ' To ' . \Tourpage\Helpers\Utils::padInt($timeSlot->end->hours) . ':' . \Tourpage\Helpers\Utils::padInt($timeSlot->end->minutes) . ' ' . ($timeSlot->end->hours >= 12 ? 'PM' : 'AM');
                            $timeOptions .= '</label>';
                            $timeOptions .= '</li>';
                            $sl++;
                        }
                        $timeOptions .= '</ul>';
                        $response['timeOptions'] = $timeOptions;
                    }
                }
            }
        }
        $response['availableForBooking'] = $availableForBooking;
        $response['message'] = $message;
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function customerReviewAction() {
        $response = [];
        if ($this->request->isAjax()) {
            $customerName = $this->request->getPost('customer_name');
            $customerEmail = $this->request->getPost('customer_email');
            $customerReview = $this->request->getPost('customer_review');
            $ratingSet = $this->request->getPost('rating_set');
            $memberId = $this->request->getPost('member_id');
            $reviewOn = $this->request->getPost('review_on');
            if (isset($reviewOn['type'])) {
                switch ($reviewOn['type']) {
                    case 'tour':
                        $tourId = $reviewOn['id'];
                        $vendorId = $reviewOn['vid'];
                        $memberLoggin = FALSE;
                        if ($memberId > 0) {
                            $memberLoggin = TRUE;
                        }
                        $review = new \Tourpage\Models\ToursReview();
                        $review->tourId = $tourId;
                        $review->vendorId = $vendorId;
                        $review->reviewByName = \Tourpage\Helpers\Utils::encodeString($customerName);
                        $review->reviewByEmail = $customerEmail;
                        $review->reviewContent = \Tourpage\Helpers\Utils::encodeString($customerReview);
                        $review->isMember = $memberLoggin ? \Tourpage\Models\ToursReview::REVIEWER_IS_MEMBER_STATUS_CODE : \Tourpage\Models\ToursReview::REVIEWER_IS_NOT_MEMBER_STATUS_CODE;
                        $review->starCount = $ratingSet;
                        $review->reviewStatus = \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE;
                        $review->reviewOn = \Tourpage\Helpers\Utils::currentDate();
                        if ($review->save()) {
                            $rating = \Tourpage\Models\ToursRating::findFirstByTourId($tourId);
                            if ($rating && $rating->count() > 0) {
                                if ($ratingSet > 0) {
                                    $rating->{'star_' . $ratingSet} = $rating->{'star_' . $ratingSet} + 1;
                                    $rating->save();
                                }
                            } else {
                                $rating = new \Tourpage\Models\ToursRating();
                                $rating->tourId = $tourId;
                                for ($star = 1; $star <= 5; $star++) {
                                    $starCount = 0;
                                    if ($star == $ratingSet) {
                                        $starCount = 1;
                                    }
                                    $rating->{'star_' . $star} = $starCount;
                                }
                                $rating->save();
                            }

                            if ($memberLoggin) {
                                $memberReview = new \Tourpage\Models\MembersTourReview();
                                $memberReview->memberId = $memberId;
                                $memberReview->tourId = $tourId;
                                $memberReview->reviewId = $review->reviewId;
                                $memberReview->save();
                            }

                            $response['success'] = TRUE;
                            $response['message'] = 'Your review has been successfully posted';
                        }
                        break;
                    case 'vendor':
                        $vendorId = $reviewOn['id'];
                        break;
                }
            }
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }

    public function processPreviewAction() {
        $response = [];
        if ($this->request->isAjax()) {
            $formPostData = $this->request->getPost();
            if (isset($formPostData['vendor']) && !empty($formPostData['vendor'])) {
                $vendorId = $formPostData['vendor'];
                $venfor = \Tourpage\Models\Vendors::findFirst($vendorId);
                $formPostData['vendor'] = $venfor;
            }
            if (isset($formPostData['tour_key_highlight']) && count($formPostData['tour_key_highlight']) > 0) {
                $keyHighlight = $formPostData['tour_key_highlight'];
                $formPostData['tour_key_highlight'] = [];
                foreach ($keyHighlight as $highlightId) {
                    $modelHighlight = \Tourpage\Models\KeyHighlight::findFirst($highlightId);
                    $formPostData['tour_key_highlight'][$highlightId] = $modelHighlight;
                }
            }
            if (isset($formPostData['tour_details']) && count($formPostData['tour_details']) > 0) {
                $attributes = $formPostData['tour_details'];
                $formPostData['tour_details'] = [];
                foreach ($attributes as $attributeId => $attributeContent) {
                    $attributeKey = \Tourpage\Models\ToursAttributeKeys::findFirst($attributeId);
                    $formPostData['tour_details'][$attributeId] = [
                        'name' => $attributeKey->keyName,
                        'content' => \Tourpage\Helpers\Utils::decodeString($attributeContent)
                    ];
                }
            }
            if (!isset($formPostData['tour_price_type_text'])) {
                $formPostData['tour_price_type_text'] = \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_TEXT;
            }
            if (isset($formPostData['tour_price_type'])) {
                $priceType = $formPostData['tour_price_type'];
                if ($priceType == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE) {
                    $formPostData['tour_price_type_text'] = \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_TEXT;
                }
            }
            $previewTemplate = clone $this->view;
            $previewTemplate->start();
            $previewTemplate->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            $previewTemplate->formPostData = $formPostData;
            $previewTemplate->render('tour', 'preview');
            $previewTemplate->finish();
            $response['content'] = $previewTemplate->getContent();
            $previewTemplate->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        }
        $this->response->setJsonContent($response);
        $this->response->send();
    }
}
