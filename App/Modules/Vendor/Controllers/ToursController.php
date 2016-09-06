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
 * Class Product Controller
 * @author amit
 */
class ToursController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for Product Controller
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
            $tourName = $this->request->getPost('tn');
            $tourBookingStatus = $this->request->getPost('bs');
            $tourStatus = $this->request->getPost('s');
            $redirectTo = $this->url->getBaseUri() . '/vendor/tours';
            if ($tourName != '') {
                $queryString .= 'tn=' . $tourName . '&';
            }
            if ($tourBookingStatus != '') {
                if ($tourBookingStatus != '[all]') {
                    $queryString .= 'bs=' . $tourBookingStatus . '&';
                }
            }
            if ($tourStatus != '') {
                if ($tourStatus != '[all]') {
                    $queryString .= 's=' . $tourStatus . '&';
                }
            }
            if ($queryString) {
                $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                $redirectTo = $redirectTo . '?' . $queryString;
            }
            $this->response->redirect($redirectTo);
        }
        $tours = \Tourpage\Models\VendorsTours::query();
        $tours->where("vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $vendorId;
        if (!$this->vendors->getVendorData()->isParent()) {
            $tours->andWhere("\Tourpage\Models\VendorsTours.createBy = :create_by:");
            $modelBind['create_by'] = $this->vendors->getId();
        }
        if ($this->request->hasQuery('tn')) {
            $defaultValues['tn'] = $this->request->getQuery('tn');
            $modelBind['title'] = "%" . $defaultValues['tn'] . "%";
            $tours->join('\Tourpage\Models\Tours', 't.tourId = \Tourpage\Models\VendorsTours.tourId', 't');
            $tours->andWhere("t.tourTitle LIKE :title:");
        }
        if ($this->request->hasQuery('bs')) {
            $defaultValues['bs'] = $this->request->getQuery('bs');
            $modelBind['booking'] = $defaultValues['bs'];
            $tours->join('\Tourpage\Models\Tours', 'tbs.tourId = \Tourpage\Models\VendorsTours.tourId', 'tbs');
            $tours->andWhere("tbs.tourBookingStatus = :booking:");
        }
        if ($this->request->hasQuery('s')) {
            $defaultValues['s'] = $this->request->getQuery('s');
            $modelBind['status'] = $defaultValues['s'];
            $tours->andWhere("\Tourpage\Models\VendorsTours.status = :status:");
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $tours->bind($modelBind);
        }
        $tours->order("\Tourpage\Models\VendorsTours.vendorTourId DESC");
        $this->assets->collection('header')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('footer')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->setTitle('Tours');
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $tours->execute(),
            "page" => $page,
        ));
        $this->view->defaultValues = $defaultValues;
        $this->view->bookingInfo = $this->getBookingInfo($vendorId);
        $this->view->pager = $pager;
    }
    
    private function getBookingInfo($vendorId)
    {
        $info = [];
        $bookings = \Tourpage\Models\BookingTours::query();
        $bookings->where("\Tourpage\Models\BookingTours.vendorId = :vendorId:");
        $modelBind['vendorId'] = $vendorId;
        $bookings->bind($modelBind);
        $bookings->order("\Tourpage\Models\BookingTours.bookingId DESC");
        $bookings->groupBy("\Tourpage\Models\BookingTours.bookingId");
        $bookingsData = $bookings->execute();
        foreach ($bookingsData as $item) {
            $info['bookedCount'][$item->tourId] = $item->headCount;
            //$booking = \Tourpage\Models\Booking::findFirst($item->bookingId);
            //$info['bookingStatus'][$item->tourId] = $booking->bookingStatus;
        }
        return $info;
    }

    /**
     * Action for Product Add
     * This action will create new product
     */
    public function addAction() {
        $this->tag->setTitle('Create New Tour');
        $tourForm = new \Multiple\Vendor\Forms\TourForm();
		//var_dump($this->request->getPost());
        if ($this->request->isPost()) {
            $submitType = $this->request->getPost('submit');
            if ($tourForm->isValid($this->request->getPost())) {
                if ($this->validateTourForm()) {
                    $lengthData = [];
                    $lengthData['length_type'] = $this->request->getPost('tour_length');
                    switch ($lengthData['length_type']) {
                        case \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE:
                        case \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE:
                            if ($this->request->getPost('lmd_duration')) {
                                $lengthData['duration'] = $this->request->getPost('lmd_duration');
                            }
                            break;
                        case \Tourpage\Models\Tours::LENGTH_SINGLE_DAY_CODE:
                            if ($this->request->getPost('lsd_duration_hr') || $this->request->getPost('lsd_duration_mn')) {
                                $lengthData['duration_hr'] = $this->request->getPost('lsd_duration_hr');
                                $lengthData['duration_mn'] = $this->request->getPost('lsd_duration_mn');
                            }
                            break;
                        case \Tourpage\Models\Tours::LENGTH_HOURLY:
                            if ($this->request->getPost('tour_times')) {
                                $lengthData['duration_times'] = $this->request->getPost('tour_times');
                            }
                            break;
                    }

                    if ($lengthData['length_type'] == \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE || $lengthData['length_type'] == \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE) {
                        $lengthData['week_days'] = array_keys(\Tourpage\Helpers\Utils::weekDays());
                    } else {
                        $lengthData['week_days'] = $this->request->getPost('tour_week_days');
                    }

                    $socialMedia = [];
                    if (!isset($socialMedia['links'])) {
                        $socialMedia['links'] = [];
                    }
                    if ($this->request->getPost('social_media_links_facebook')) {
                        if (!isset($socialMedia['links']['facebook'])) {
                            $socialMedia['links']['facebook'] = '';
                        }
                        $socialMedia['links']['facebook'] = urlencode($this->request->getPost('social_media_links_facebook'));
                    }
                    if ($this->request->getPost('social_media_links_twitter')) {
                        if (!isset($socialMedia['links']['twitter'])) {
                            $socialMedia['links']['twitter'] = '';
                        }
                        $socialMedia['links']['twitter'] = urlencode($this->request->getPost('social_media_links_twitter'));
                    }
                    if ($this->request->getPost('social_media_links_instagram')) {
                        if (!isset($socialMedia['links']['instagram'])) {
                            $socialMedia['links']['instagram'] = '';
                        }
                        $socialMedia['links']['instagram'] = urlencode($this->request->getPost('social_media_links_instagram'));
                    }
                    $tourCapacity = $this->request->getPost('tour_capacity', "int");
                    if ($tourCapacity <= 0) {
                        $tourCapacity = -1;
                    }
                    $tour = new \Tourpage\Models\Tours();
                    $tour->tourId = \Tourpage\Helpers\Utils::getUid();
                    $tour->tourTitle = $this->request->getPost('tour_title', "string");
                    $tour->tourSlug = \Tourpage\Helpers\Utils::slug($tour->tourTitle);
                    $tour->tourSubTitle = $this->request->getPost('tour_sub_title', "string");
                    $tour->tourBookingStatus = $this->request->getPost('tour_booking_status', "int");
                    $tour->tourCapacity = $tourCapacity;
                    $tour->tourKeyword = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('tour_keyword'));
                    $tour->tourPolicy = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('tour_policy'));
                    $tour->tourStartFrom = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_start_date'));
                    $tour->tourEndTo = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_end_date'));
                    $tour->tourLengthData = serialize($lengthData);
                    $tour->socialMedia = serialize($socialMedia);
                    $tour->tourCountryId = $this->request->getPost('tour_country', "int");
                    $tour->tourStateId = $this->request->getPost('tour_state', "int");
                    $tour->tourCity = $this->request->getPost('tour_city', "string");
                    $tour->tourCreatedOn = \Tourpage\Helpers\Utils::currentDate();
                    if ($tour->save()) {
                        $priceData = [];
                        $priceData['tour_price_type'] = $this->request->getPost('tour_price_type', 'string');
                        if ($priceData['tour_price_type'] == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE) {
                            $priceData['tour_price_group'] = $this->request->getPost('tour_age_group');
                        }
                        if ($priceData['tour_price_type'] == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE) {
                            $priceData['tour_price'] = $this->request->getPost('tour_price', 'float');
                        }
                        //$priceData['tour_price_for_child'] = $this->request->getPost('tour_price_for_child', 'float');
                        if ($this->request->hasPost('special_disc_enb')) {
                            if ($this->request->getPost('special_disc_enb') == 'y') {
                                if (!isset($priceData['discount'])) {
                                    $priceData['discount'] = [];
                                }
                                if ($this->request->getPost('tour_discount', 'float') && $this->request->getPost('tour_discount_start') && $this->request->getPost('tour_discount_end')) {
                                    $priceData['discount']['price'] = $this->request->getPost('tour_discount', 'float');
                                    $priceData['discount']['start'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_discount_start'));
                                    $priceData['discount']['end'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_discount_end'));
                                } else {
                                    unset($priceData['discount']);
                                }
                            }
                        }

                        if ($this->request->hasPost('tour_mp_discount') && $this->request->hasPost('tour_mp_count')) {
                            if (!isset($priceData['discount'])) {
                                $priceData['discount'] = [];
                            }
                            $priceData['discount']['mp_percentage'] = $this->request->getPost('tour_mp_discount', 'float');
                            $priceData['discount']['mp_count'] = $this->request->getPost('tour_mp_count', 'int');
                        }

                        if ($this->request->hasPost('tour_seasonal_offer')) {
                            if (!isset($priceData['tour_seasonal_offer'])) {
                                $priceData['tour_seasonal_offer'] = [];
                            }

                            $tourSeasonalOffers = $this->request->getPost('tour_seasonal_offer');
                            if (count($tourSeasonalOffers) > 0) {
                                foreach ($tourSeasonalOffers as $seasonalOffer) {
                                    if (!empty($seasonalOffer['name']) && !empty($seasonalOffer['start']) && !empty($seasonalOffer['end']) && !empty($seasonalOffer['price'])) {
                                        $priceData['tour_seasonal_offer'][] = array(
                                            'name' => $seasonalOffer['name'],
                                            'start' => \Tourpage\Helpers\Utils::formatDatepickerToMySql($seasonalOffer['start']),
                                            'end' => \Tourpage\Helpers\Utils::formatDatepickerToMySql($seasonalOffer['end']),
                                            'price' => $seasonalOffer['price'],
                                        );
                                    }
                                }
                            }
                        }

                        if (count($priceData) > 0) {
                            $tourPrice = new \Tourpage\Models\ToursPrice();
                            $tourPrice->tourId = $tour->tourId;
                            $tourPrice->priceData = serialize($priceData);
                            if (!$tourPrice->save()) {
                                $this->flash->error($tourPrice->getMessages());
                            }
                        }

                        foreach ($this->request->getPost('tour_category') as $category) {
                            $touCategories = new \Tourpage\Models\ToursCategory();
                            $touCategories->categoryId = $category;
                            $touCategories->tourId = $tour->tourId;
                            $touCategories->save();
                        }
                        if ($this->request->getPost('tour_option')) {
                            foreach ($this->request->getPost('tour_option') as $option) {
                                if (!empty($option['name']) && !empty($option['price'])) {
                                    $filter = new \Phalcon\Filter();
                                    $tourOption = new \Tourpage\Models\ToursOptions();
                                    $tourOption->tourId = $tour->tourId;
                                    $tourOption->optionName = $filter->sanitize($option['name'], "string");
                                    $tourOption->optionPrice = $filter->sanitize($option['price'], "float");
                                    $tourOption->optionPriceType = $filter->sanitize($option['price_type'], "string");
                                    $tourOption->optionStatus = \Tourpage\Models\ToursOptions::ACTIVE_STATUS_CODE;
                                    $tourOption->save();
                                }
                            }
                        }
                        foreach ($this->request->getPost('tour_details') as $detailsKey => $detailsContent) {
                            $tourAttribute = new \Tourpage\Models\ToursAttributes();
                            $tourAttribute->keyId = $detailsKey;
                            $tourAttribute->tourId = $tour->tourId;
                            $tourAttribute->attributeContent = \Tourpage\Helpers\Utils::encodeString($detailsContent);
                            $tourAttribute->save();
                        }

                        $tour_images = $this->request->getPost('tour_image');
                        $baseLocation = $this->vendors->getTourImagesPath();
                        $i = 1;
                        foreach ($tour_images['name'] as $fileIndex => $fileName) {
                            if ($i <= 8) {
                                $tourImage = new \Tourpage\Models\ToursImages();
                                $tourImage->tourId = $tour->tourId;
                                $tourImage->imagePath = $fileName;
                                $tourImage->imageDefault = $i == 1 ? \Tourpage\Models\ToursImages::DEFAULT_STATUS_CODE : \Tourpage\Models\ToursImages::NORMAL_STATUS_CODE;
                                $tourImage->imageUploadedOn = \Tourpage\Helpers\Utils::currentDate();
                                if ($tourImage->save()) {
                                    $imageFile = new \Phalcon\Image\Adapter\GD($tour_images['path'][$fileIndex] . '/' . $fileName);
                                    $imageFile->save($baseLocation . '/' . $fileName);
                                    $thumbFile = new \Phalcon\Image\Adapter\GD($tour_images['path'][$fileIndex] . '/' . 'thumb' . $fileName);
                                    $thumbFile->save($baseLocation . '/' . 'thumb' . $fileName);
                                    unlink($tour_images['path'][$fileIndex] . '/' . $fileName);
                                    unlink($tour_images['path'][$fileIndex] . '/' . 'thumb' . $fileName);
                                }
                                $i++;
                            }
                        }

                        $vendorTour = new \Tourpage\Models\VendorsTours();
                        $vendorTour->vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
                        $vendorTour->createBy = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getId() : 0;
                        $vendorTour->tourId = $tour->tourId;
                        $vendorTour->status = $this->request->getPost('tour_status', "int");
                        $vendorTour->save();

                        $tourKeyHighlights = $this->request->getPost('tour_key_highlight');
                        if ($tourKeyHighlights && count($tourKeyHighlights) > 0) {
                            foreach ($tourKeyHighlights as $tourKeyHighlight) {
                                $toursKeyHighlight = new \Tourpage\Models\ToursKeyHighlight();
                                $toursKeyHighlight->tourId = $tour->tourId;
                                $toursKeyHighlight->keyhighlightId = $tourKeyHighlight;
                                $toursKeyHighlight->save();
                            }
                        }
						if ($this->request->getPost('place_of_attractions')) {
							$tourAttractions = new \Tourpage\Models\ToursAttractions();
                            $tourAttractions->attractionId = $this->request->getPost('place_of_attractions');
                            $tourAttractions->tourId = $tour->tourId;
                            $tourAttractions->save();
						}
						
                        $queryString = '';
                        if ($submitType === 'Submit') {
                            $this->flash->success("Tour has been added successfuly.");
                            //$this->response->redirect($this->router->getRewriteUri());
                            //$this->response->redirect($tour->getUri());
                        }
                        if ($submitType === 'Preview') {
                            //$queryString .= '#preview?st=prv&vi=' . (!$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId()) . '&ti=' . $tour->tourId;
                        }
                        //$this->response->redirect('/vendor/tours/edit/' . $tour->tourId . $queryString);
                        $this->response->redirect('/vendor/tours');
                    } else {
                        foreach ($tour->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                }
            }
        }

        $duration = new \stdClass();
        $duration->Time = new \stdClass();
        $duration->Time->Hours = $duration->Time->Minutes = [];
        for ($h = 0; $h <= 23; $h++) {
            $duration->Time->Hours[$h] = (string) (strlen($h) == 1 ? '0' . $h : $h);
        }
        for ($m = 0; $m <= 59; $m = $m + 5) {
            $duration->Time->Minutes[$m] = (string) (strlen($m) == 1 ? '0' . $m : $m);
        }
		$placeOfAttractions = \Tourpage\Models\PlaceOfAttractions::find(array(
                    'conditions' => 'stateId = :state_id:',
                    'bind' => array('state_id' => $this->request->getPost('tour_state', "int"))
        ));
        $this->view->placeOfAttractions = $placeOfAttractions;
        $keyHighlights = \Tourpage\Models\KeyHighlight::find(array(
                    'conditions' => 'keyhighlightStatus = :status:',
                    'bind' => array('status' => \Tourpage\Models\KeyHighlight::ACTIVE_STATUS_CODE)
        ));
        $this->view->keyHighlights = $keyHighlights;
        $this->view->ageGroups = \Tourpage\Helpers\Utils::getVar('config_tour_age_group');
        $this->view->timeDuration = $duration;
        $this->view->toursAttributeKeys = \Tourpage\Models\ToursAttributeKeys::find(array(
                    'keyStatus = :status:',
                    'bind' => array('status' => \Tourpage\Models\ToursAttributeKeys::ACTIVE_STATUS_CODE)
        ));
        $this->view->form = $tourForm;
    }

    /**
     * Action for tour edit
     * @param string $tourId
     * @return boolean
     */
    public function editAction($tourId = '') {
        $this->tag->setTitle('Update Tour');
        $tour = \Tourpage\Models\Tours::findFirstByTourId($tourId);
        if (!$tour) {
            return false;
        }
        $tourForm = new \Multiple\Vendor\Forms\TourForm($tour, array('edit' => TRUE));
        if ($this->request->isPost()) {
			$submitType = $this->request->getPost('submit');
            if ($tourForm->isValid($this->request->getPost())) {
                if ($this->validateTourForm(array('edit' => true, 'entity' => $tour))) {
                    $lengthData = [];
                    $lengthData['length_type'] = $this->request->getPost('tour_length');
                    switch ($lengthData['length_type']) {
                        case \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE:
                        case \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE:
                            if ($this->request->getPost('lmd_duration')) {
                                $lengthData['duration'] = $this->request->getPost('lmd_duration');
                            }
                            break;
                        case \Tourpage\Models\Tours::LENGTH_SINGLE_DAY_CODE:
                            if ($this->request->getPost('lsd_duration_hr') || $this->request->getPost('lsd_duration_mn')) {
                                $lengthData['duration_hr'] = $this->request->getPost('lsd_duration_hr');
                                $lengthData['duration_mn'] = $this->request->getPost('lsd_duration_mn');
                            }
                            break;
                        case \Tourpage\Models\Tours::LENGTH_HOURLY:
                            if ($this->request->getPost('tour_times')) {
                                $lengthData['duration_times'] = $this->request->getPost('tour_times');
                            }
                            break;
                    }

                    if ($lengthData['length_type'] == \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE || $lengthData['length_type'] == \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE) {
                        $lengthData['week_days'] = array_keys(\Tourpage\Helpers\Utils::weekDays());
                    } else {
                        $lengthData['week_days'] = $this->request->getPost('tour_week_days');
                    }

                    $socialMedia = [];
                    if (!isset($socialMedia['links'])) {
                        $socialMedia['links'] = [];
                    }
                    if ($this->request->getPost('social_media_links_facebook')) {
                        if (!isset($socialMedia['links']['facebook'])) {
                            $socialMedia['links']['facebook'] = '';
                        }
                        $socialMedia['links']['facebook'] = urlencode($this->request->getPost('social_media_links_facebook'));
                    }
                    if ($this->request->getPost('social_media_links_twitter')) {
                        if (!isset($socialMedia['links']['twitter'])) {
                            $socialMedia['links']['twitter'] = '';
                        }
                        $socialMedia['links']['twitter'] = urlencode($this->request->getPost('social_media_links_twitter'));
                    }
                    if ($this->request->getPost('social_media_links_instagram')) {
                        if (!isset($socialMedia['links']['instagram'])) {
                            $socialMedia['links']['instagram'] = '';
                        }
                        $socialMedia['links']['instagram'] = urlencode($this->request->getPost('social_media_links_instagram'));
                    }
                    $tourCapacity = $this->request->getPost('tour_capacity', "int");
                    if ($tourCapacity <= 0) {
                        $tourCapacity = -1;
                    }
                    //$tour->tourTitle = $this->request->getPost('tour_title', "string");
                    //$tour->tourSlug = \Tourpage\Helpers\Utils::slug($tour->tourTitle);
                    //$tour->tourSubTitle = $this->request->getPost('tour_sub_title', "string");
                    //$tour->tourBookingStatus = $this->request->getPost('tour_booking_status', "int");
                    $tour->tourCapacity = $tourCapacity;
                    $tour->tourKeyword = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('tour_keyword'));
                    $tour->tourPolicy = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('tour_policy'));
                    //$tour->tourStartFrom = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_start_date'));
                    //$tour->tourEndTo = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_end_date'));
                    //$tour->tourLengthData = serialize($lengthData);
                    $tour->socialMedia = serialize($socialMedia);
                    //$tour->tourCountryId = $this->request->getPost('tour_country', "int");
                    //$tour->tourStateId = $this->request->getPost('tour_state', "int");
                    //$tour->tourCity = $this->request->getPost('tour_city', "string");
                    if ($tour->save()) {
                        $priceData = [];
						//var_dump( $this->request->getPost());die();
                        $priceData['tour_price_type'] = $this->request->getPost('tour_price_type', 'string');
                        if ($priceData['tour_price_type'] == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE) {
                            $priceData['tour_price_group'] = $this->request->getPost('tour_age_group');
                        }
                        if ($priceData['tour_price_type'] == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE) {
                            $priceData['tour_price'] = $this->request->getPost('tour_price', 'float');
                        }
                        //$priceData['tour_price_for_child'] = $this->request->getPost('tour_price_for_child', 'float');
                        if ($this->request->hasPost('special_disc_enb')) {
                            if ($this->request->getPost('special_disc_enb') == 'y') {
                                if (!isset($priceData['discount'])) {
                                    $priceData['discount'] = [];
                                }
                                if ($this->request->getPost('tour_discount', 'float') && $this->request->getPost('tour_discount_start') && $this->request->getPost('tour_discount_end')) {
                                    $priceData['discount']['price'] = $this->request->getPost('tour_discount', 'float');
                                    $priceData['discount']['start'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_discount_start'));
                                    $priceData['discount']['end'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('tour_discount_end'));
                                } else {
                                    unset($priceData['discount']);
                                }
                            }
                        }

                        if ($this->request->hasPost('tour_mp_discount') && $this->request->hasPost('tour_mp_count')) {
                            if (!isset($priceData['discount'])) {
                                $priceData['discount'] = [];
                            }
                            $priceData['discount']['mp_percentage'] = $this->request->getPost('tour_mp_discount', 'float');
                            $priceData['discount']['mp_count'] = $this->request->getPost('tour_mp_count', 'int');
                        }

                        //if ($this->request->hasPost('tour_seasonal_offer')) {
                            //if (!isset($priceData['tour_seasonal_offer'])) {
                               // $priceData['tour_seasonal_offer'] = [];
                            //}

                           // $tourSeasonalOffers = $this->request->getPost('tour_seasonal_offer');
                            //if (count($tourSeasonalOffers) > 0) {
                               // foreach ($tourSeasonalOffers as $seasonalOffer) {
                                 //   if (!empty($seasonalOffer['name']) && !empty($seasonalOffer['start']) && !empty($seasonalOffer['end']) && !empty($seasonalOffer['price'])) {
                                       // $priceData['tour_seasonal_offer'][] = array(
                                            //'name' => $seasonalOffer['name'],
                                            //'start' => \Tourpage\Helpers\Utils::formatDatepickerToMySql($seasonalOffer['start']),
                                            //'end' => \Tourpage\Helpers\Utils::formatDatepickerToMySql($seasonalOffer['end']),
                                            //'price' => $seasonalOffer['price'],
                                        //);
                                    //}
                                //}
                            //}
                        //}

                        if (count($priceData) > 0) {
                            $tourPrice = \Tourpage\Models\ToursPrice::findFirst(array(
                                        'tourId = :tour_id:',
                                        'bind' => array('tour_id' => $tour->tourId),
                            ));
                            if (!$tourPrice) {
                                $tourPrice = new \Tourpage\Models\ToursPrice();
                            }
                            $tourPrice->tourId = $tour->tourId;
                            $tourPrice->priceData = serialize($priceData);
							 $tourPrice->update();

                            if (!$tourPrice->save()) {
                                \Tourpage\Helpers\Utils::printArray($tourPrice->getMessages());
                            }
                        }

                        
						foreach ($this->request->getPost('tour_category') as $category) {
                            $tourCategory = \Tourpage\Models\ToursCategory::findFirst(array(
                                        'categoryId = :category_id: AND tourId = :tour_id:',
                                        'bind' => array(
                                            'category_id' => $category,
                                            'tour_id' => $tour->tourId
                                        )
                            ));
                            if (!$tourCategory) {
                                $touCategories = new \Tourpage\Models\ToursCategory();
                                $touCategories->categoryId = $category;
                                $touCategories->tourId = $tour->tourId;
                                $touCategories->save();
                            }
                        }
                        if ($this->request->getPost('tour_category')) {
                            $postCategories = $this->request->getPost('tour_category');
                            $tourCategories = \Tourpage\Models\ToursCategory::find(array(
                                        'tourId = :tour_id:',
                                        'bind' => array(
                                            'tour_id' => $tour->tourId
                                        )
                            ));
                            if ($tourCategories && count($tourCategories) > 0) {
                                foreach ($tourCategories as $tourCategory) {
                                    if (!in_array($tourCategory->categoryId, $postCategories)) {
                                        $tourCategory->delete();
                                    }
                                }
                            }
                        }
                        foreach ($this->request->getPost('tour_details') as $detailsKey => $detailsContent) {
                                 $tourAttribute = \Tourpage\Models\ToursAttributes::findFirst(array(
                                        'keyId = :key_id: AND tourId = :tour_id:',
                                        'bind' => array(
                                            'key_id' => $detailsKey,
                                            'tour_id' => $tour->tourId,
                                        )
                            ));
                            if ($tourAttribute && $tourAttribute->count() > 0) {
                                $tourAttribute->attributeContent = \Tourpage\Helpers\Utils::encodeString($detailsContent);
                                $tourAttribute->save();
                            } else {
                                $tourAttribute = new \Tourpage\Models\ToursAttributes();
                                $tourAttribute->keyId = $detailsKey;
                                $tourAttribute->tourId = $tour->tourId;
                                $tourAttribute->attributeContent = \Tourpage\Helpers\Utils::encodeString($detailsContent);
                                $tourAttribute->save();
                            }
                        }
                        /*if ($this->request->getPost('tour_option')) {
                            $deleteAttributes = \Tourpage\Models\ToursOptions::find(array(
                                        'tourId = :tour_id:',
                                        'bind' => array('tour_id' => $tour->tourId),
                            ));
                            if ($deleteAttributes->delete()) {
                                $removeOptions = array();
                                if ($this->request->getPost('remove_opt')) {
                                    $removeOptions = $this->request->getPost('remove_opt');
                                    foreach ($removeOptions as $removeOption) {
                                        $removeTourOpt = \Tourpage\Models\ToursOptions::findFirst($removeOption);
                                        if ($removeTourOpt) {
                                            $removeTourOpt->delete();
                                        }
                                    }
                                }
                                foreach ($this->request->getPost('tour_option') as $optionKey => $option) {
                                    if (!in_array($optionKey, $removeOptions)) {
                                        if (!empty($option['name']) && !empty($option['price'])) {
                                            $filter = new \Phalcon\Filter();
                                            $tourOption = new \Tourpage\Models\ToursOptions();
                                            $tourOption->tourId = $tour->tourId;
                                            $tourOption->optionName = $filter->sanitize($option['name'], "string");
                                            $tourOption->optionPrice = $filter->sanitize($option['price'], "float");
                                            $tourOption->optionPriceType = $filter->sanitize($option['price_type'], "string");
                                            $tourOption->optionStatus = \Tourpage\Models\ToursOptions::ACTIVE_STATUS_CODE;
                                            $tourOption->save();
                                        }
                                    }
                                }
                            }
                        }*/

                        if ($this->request->getPost('remove_img')) {
                            foreach ($this->request->getPost('remove_img') as $removeImg) {
                                $tourImage = \Tourpage\Models\ToursImages::findFirst($removeImg);
                                $tourImage->removeData();
                            }
                        }

                        $tour_images = $this->request->getPost('tour_image');
                        if (isset($tour_images['name']) && count($tour_images['name']) > 0) {
                            $baseLocation = $this->vendors->getTourImagesPath();
                            $i = 1;
                            foreach ($tour_images['name'] as $fileIndex => $fileName) {
                                if ($i <= 8) {
                                    $tourImage = new \Tourpage\Models\ToursImages();
                                    $tourImage->tourId = $tour->tourId;
                                    $tourImage->imagePath = $fileName;
                                    $tourImage->imageDefault = \Tourpage\Models\ToursImages::NORMAL_STATUS_CODE;
                                    $tourImage->imageUploadedOn = time();
                                    if ($tourImage->save()) {
                                        $imageFile = new \Phalcon\Image\Adapter\GD($tour_images['path'][$fileIndex] . '/' . $fileName);
                                        $imageFile->save($baseLocation . '/' . $fileName);
                                        $thumbFile = new \Phalcon\Image\Adapter\GD($tour_images['path'][$fileIndex] . '/' . 'thumb' . $fileName);
                                        $thumbFile->save($baseLocation . '/' . 'thumb' . $fileName);
                                        unlink($tour_images['path'][$fileIndex] . '/' . $fileName);
                                        unlink($tour_images['path'][$fileIndex] . '/' . 'thumb' . $fileName);
                                    }
                                    $i++;
                                }
                            }
                        }

                        if (!$tour->getDefaultImage()) {
                            $tourImage = \Tourpage\Models\ToursImages::findFirstByTourId($tour->tourId);
                            if ($tourImage && $tourImage->count() > 0) {
                                $tourImage->imageDefault = \Tourpage\Models\ToursImages::DEFAULT_STATUS_CODE;
                                $tourImage->save();
                            }
                        }

                        if ($this->request->getPost('default_img')) {
                            $allTourImages = \Tourpage\Models\ToursImages::findByTourId($tour->tourId);
                            if ($allTourImages->count() > 0) {
                                foreach ($allTourImages as $allImage) {
                                    $allImage->imageDefault = \Tourpage\Models\ToursImages::NORMAL_STATUS_CODE;
                                    $allImage->save();
                                }
                            }
                            $defaultImageId = $this->request->getPost('default_img');
                            $tourImage = \Tourpage\Models\ToursImages::findFirst($defaultImageId);
                            if ($tourImage && $tourImage->count() > 0) {
                                $tourImage->imageDefault = \Tourpage\Models\ToursImages::DEFAULT_STATUS_CODE;
                                $tourImage->save();
                            }
                        }

                        $vendorTour = \Tourpage\Models\VendorsTours::findFirstByTourId($tour->tourId);
                        $vendorTour->status = $this->request->getPost('tour_status', "int");
                        $vendorTour->update();

                        $tourKeyHighlights = $this->request->getPost('tour_key_highlight');
                        if ($tourKeyHighlights && count($tourKeyHighlights) > 0) {
                            $tourKeyHighlightsModify = \Tourpage\Models\ToursKeyHighlight::find(array(
                                        'conditions' => 'tourId = :tour_id:',
                                        'bind' => array('tour_id' => $tourId)
                            ));
                            if ($tourKeyHighlightsModify && $tourKeyHighlightsModify->count() > 0) {
                                foreach ($tourKeyHighlightsModify as $tourKeyHighlightModify) {
                                    $tourKeyHighlightModify->delete();
                                }
                            }
                            foreach ($tourKeyHighlights as $tourKeyHighlight) {
                                $toursKeyHighlight = new \Tourpage\Models\ToursKeyHighlight();
                                $toursKeyHighlight->tourId = $tour->tourId;
                                $toursKeyHighlight->keyhighlightId = $tourKeyHighlight;
                                $toursKeyHighlight->save();
                            }
                        }
                        $this->flash->success("Tour has been updated successfuly.");
                        $this->response->redirect('/vendor/tours');
                    } else {
                        foreach ($tour->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }

                    /*$queryString = '';
                    if ($submitType === 'Submit') {
                        $this->flash->success("Tour has been updated successfuly.");
                        //$this->response->redirect($this->router->getRewriteUri());
                        $this->response->redirect('/vendor/tours');
                    }*/
                    /*if ($submitType === 'Preview') {
                        $queryString .= '#preview?st=prv&vi=' . (!$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId()) . '&ti=' . $tour->tourId;
                        $this->response->redirect('/vendor/tours/edit/' . $tour->tourId . $queryString);
                    }*/
                }
            }
        }

        $toursCategories = array();
        if ($tour->tourCategories->count() > 0) {
            foreach ($tour->tourCategories as $tourCategory) {
                $toursCategories[] = $tourCategory->categoryId;
            }
        }
        $this->view->tour = $tour;
        $tourAttributes = array();
        if ($tour->tourAttributes->count() > 0) {
            foreach ($tour->tourAttributes as $tourAttribute) {
                $tourAttributes[$tourAttribute->keyId] = \Tourpage\Helpers\Utils::decodeString($tourAttribute->attributeContent);
            }
        }
        $duration = new \stdClass();
        $duration->Time = new \stdClass();
        $duration->Time->Hours = $duration->Time->Minutes = [];
        for ($h = 0; $h <= 23; $h++) {
            $duration->Time->Hours[$h] = (string) (strlen($h) == 1 ? '0' . $h : $h);
        }
        for ($m = 0; $m <= 59; $m = $m + 5) {
            $duration->Time->Minutes[$m] = (string) (strlen($m) == 1 ? '0' . $m : $m);
        }
        $keyHighlights = \Tourpage\Models\KeyHighlight::find(array(
                    'conditions' => 'keyhighlightStatus = :status:',
                    'bind' => array('status' => \Tourpage\Models\KeyHighlight::ACTIVE_STATUS_CODE)
        ));
        $this->view->keyHighlights = $keyHighlights;
        $keyHighlightData = [];
        $tourKeyHighlightsModifyData = \Tourpage\Models\ToursKeyHighlight::find(array(
                    'conditions' => 'tourId = :tour_id:',
                    'bind' => array('tour_id' => $tourId)
        ));
        if ($tourKeyHighlightsModifyData && $tourKeyHighlightsModifyData->count() > 0) {
            foreach ($tourKeyHighlightsModifyData as $tourKeyHighlightModifyData) {
                $keyHighlightData[] = $tourKeyHighlightModifyData->keyhighlightId;
            }
        }
        $this->view->keyHighlightData = $keyHighlightData;
        $this->view->ageGroups = \Tourpage\Helpers\Utils::getVar('config_tour_age_group');
        $this->view->timeDuration = $duration;
        $this->view->tourAttributes = $tourAttributes;
        $this->view->toursCategories = $toursCategories;
        $this->view->toursAttributeKeys = \Tourpage\Models\ToursAttributeKeys::find(array(
                    'keyStatus = :status:',
                    'bind' => array('status' => \Tourpage\Models\ToursAttributeKeys::ACTIVE_STATUS_CODE)
        ));
        $this->view->form = $tourForm;
    }



    /**
     * Action for tour delete
     * @param string $tourId
     * @return boolean
     */
    public function removeAction($tourId = '') {
        $tour = \Tourpage\Models\Tours::findFirstByTourId($tourId);
        if (!$tour) {
            return false;
        }
        $this->tag->setTitle('Remove Tour');
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if ($tour->removeData()) {
                    $this->flash->success("Tours has been removed successfuly.");
                    $this->response->redirect('/vendor/tours');
                }
            }
        }
        $this->view->tour = $tour;
    }

    /**
     * Validating tour add form element
     * @return boolean
     */
    private function validateTourForm($options = null) {
        $errors = [];
        $valid = false;
        if (!isset($options['edit']) || !$options['edit']) {
            if (!$this->request->getPost('tour_category')) {
                $errors['error_tour_category'] = "Tour Category is require";
            }
            $tour_price_type = $this->request->getPost('tour_price_type', 'string');
            if ($tour_price_type == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE) {
                $tour_price_group = $this->request->getPost('tour_age_group');
                if (count($tour_price_group) > 0) {
                    foreach ($tour_price_group as $pgKey => $pg) {
                        if (empty($pg['price']) || !is_numeric($pg['price'])) {
                            $errors['error_pg_price'] = "Invalid price. Price should be numeric";
                        }
                        if (empty($pg['age']) || !is_numeric($pg['age'])) {
                            $errors['error_pg_age'] = "Invalid age. Age should be numeric";
                        }
                    }
                } else {
                    $errors['error_price_group'] = "Tour price is require";
                }
            }
            $tour_images = $this->request->getPost('tour_image');
            if (!isset($tour_images['name']) || count($tour_images['name']) == 0) {
                $errors['error_tour_image'] = "Tour image is require";
            }
            $tour_length_type = $this->request->getPost('tour_length');
            if ($tour_length_type != \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE && $tour_length_type != \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE) {
                $length_data_week_days = $this->request->getPost('tour_week_days');
                if (count($length_data_week_days) == 0) {
                    $errors['error_tour_week_days'] = "Tour need to have atleast one week day";
                }
            }
        }
        if (isset($options['edit']) && $options['edit']) {
            if (isset($options['entity']) && $options['entity'] instanceof \Tourpage\Models\Tours) {
                $tour = $options['entity'];
                $remove_img = $this->request->getPost('remove_img');
                $tour_images = $this->request->getPost('tour_image');
                if (!isset($tour_images['name']) || count($tour_images['name']) == 0) {
                    if ($tour->tourImages->count() == count($remove_img)) {
                        $errors['error_tour_image_remove'] = "Tour need to have atleast one image";
                    }
                }
            }
        }
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->flash->error($error);
            }
        } else {
            $valid = true;
        }
        return $valid;
    }

    /**
     * Action for tour promotion management
     * @param int $page
     * @return boolean
     */
    public function promotionsAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $modelBind = [];
        if ($this->request->isPost()) {
            $promotionalDiscountTours = $this->request->getPost('pdt');
            $promotionalDiscountStart = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('discount_start'));
            $promotionalDiscountEnd = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('discount_end'));
            $promotionalDiscountPercentage = $this->request->getPost('discount_percentage', 'float');

            if (count($promotionalDiscountTours) > 0) {
                if (!empty($promotionalDiscountStart) && !empty($promotionalDiscountEnd) && !empty($promotionalDiscountPercentage)) {
                    foreach ($promotionalDiscountTours as $pdt) {
                        $tourPrice = \Tourpage\Models\ToursPrice::findFirst(array(
                                    'conditions' => 'tourId = :tourId:',
                                    'bind' => array('tourId' => $pdt)
                        ));
                        if ($tourPrice && $tourPrice->count() > 0) {
                            $priceData = $tourPrice->getPriceData();
                            if (!isset($priceData['discount'])) {
                                $priceData['discount'] = [];
                            }
                            $priceData['discount']['price'] = $promotionalDiscountPercentage;
                            $priceData['discount']['start'] = $promotionalDiscountStart;
                            $priceData['discount']['end'] = $promotionalDiscountEnd;

                            $tourPrice->priceData = serialize($priceData);
                            $tourPrice->save();
                        }
                    }
                    $this->flash->success('Promotional discount successfully applied.');
                    $this->response->redirect($this->router->getRewriteUri());
                } else {
                    $this->flash->error('Invalid details found');
                }
            } else {
                $this->flash->error('Please select tour to apply discount');
            }
        }
        $tours = \Tourpage\Models\VendorsTours::query();
        $tours->where("vendorId = :vendor_id:");
        $modelBind['vendor_id'] = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        if (count($modelBind) > 0) {
            $tours->bind($modelBind);
        }
        $tours->order("vendorTourId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $tours->execute(),
            "page" => $page,
        ));

        $this->tag->setTitle('Promotional Discount');
        $this->view->pager = $pager;
    }

    /**
     * Action for tour reviews
     * @param type $tourId
     * @param type $page
     * @return boolean
     */
    public function reviewsAction($page = 1, $tourId = '') {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }

        $tour = null;
        if ($tourId != '') {
            $tour = \Tourpage\Models\Tours::findFirstByTourId($tourId);
            if (!$tour) {
                return FALSE;
            }
        }

        if ($this->request->isPost()) {
            $reviewIds = $this->request->getPost('rvt');
            $reviewAction = $this->request->getPost('review_action');
            if (count($reviewIds) > 0) {
                foreach ($reviewIds as $reviewId) {
                    $review = \Tourpage\Models\ToursReview::findFirst($reviewId);
                    if ($review && $review->count() > 0) {
                        switch ($reviewAction) {
                            case 'active':
                                $review->reviewStatus = \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE;
                                $review->save();
                                break;
                            case 'inactive':
                                $review->reviewStatus = \Tourpage\Models\ToursReview::INACTIVE_STATUS_CODE;
                                $review->save();
                                break;
                            case 'remove':
                                $review->removeData();
                                break;
                        }
                    }
                }
            }
            $this->flash->success('Selected action has been applied on to reviews.');
            $this->response->redirect($this->router->getRewriteUri());
        }
        $modelBind = [];
        $reviews = \Tourpage\Models\ToursReview::query();
        $reviews->where("vendorId = :vendor_id:");
        $modelBind['vendor_id'] = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        if ($tourId != '') {
            $reviews->andWhere("tourId = :tour_id:");
            $modelBind['tour_id'] = $tourId;
        }
        if (count($modelBind) > 0) {
            $reviews->bind($modelBind);
        }
        $reviews->order("reviewId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $reviews->execute(),
            "page" => $page,
        ));
        if ($tourId != '') {
            $pager->setUriPattern('/vendor/tours/reviews/{page}/' . $tour->tourId);
        }
        $this->assets->collection('header')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('footer')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->setTitle('Reviews' . ($tour ? ' for ' . $tour->tourTitle : ''));
        $this->view->tour = $tour;
        $this->view->pager = $pager;
    }

    /**
     * Action for Tour Repoert
     * Report is for how many booking has been placed
     * total amount book, how many person or group booked on
     * a perticular day.
     * @param string $tourId
     */
    public function reportAction($tourId = '') {
        if ($tourId != '') {
            $tour = \Tourpage\Models\Tours::findFirstByTourId($tourId);
            if (!$tour) {
                return FALSE;
            }
        } else {
            return FALSE;
        }
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        $modelBind = $bookinDates = [];
        $bookingQuery = \Tourpage\Models\BookingTours::query();
        $bookingQuery->where("vendorId = :vendor_id:");
        $bookingQuery->andWhere("tourId = :tour_id:");
        $modelBind['vendor_id'] = $vendorId;
        $modelBind['tour_id'] = $tourId;
        if (count($modelBind) > 0) {
            $bookingQuery->bind($modelBind);
        }
        $bookingQuery->order("bookingId DESC");
        $bookings = $bookingQuery->execute();
        $totalBookingAmount = $totalHeadCount = 0;
        if ($bookings && $bookings->count() > 0) {
            foreach ($bookings as $booking) {
                $totalBookingAmount += $booking->getBooking()->getTotalAmountByVendor($vendorId);
                $totalHeadCount += $booking->headCount;
                if ($tour->tourDuration->lengthType != \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE) {
                    if (!isset($bookinDates[$booking->departureOn])) {
                        $bookinDates[$booking->departureOn] = ['headCount' => 0];
                        if ($tour->tourPrice->data->priceType == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE) {
                            if (!isset($bookinDates[$booking->departureOn]['group'])) {
                                $bookinDates[$booking->departureOn]['group'] = [];
                                foreach (\Tourpage\Helpers\Utils::getVar('config_tour_age_group') as $groupKey => $groupTitle) {
                                    $bookinDates[$booking->departureOn]['group'][$groupKey] = [
                                        'title' => $groupTitle,
                                        'count' => 0
                                    ];
                                }
                            }
                        }
                        if (!empty($booking->arivalOn)) {
                            $bookinDates[$booking->departureOn]['to'] = $booking->arivalOn;
                        }
                    }
                    $bookinDates[$booking->departureOn]['headCount'] += $booking->headCount;
                    if (isset($booking->data->headCount)) {
                        foreach ($booking->data->headCount as $ag => $hc) {
                            $bookinDates[$booking->departureOn]['group'][$ag]['count'] += $hc['count'];
                        }
                    }
                } else {
                    if (!isset($bookinDates['headCount'])) {
                        $bookinDates['headCount'] = 0;
                    }
                    if ($tour->tourPrice->data->priceType == \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE) {
                        if (!isset($bookinDates['group'])) {
                            $bookinDates['group'] = [];
                            foreach (\Tourpage\Helpers\Utils::getVar('config_tour_age_group') as $groupKey => $groupTitle) {
                                $bookinDates['group'][$groupKey] = [
                                    'title' => $groupTitle,
                                    'count' => 0
                                ];
                            }
                        }
                    }
                    $bookinDates['headCount'] += $booking->headCount;
                    if (isset($booking->data->headCount)) {
                        foreach ($booking->data->headCount as $ag => $hc) {
                            $bookinDates['group'][$ag]['count'] += $hc['count'];
                        }
                    }
                }
            }
        }

        $this->tag->setTitle('Tour Booking Report');
        $this->view->tour = $tour;
        $this->view->bookings = $bookings;
        $this->view->totalBookingAmount = $totalBookingAmount;
        $this->view->totalHeadCount = $totalHeadCount;
        $this->view->bookinDates = $bookinDates;
    }

public function displayAction($page = 1,$id=''){
	
	}






}
