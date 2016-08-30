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
 * AccountController
 * Controller for Member Accounts
 * @author amit
 */
class AccountController extends FrontendController {

    /**
     * Index Action for Member Account
     */
    public function indexAction() {
        $member = $this->member->refresh();
        //$accountForm = new \Multiple\Frontend\Forms\MemberRegistrationForm($member, array('edit' => true));
        $accountForm = new \Multiple\Frontend\Forms\MemberProfileForm($member, array('edit' => true));
        $avatarForm = new \Tourpage\Forms\CFilesForm(NULL, array(
            'name' => 'avtr',
            'allowEmpty' => true,
            'maxResolution' => 0,
            'maxSize' => 0
        ));
        if ($this->request->isPost()) {
            $valid = $accountForm->isValid($this->request->getPost());
            $valid = $valid && $avatarForm->isValid($_FILES);
            if ($valid) {
                $member->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                $member->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                $member->nickName = $this->request->getPost('nick_name', array('string', 'striptags'));
                //$member->emailAddress = $this->request->getPost('email_address', 'email');
                $member->phone = $this->request->getPost('phone', array('string', 'striptags'));
                $member->addressOne = $this->request->getPost('address_1', array('string', 'striptags'));
                $member->addressTwo = $this->request->getPost('address_2', array('string', 'striptags'));
                $member->city = $this->request->getPost('city', array('string', 'striptags'));
                $member->stateId = $this->request->getPost('state');
                $member->countryId = $this->request->getPost('country');
                $member->zipCode = $this->request->getPost('zip_code');
                if ($member->save()) {
                    if ($this->request->hasFiles(TRUE)) {
                        $filesData = $this->request->getUploadedFiles(TRUE);
                        $file = $filesData[0];
                        $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/avtr/' . md5($member->memberId) . '/';
                        if (!file_exists($baseLocation)) {
                            mkdir($baseLocation);
                            chmod($baseLocation, 0777);
                        }
                        $imageName = time() . md5($file->getName() . rand(0, 1000)) . '.' . $file->getExtension();
                        if ($file->moveTo($baseLocation . $imageName)) {
                            foreach (\Tourpage\Models\Members::avatarSizes() as $avatarSize) {
                                list($resizeWidth, $resizeHeight) = explode('x', $avatarSize);
                                if (!empty($member->avatar)) {
                                    if (file_exists($baseLocation . $avatarSize . $member->avatar)) {
                                        unlink($baseLocation . $avatarSize . $member->avatar);
                                    }
                                }
                                $resizeFile = new \Phalcon\Image\Adapter\GD($baseLocation . $imageName);
                                $resizeFile->resize($resizeWidth, $resizeHeight);
                                $resizeFile->save($baseLocation . $avatarSize . $imageName);
                            }
                            if (!empty($member->avatar)) {
                                if (file_exists($baseLocation . $member->avatar)) {
                                    unlink($baseLocation . $member->avatar);
                                }
                            }
                            $member->avatar = $imageName;
                            $member->save();
                            $this->flash->success('Profile has been saved successfully');
                            $this->member->refresh();
                        } else {
                            $this->flash->error((string) $file->getError());
                        }
                    } else {
                        $this->flash->success('Profile has been saved successfully');
                    }
                } else {
                    foreach ($member->getMessages() as $memberMessage) {
                        $this->flash->error((string) $memberMessage);
                    }
                }
                $this->response->redirect($this->router->getRewriteUri());
            }
        }
		$this->view->memberMessages = \Tourpage\Models\MembersMessages::findFirst(array(
                    'conditions' => 'memberId = :member_id: AND memberMessageStatus = :status:',
                    'bind' => array('member_id' => $member->memberId, 'status' => \Tourpage\Models\MembersMessages::UNREAD_STATUS_CODE)
        ));
        $this->tag->setTitle('Profile');
        $this->view->form = $accountForm;
        $this->view->avatar = $avatarForm;
    }

    /**
     * Action for Members Payment Options
     * This action is for to give ability to member
     * to manage their credit/debid cards, which they can
     * use during booking payment
     */
    public function paymentOptionsAction() {
        if ($this->request->isPost()) {
            $formType = $this->request->getPost('form_type');
            if ($formType == 'new_card') {
                $cardType = $this->request->getPost('card_type');
                $cardNumber = $this->request->getPost('card_number');
                $cardName = $this->request->getPost('card_name');
                $cardCvv = $this->request->getPost('card_cvv');
                $expiryMonth = $this->request->getPost('card_exp_month');
                $expiryYear = $this->request->getPost('card_exp_year');
                $firstName = $this->request->getPost('first_name');
                $lastName = $this->request->getPost('last_name');
                $addressOne = $this->request->getPost('address_1');
                $addressTwo = $this->request->getPost('address_2');
                $phone = $this->request->getPost('phone');
                $country = $this->request->getPost('country');
                $state = $this->request->getPost('state');
                $city = $this->request->getPost('city');
                $zipCode = $this->request->getPost('zip');

                $memberCard = new \Tourpage\Models\MembersCards();
                $memberCard->memberId = $this->member->getId();
                $memberCard->firstName = $firstName;
                $memberCard->lastName = $lastName;
                $memberCard->addressOne = $addressOne;
                $memberCard->addressTwo = $addressTwo;
                $memberCard->phone = $phone;
                $memberCard->countryId = $country;
                $memberCard->stateId = $state;
                $memberCard->city = $city;
                $memberCard->zipCode = $zipCode;
                $memberCard->cardType = $cardType;
                $memberCard->cardNumber = $cardNumber;
                $memberCard->cardName = $cardName;
                $memberCard->cardCvv = $cardCvv;
                $memberCard->expiryMonth = $expiryMonth;
                $memberCard->expiryYear = $expiryYear;
                $memberCard->status = \Tourpage\Models\MembersCards::ACTIVE_STATUS_CODE;
                $memberCard->addedOn = \Tourpage\Helpers\Utils::currentDate();
                if ($memberCard->save()) {
                    $this->flash->success('Credit/Debid Card has been saved successfully');
                } else {
                    foreach ($memberCard->getMessages() as $memberMessage) {
                        $this->flash->error((string) $memberMessage);
                    }
                }
            }
            if ($formType == 'saved_card') {
                $cards = $this->request->getPost('card');
                if ($cards && count($cards) > 0) {
                    $savedCardCount = $skipCardCount = 0;
                    foreach ($cards as $cardId => $cardData) {
                        $cardType = $cardData['card_type'];
                        $cardNumber = $cardData['card_number'];
                        $cardName = $cardData['card_name'];
                        $cardCvv = $cardData['card_cvv'];
                        $expiryMonth = $cardData['card_exp_month'];
                        $expiryYear = $cardData['card_exp_year'];
                        $firstName = $cardData['first_name'];
                        $lastName = $cardData['last_name'];
                        $addressOne = $cardData['address_1'];
                        $addressTwo = $cardData['address_2'];
                        $phone = $cardData['phone'];
                        $country = $cardData['country'];
                        $state = $cardData['state'];
                        $city = $cardData['city'];
                        $zipCode = $cardData['zip'];

                        $memberCard = \Tourpage\Models\MembersCards::findFirst($cardId);
                        $memberCard->firstName = $firstName;
                        $memberCard->lastName = $lastName;
                        $memberCard->addressOne = $addressOne;
                        $memberCard->addressTwo = $addressTwo;
                        $memberCard->phone = $phone;
                        $memberCard->countryId = $country;
                        $memberCard->stateId = $state;
                        $memberCard->city = $city;
                        $memberCard->zipCode = $zipCode;
                        $memberCard->cardType = $cardType;
                        $memberCard->cardNumber = $cardNumber;
                        $memberCard->cardName = $cardName;
                        $memberCard->cardCvv = $cardCvv;
                        $memberCard->expiryMonth = $expiryMonth;
                        $memberCard->expiryYear = $expiryYear;
                        if (!$memberCard->save()) {
                            $skipCardCount++;
                            foreach ($memberCard->getMessages() as $memberMessage) {
                                $this->flash->error((string) $cardData['card_number'] . ' : ' . $memberMessage);
                            }
                        } else {
                            $savedCardCount++;
                        }
                    }
                    if ($skipCardCount == 0) {
                        if ($savedCardCount > 0) {
                            $this->flash->success('Credit/Debid Card has been saved successfully');
                        }
                    }
                }
            }
            $this->response->redirect($this->router->getRewriteUri());
        }
        $memberData = $this->member->getResource();
        \Phalcon\Tag::setDefaults(array(
            'card_exp_month' => \Tourpage\Helpers\Utils::__getCurrentMonth(),
            'card_exp_year' => \Tourpage\Helpers\Utils::__getCurrentYear(),
            'first_name' => $memberData->firstName,
            'last_name' => $memberData->lastName,
            'address_1' => $memberData->addressOne,
            'address_2' => $memberData->addressTwo,
            'phone' => $memberData->phone,
            'country' => $memberData->countryId,
            'state' => $memberData->stateId,
            'city' => $memberData->city,
            'zip' => $memberData->zipCode,
        ));

        $memberCards = \Tourpage\Models\MembersCards::find(array(
                    'conditions' => 'memberId = :member_id:',
                    'bind' => array('member_id' => $this->member->getId())
        ));
        $this->tag->setTitle('Payment Options');
        $this->view->memberCards = $memberCards;
    }

    /**
     * Action for remove members saved credit/debid card
     * @param int $cardId
     * @return boolean
     */
    public function removeCardAction($cardId = 0) {
        if ($cardId <= 0) {
            return FALSE;
        }
        if (!preg_match_all('/[0-9]+/', $cardId, $matches)) {
            return false;
        }
        $memberCard = \Tourpage\Models\MembersCards::findFirst($cardId);
        if (!$memberCard) {
            return FALSE;
        }
        if ($memberCard->removeData()) {
            $this->flash->success('Card No. ' . $memberCard->cardNumber . ' has been removed successfully');
        }
        $this->response->redirect('/account/paymentoptions');
    }

    /**
     * Members Travel Preferance
     * @param string $preference
     */
    public function travelpreferenceAction($preference = 'perfectday') {
        $member = $this->member->getResource();
        switch ($preference) {
            case 'perfectday':
                $this->tag->setTitle('Your Perfect Day');
                break;
            case 'fevactivities':
                $memberActivities = [];
                if ($member->activities && $member->activities->count() > 0) {
                    foreach ($member->activities as $activity) {
                        $memberActivities[] = $activity->activityId;
                    }
                }
                if ($this->request->isPost()) {
                    $activities = $this->request->getPost('actv');
                    $oldActivities = $newActivities = $removedActivities = [];
                    foreach ($activities as $typeOfActivity) {
                        if (in_array($typeOfActivity, $memberActivities)) {
                            $oldActivities[] = $typeOfActivity;
                        } else {
                            $newActivities[] = $typeOfActivity;
                        }
                    }
                    if (count($memberActivities) > 0) {
                        $removedActivities = array_diff($memberActivities, $oldActivities);
                    }
                    if (count($newActivities) > 0) {
                        foreach ($newActivities as $newActivity) {
                            $newMemberAttraction = new \Tourpage\Models\MembersActivities();
                            $newMemberAttraction->memberId = $this->member->getId();
                            $newMemberAttraction->activityId = $newActivity;
                            $newMemberAttraction->save();
                        }
                    }
                    if (count($removedActivities) > 0) {
                        foreach ($removedActivities as $removedActivity) {
                            $removedMemberActivity = \Tourpage\Models\MembersActivities::findFirst(array(
                                        'conditions' => 'activityId = :activity_id:',
                                        'bind' => array(
                                            'activity_id' => $removedActivity
                                        )
                            ));
                            if ($removedMemberActivity && $removedMemberActivity->count() > 0) {
                                $removedMemberActivity->removeData();
                            }
                        }
                    }
                    $this->member->refresh();
                    $this->flash->success('Type of Activity has been saved');
                    $this->response->redirect($this->router->getRewriteUri());
                }
                $activities = \Tourpage\Models\PlaceOfActivities::find(array(
                            'conditions' => 'status = :status:',
                            'bind' => array(
                                'status' => \Tourpage\Models\PlaceOfActivities::ACTIVE_STATUS_CODE
                            )
                ));
                $this->tag->setTitle('Favorite Activities');
                $this->view->memberActivities = $memberActivities;
                $this->view->activities = $activities;
                break;
            case 'fevdestination':
                $memberAttractions = [];
                if ($member->attractions && $member->attractions->count() > 0) {
                    foreach ($member->attractions as $attraction) {
                        $memberAttractions[] = $attraction->attractionId;
                    }
                }
                if ($this->request->isPost()) {
                    $destinations = $this->request->getPost('dest');
                    $oldAttractions = $newAttractions = $removedAttractions = [];
                    foreach ($destinations as $destination) {
                        if (in_array($destination, $memberAttractions)) {
                            $oldAttractions[] = $destination;
                        } else {
                            $newAttractions[] = $destination;
                        }
                    }
                    if (count($memberAttractions) > 0) {
                        $removedAttractions = array_diff($memberAttractions, $oldAttractions);
                    }
                    if (count($newAttractions) > 0) {
                        foreach ($newAttractions as $newAttraction) {
                            $newMemberAttraction = new \Tourpage\Models\MembersAttractions();
                            $newMemberAttraction->memberId = $this->member->getId();
                            $newMemberAttraction->attractionId = $newAttraction;
                            $newMemberAttraction->save();
                        }
                    }
                    if (count($removedAttractions) > 0) {
                        foreach ($removedAttractions as $removedAttraction) {
                            $removedMemberAttraction = \Tourpage\Models\MembersAttractions::findFirst(array(
                                        'conditions' => 'attractionId = :attraction_id:',
                                        'bind' => array(
                                            'attraction_id' => $removedAttraction
                                        )
                            ));
                            if ($removedMemberAttraction && $removedMemberAttraction->count() > 0) {
                                $removedMemberAttraction->removeData();
                            }
                        }
                    }
                    $this->member->refresh();
                    $this->flash->success('Destination has been saved');
                    $this->response->redirect($this->router->getRewriteUri());
                }
                $attractions = \Tourpage\Models\PlaceOfAttractions::find(array(
                            'conditions' => 'status = :status:',
                            'bind' => array(
                                'status' => \Tourpage\Models\PlaceOfAttractions::ACTIVE_STATUS_CODE
                            )
                ));
                $this->tag->setTitle('Favorite Destination');
                $this->view->memberAttractions = $memberAttractions;
                $this->view->attractions = $attractions;
                break;
        }
        $this->view->pick('account/travelpreference/' . $preference);
    }

    /**
     * Member reviews list
     * @param string $type Type of Review Status
     * @param int $page pagination counter
     */
    public function reviewsAction($type = 'confirmed', $page = 1) {
        $modelBind = [];
        $memberReviews = \Tourpage\Models\MembersTourReview::query();
        $memberReviews->where("memberId = :member_id:");
        $modelBind['member_id'] = $this->member->getId();
        $memberReviews->join('\Tourpage\Models\ToursReview', 'tr.reviewId = \Tourpage\Models\MembersTourReview.reviewId', 'tr');
        $memberReviews->andWhere("tr.reviewStatus = :review_status:");
        $modelBind['review_status'] = \Tourpage\Models\ToursReview::INACTIVE_STATUS_CODE;
        if ($type == 'confirmed') {
            $modelBind['review_status'] = \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE;
        }
        if (count($modelBind) > 0) {
            $memberReviews->bind($modelBind);
        }
        $memberReviews->order("memberTourReviewId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $memberReviews->execute(),
            "page" => $page,
        ));
        $pager->setUriPattern('/account/reviews/' . $type . '/{page}');
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->setTitle('Reviews');
        $this->view->reviewType = $type;
        $this->view->pager = $pager;
    }

    public function offersAction() {
        $this->tag->setTitle('Points & Awards');
    }

    /**
     * Members booked tours
     * @param string $type Type of tour Upcomming | Past
     * @param int $page Pagination page counter
     * @return boolean
     */
    public function toursAction($type = 'upcomming', $page = 1) {
        if ($type != 'upcomming' && $type != 'past') {
            return FALSE;
        }
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $modelBind = [];
        $bookings = \Tourpage\Models\BookingTours::query();
        $bookings->rightJoin('\Tourpage\Models\Booking', 'b.bookingId = \Tourpage\Models\BookingTours.bookingId', 'b');
        $bookings->where("b.memberId = :member_id:");
        $modelBind['member_id'] = $this->member->getId();
        $modelBind['current_date'] = \Tourpage\Helpers\Utils::currentDate();
        if ($type == 'upcomming') {
            $bookings->andWhere("\Tourpage\Models\BookingTours.departureOn > :current_date:");
        }
        if ($type == 'past') {
            $bookings->andWhere("\Tourpage\Models\BookingTours.departureOn <= :current_date:");
        }
        if (count($modelBind) > 0) {
            $bookings->bind($modelBind);
        }
        $bookings->order("\Tourpage\Models\BookingTours.departureOn " . ($type == 'upcomming' ? 'ASC' : 'DESC'));
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $bookings->execute(),
            "page" => $page,
        ));
        $pager->setUriPattern('/account/tours/' . $type . '/{page}');
        $this->tag->setTitle('My Tours - ' . ucfirst($type));
        $this->view->type = $type;
        $this->view->pager = $pager;
    }

    /**
     * Action for member account setting
     */
    public function settingsAction() {
        if ($this->request->isPost()) {
            $language = $this->request->getPost('language');
            $currency = $this->request->getPost('currency');
            $newsletter = $this->request->getPost('newsletter');
            $memberData = $this->member->refresh();
            $memberData->languageId = $language;
            $memberData->currencyId = $currency;
            $memberData->newsletter = !empty($newsletter) ? \Tourpage\Models\Members::NEWSLETTER_SUBSCRIBE_STATUS_CODE : \Tourpage\Models\Members::NEWSLETTER_UNSUBSCRIBE_STATUS_CODE;
            if ($memberData->save()) {
                $this->member->refresh();
                $this->flash->success('Settings has been saved successfully');
            } else {
                foreach ($memberData->getMessages() as $message) {
                    $this->flash->error((string) $message);
                }
            }
            $this->response->redirect($this->router->getRewriteUri());
        }
        $memberData = $this->member->getResource();
        \Phalcon\Tag::setDefaults(array(
            'language' => $memberData->languageId,
            'currency' => $memberData->currencyId,
            'newsletter' => $memberData->newsletter,
        ));
        $this->tag->setTitle('Account Settings');
    }

    /**
     * Action for member favorite list
     */
    public function favoritesAction($page = 1) {
        $this->tag->setTitle('My Favorites');
        $modelBind = [];
        $favoriteFilter = new \stdClass();
        $favoriteFilter->Categories = [];
        $favoriteFilter->Cities = [];
		$favoriteFilter->Keywords = [];
		$favoriteFilter->Attractions = [];
        $favoriteTours = \Tourpage\Models\MembersFavoriteTours::query();
        $favoriteTours->where("\Tourpage\Models\MembersFavoriteTours.memberId = :member_id:");
        $modelBind['member_id'] = $this->member->getId();
        if (count($modelBind) > 0) {
            $favoriteTours->bind($modelBind);
        }
        $favoriteToursPager = $favoriteTours->execute();
        
        if ($favoriteToursPager && $favoriteToursPager->count() > 0) {
            foreach ($favoriteToursPager as $favoriteTour) {
				
                foreach ($favoriteTour->tour->tourCategories as $tourCategory) {
                    if (!isset($favoriteFilter->Categories[$tourCategory->categoryTour->categoryId])) {
                        $favoriteFilter->Categories[$tourCategory->categoryTour->categoryId] = (object) [
                            'id' => $tourCategory->categoryTour->categoryId,
                            'title' => $tourCategory->categoryTour->categoryTitle,
                            'slug' => $tourCategory->categoryTour->categorySlug,
                            //'tourCount' => $tourCategory->categoryTour->tours->count(),
                            'tourCount' => 0,
                        ];
						
                    }
                    $favoriteFilter->Categories[$tourCategory->categoryTour->categoryId]->tourCount ++;
					
                }
                if (!empty($favoriteTour->tour->tourCity)) {
                    $cityMd5 = md5($favoriteTour->tour->tourCity);
                    if (!isset($favoriteFilter->Cities[$cityMd5])) {
                        /*$criteria = [
                            'tourCity = :city:',
                            'bind' => ['city' => $favoriteTour->tour->tourCity]
                        ];
                        $tourCount = \Tourpage\Models\Tours::count($criteria);*/
                        $favoriteFilter->Cities[$cityMd5] = (object) [
                            'title' => $favoriteTour->tour->tourCity,
                            'slug' => \Tourpage\Helpers\Utils::slug($favoriteTour->tour->tourCity),
                            //'tourCount' => $tourCount
                            'tourCount' => 0
                        ];
                    }
                    $favoriteFilter->Cities[$cityMd5]->tourCount ++;
                }
				
				if (!empty($favoriteTour->tour->tourKeyword)) {
					$kwd = explode(', ',$favoriteTour->tour->tourKeyword);
					for($i=0;$i<count($kwd);$i++)
					{
						$keywordMd5 = md5($kwd[$i]);
					
                    //$keywordMd5 = md5($favoriteTour->tour->tourKeyword);
                    if (!isset($favoriteFilter->Keywords[$keywordMd5])) {
                        $favoriteFilter->Keywords[$keywordMd5] = (object) [
                            'title' => $kwd[$i],
                            'tourId' => $favoriteTour->tour->tourId,
							'tourCount' => 0
                        ];
                    }
                    $favoriteFilter->Keywords[$keywordMd5]->tourCount ++;
					}
                }
				foreach ($favoriteTour->tour->tourAttractions as $tourAttraction) {
                    if (!isset($favoriteFilter->Attractions[$tourAttraction->attractionTour->attractionId])) {
                        $favoriteFilter->Attractions[$tourAttraction->attractionTour->attractionId] = (object) [
                            'id' => $tourAttraction->attractionTour->attractionId,
                            'title' => $tourAttraction->attractionTour->attractionName,
                            /*'slug' => $tourAttraction->attractionTour->categorySlug,*/
                            'tourCount' => 0,
                        ];
						
                    }
                    $favoriteFilter->Attractions[$tourAttraction->attractionTour->attractionId]->tourCount ++;
					
                }
            }
        }
        if ($this->request->hasQuery('sl') && $this->request->hasQuery('ci')) {
            $categoryId = $this->request->getQuery('ci');
            $modelBind['category_id'] = $categoryId;
            $favoriteTours->leftJoin('\Tourpage\Models\ToursCategory', 'tc.tourId = \Tourpage\Models\MembersFavoriteTours.tourId', 'tc');
            $favoriteTours->andWhere("tc.categoryId = :category_id:");
        }
        if ($this->request->hasQuery('l')) {
            $city = urldecode($this->request->getQuery('l'));
            $modelBind['city'] = '%' . $city . '%';
            $favoriteTours->leftJoin('\Tourpage\Models\Tours', 't.tourId = \Tourpage\Models\MembersFavoriteTours.tourId', 't');
            $favoriteTours->andWhere("t.tourCity LIKE :city:");
        }
		if ($this->request->hasQuery('ti')) {
            $tourId = urldecode($this->request->getQuery('ti'));
            $modelBind['tourId'] = '%' . $tourId . '%';
            $favoriteTours->leftJoin('\Tourpage\Models\Tours', 't.tourId = \Tourpage\Models\MembersFavoriteTours.tourId', 't');
            $favoriteTours->andWhere("t.tourId LIKE :tourId:");
        }
		if ($this->request->hasQuery('at')) {
            $attractionId = $this->request->getQuery('at');
            $modelBind['attraction_id'] = $attractionId;
            $favoriteTours->leftJoin('\Tourpage\Models\ToursAttractions', 'ta.tourId = \Tourpage\Models\MembersFavoriteTours.tourId', 'ta');
            $favoriteTours->andWhere("ta.attractionId = :attraction_id:");
        }
        if (count($modelBind) > 0) {
            $favoriteTours->bind($modelBind);
        }
        $favoriteToursPager = $favoriteTours->execute();
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $favoriteToursPager,
            "page" => $page,
            'limit' => 12
        ));
        $this->view->favoriteTours = $pager;
        $this->view->favoriteFilter = $favoriteFilter;
    }

    /**
     * Action for remove item from member favorite list
     * @param mixed $favoriteId
     * @param string $type
     */
    public function removefavoriteAction($favoriteId, $type) {
        if (!empty($favoriteId) && !empty($type)) {
            if ($this->member->removeFromFavorite($favoriteId, $type)) {
                $this->flash->success("Favorite Item has been removed from favorite list.");
            }
        }
        $this->response->redirect('/account/favorites');
    }

    /**
     * Action for member change password
     */
    public function changepassAction() {
        $passwordForm = new \Multiple\Frontend\Forms\PasswordForm();
        if ($this->request->isPost()) {
            if ($passwordForm->isValid($this->request->getPost())) {
                $oldPassword = $this->request->getPost('old_password');
                $password = $this->request->getPost('password');
                $rePassword = $this->request->getPost('re_password');
                $member = $this->member->refresh();
                if (strcasecmp($member->passWord, \Tourpage\Helpers\Utils::encryptPassword($oldPassword)) == 0) {
                    $member->passWord = \Tourpage\Helpers\Utils::encryptPassword($password);
                    if ($member->save()) {
                        $this->flash->success('Password has been changed successfully.');
                    } else {
                        foreach ($member->getMessages() as $memberMessage) {
                            $this->flash->error((string) $memberMessage);
                        }
                    }
                } else {
                    $this->flash->error('Invalid Old Password');
                }
                $this->response->redirect($this->router->getRewriteUri());
            }
        }
        $this->tag->setTitle('Change Password');
        $this->view->form = $passwordForm;
    }

    public function alertsAction() {
        $this->tag->setTitle('Alerts');
		$modelBind = [];
        $alertLists = \Tourpage\Models\MembersNotifications::query();
        $alertLists->where("\Tourpage\Models\MembersNotifications.memberId = :member_id:");
        $modelBind['member_id'] = $this->member->getId();
        if (count($modelBind) > 0) {
            $alertLists->bind($modelBind);
        }
        $alertListsPager = $alertLists->execute();
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $alertListsPager,
            "page" => $page,
            'limit' => 10
        ));
        $this->view->alertLists = $pager;
    }
    public function readalertsAction($notificationId) {
        if (!empty($notificationId)) {
			$criteria = array(
                    'memberId = :member_id: AND notificationId = :notification_id:',
                    'bind' => array(
                        'member_id' => $this->member->getId(),
						'notification_id' => $notificationId
                    )
                );
			$alert = \Tourpage\Models\MembersNotifications::findFirst($criteria);
			if($alert){
				$alert->memberNotificationStatus = \Tourpage\Models\MembersNotifications::READ_STATUS_CODE;
				if($alert->save()){
					$this->flash->success("Alert marked read successfully.");
				}
			}
        }
        $this->response->redirect('/account/alerts');
    }
}
