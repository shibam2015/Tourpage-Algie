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

namespace Multiple\Backend\Controllers;

/**
 * Class Product Controller
 * @author amit
 */
class ToursController extends BackendController {

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
        if ($this->request->isPost()) {
            $queryString = '';
            $tourName = $this->request->getPost('tn');
            $tourOperator = $this->request->getPost('to');
            $tourBookingStatus = $this->request->getPost('bs');
            $tourStatus = $this->request->getPost('s');
            $redirectTo = $this->url->getBaseUri() . '/admin/tours';
            if ($tourName != '') {
                $queryString .= 'tn=' . $tourName . '&';
            }
            if ($tourOperator != '') {
                if ($tourOperator != '[all]') {
                    $queryString .= 'to=' . $tourOperator . '&';
                }
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

        $tours = \Tourpage\Models\Tours::query();
        if ($this->request->hasQuery('tn')) {
            $defaultValues['tn'] = $this->request->getQuery('tn');
            $modelBind['title'] = "%" . $defaultValues['tn'] . "%";
            $tours->andWhere("tourTitle LIKE :title:");
        }
        if ($this->request->hasQuery('to')) {
            $defaultValues['to'] = $this->request->getQuery('to');
            $modelBind['operator'] = $defaultValues['to'];
            $tours->join('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\Tours.tourId AND vt.vendorId = :operator:', 'vt');
        }
        if ($this->request->hasQuery('bs')) {
            $defaultValues['bs'] = $this->request->getQuery('bs');
            $modelBind['booking'] = $defaultValues['bs'];
            $tours->andWhere("tourBookingStatus = :booking:");
        }
        if ($this->request->hasQuery('s')) {
            $defaultValues['s'] = $this->request->getQuery('s');
            $modelBind['status'] = $defaultValues['s'];
            $tours->join('\Tourpage\Models\VendorsTours', 'vts.tourId = \Tourpage\Models\Tours.tourId AND vts.status = :status:', 'vts');
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $tours->bind($modelBind);
        }
        $tours->order("tourCreatedOn DESC");
        $this->assets->collection('headerCss')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('headerJs')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->prependTitle('Tours');
        $vendors = \Tourpage\Models\Vendors::find();
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $tours->execute(),
            "page" => $page
        ));
        $this->view->defaultValues = $defaultValues;
        $this->view->pager = $pager;
        $this->view->vendors = $vendors;
    }

    /**
     * Action for modify vendor tour
     * @param string $tourId
     * @return boolean
     */
    public function editAction($tourId = '') {
        $this->tag->setTitle('Update Tour');
        $tour = \Tourpage\Models\Tours::findFirstByTourId($tourId);
        if (!$tour) {
            return false;
        }
        $tourForm = new \Multiple\Backend\Forms\TourManageForm($tour, array('edit' => true));
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
                            $tourPrice = \Tourpage\Models\ToursPrice::findFirst(array(
                                        'tourId = :tour_id:',
                                        'bind' => array('tour_id' => $tour->tourId),
                            ));
                            if (!$tourPrice) {
                                $tourPrice = new \Tourpage\Models\ToursPrice();
                            }
                            $tourPrice->tourId = $tour->tourId;
                            $tourPrice->priceData = serialize($priceData);
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
                        if ($this->request->getPost('tour_option')) {
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
                        }

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
                    } else {
                        foreach ($tour->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }

                    $queryString = '';
                    if ($submitType === 'Submit') {
                        $this->flash->success("Tour has been updated successfuly.");
                        //$this->response->redirect($this->router->getRewriteUri());
                        $this->response->redirect('/admin/tours');
                    }
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
     * Action for remove vendor tour
     * @param string $tourId
     * @return boolean
     */
    public function removeAction($tourId) {
        if (!$tourId) {
            return false;
        }
        $tour = \Tourpage\Models\Tours::findFirstByTourId($tourId);
		//$tour = \Tourpage\Models\Tours::findFirstByTourId("tourId='".$tourId."'");
        if (!$tour) {
            return false;
        }
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if ($tour->removeData()) {
                    $this->flash->success("Tours has been removed successfuly.");
                    $this->response->redirect('/admin/tours');
                }
            }
        }
        $this->tag->setTitle('Remove Tour');
        $this->view->tour = $tour;
    }

    /**
     * Validating tour add form element
     * @return boolean
     */
    private function validateTourForm($options = null) {
        $errors = [];
        $valid = false;
        if (!$this->request->getPost('tour_category')) {
            $errors['error_tour_category'] = "Tour Category is require";
        }

        if (!isset($options['edit']) || !$options['edit']) {
            $tour_images = $this->request->getPost('tour_image');
            if (!isset($tour_images['name']) || count($tour_images['name']) == 0) {
                $errors['error_tour_image'] = "Tour image is require";
            }
        }

        if (count($errors) > 0) {
            foreach ($errors as $errors) {
                $this->flash->error($errors);
            }
        } else {
            $valid = true;
        }
        return $valid;
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
        } else {
            return FALSE;
        }


        $modelBind = [];
        $reviews = \Tourpage\Models\ToursReview::query();
        $reviews->where("tourId = :tour_id:");
        $modelBind['tour_id'] = $tourId;
        if (count($modelBind) > 0) {
            $reviews->bind($modelBind);
        }
        $reviews->order("reviewId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $reviews->execute(),
            "page" => $page,
        ));
        if ($tourId != '') {
            $pager->setUriPattern('/admin/tours/reviews/{page}/' . $tour->tourId);
        }
        $this->assets->collection('headerCss')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('headerJs')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->setTitle('Reviews' . ($tour ? ' for ' . $tour->tourTitle : ''));
        $this->view->tour = $tour;
        $this->view->pager = $pager;
    }

    /**
     * Action for list of all category
     */
    public function categoryAction($page = 1) {
      //  var_dump($pager);die();
	    if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
		
        $this->tag->prependTitle('Categories');
		$categoryTour = \Tourpage\Models\CategoryTour::query();
		$categoryTour->where("categoryParentId < 1");
		$categoryTour->order("categoryTitle ASC");
		$pager = new \Tourpage\Library\Pager(array(
            "data" => $categoryTour->execute(),
            "page" => $page,
        ));
		$this->view->categoryCount = \Tourpage\Models\CategoryTour::find();
        $this->view->pager = $pager;
    }

    /**
     * Action for add new category
     */
    public function addCategoryAction() {
		
        $this->tag->prependTitle('New Category');
        $categoryForm = new \Multiple\Backend\Forms\CategoryForm();
        
	    if ($this->request->isPost()) {
            if ($categoryForm->isValid($this->request->getPost())) {
                $category = new \Tourpage\Models\CategoryTour();
                $category->categoryParentId = 0;
                if ($this->request->getPost('category_parent')) {
                    $category->categoryParentId = $this->request->getPost('category_parent');
                }
                $category->categoryTitle = $this->request->getPost('category_title');
                $category->categorySlug = \Tourpage\Helpers\Utils::slug($category->categoryTitle);
                $category->categoryStatus = $this->request->getPost('category_status');
                $category->categoryCreatedOn = \Tourpage\Helpers\Utils::currentDate();
                  if ( $category->save()) {
                    $this->flash->success("Category has been added successfuly.");
                    //$this->response->redirect($this->router->getRewriteUri());
                    $this->response->redirect('/admin/tours/category');
				} else {
                    foreach ( $category->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
				  
				
				}
            }
        }
        $this->view->form = $categoryForm;
        $this->view->formType = 'new';
        $this->view->categoryId = 0;
    }

    /**
     * Action for Category management like edit/remove
     * @param string $action edit|remove
     * @param int $categoryId Category unique id
     */
    public function manageCategoryAction($action='', $categoryId='') {
        if (!in_array($action, array('edit', 'remove'))) {
            return false;
        }
        if (!preg_match_all('/[0-9]+/', $categoryId, $matches)) {
            return false;
        }
       
	    switch ($action) {
            case 'edit':
                $this->tag->prependTitle('Update Category');
				 
                $category = \Tourpage\Models\CategoryTour::findFirst($categoryId);
				 //$category = \Tourpage\Models\CategoryTour::findFirst("categoryId='".$categoryId."'");
                $categoryForm = new \Multiple\Backend\Forms\CategoryForm($category, array('edit' => TRUE));
                if ($this->request->isPost()) {
                    if ($categoryForm->isValid($this->request->getPost())) {
                        $category->categoryParentId = 0;
                        if ($this->request->getPost('category_parent')) {
                            $category->categoryParentId = $this->request->getPost('category_parent');
                        }
                        $category->categoryTitle = $this->request->getPost('category_title');
                        $category->categorySlug = \Tourpage\Helpers\Utils::slug($category->categoryTitle);
                        $category->categoryStatus = $this->request->getPost('category_status');
                        if ($category->save()) {
                            $this->flash->success("Category has been updated successfuly.");
                            //$this->response->redirect($this->router->getRewriteUri());
                            $this->response->redirect('/admin/tours/category');
                        } else {
                            foreach ($category->getMessages() as $message) {
                                $this->flash->error((string) $message);
                            }
                        }
                    }
                }
                $this->view->form = $categoryForm;
                $this->view->formType = 'edit';
                $this->view->categoryId = $categoryId;
                $this->view->pick("tours/addcategory");
                break;
            case 'remove':
			    $category = \Tourpage\Models\CategoryTour::findFirst($categoryId); 
				if (!$category) {
                    return false;
                }
				
                $this->tag->prependTitle('Remove Category');
				if ($this->request->isPost()) {
                    $key = $this->request->getPost('key');
                    if ($key == md5('confirm')) {
                        if (!$category->hasChild()) {
                            if ($category->toursCategory->count() == 0) {
                                if ($category->delete()) {
                                    $this->flash->success("Category has been removed successfuly.");
                                    $this->response->redirect('/admin/tours/category');
                                }
                            } else {
                                $this->flash->error("Category has one or more tours. Please remove or tranfer those to other category first to remove this category.");
                            }
                        } else {
                            $this->flash->error("Category has one or more sub-categories. Please remove or tranfer those to other category first to remove this category.");
                        }
                    }
                }
                $this->view->category = $category;
                break;
        }
    }

    /**
     * Action for list all tour description fields
     */
    public function descfieldsAction() {
        $this->tag->prependTitle('Tour Description Fields');
        $attributeKeys = \Tourpage\Models\ToursAttributeKeys::find(array(
                    'order' => 'keyId DESC'
        ));
        $this->view->attributeKeys = $attributeKeys;
    }

    /**
     * Add new tour description field
     */
    public function adddescfieldsAction() {
        $attributeForm = new \Multiple\Backend\Forms\TourAttributeKeyForm();
        if ($this->request->isPost()) {
            if ($attributeForm->isValid($this->request->getPost())) {
                $attributeKey = new \Tourpage\Models\ToursAttributeKeys();
                $attributeKey->keyName = $this->request->getPost('key_name', array('string', 'striptags'));
                $attributeKey->keyStatus = $this->request->getPost('key_status');
                if ($attributeKey->save()) {
                    $this->flash->success("Tour Description Fields has been added successfuly.");
                    $this->response->redirect('/admin/tours/descfields');
                } else {
                    foreach ($attributeKey->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->tag->prependTitle('New Tour Description Field');
        $this->view->form = $attributeForm;
        $this->view->formType = 'new';
        $this->view->pick("tours/descfieldsform");
    }

    /**
     * Edit tour description field
     * @param int $fieldId
     * @return boolean
     */
    public function editdescfieldsAction($fieldId = '') {
        if (!preg_match_all('/[0-9]+/', $fieldId, $matches)) {
            return false;
        }
        $attributeKey = \Tourpage\Models\ToursAttributeKeys::findFirst($fieldId);
        if (!$attributeKey) {
            return FALSE;
        }
        $attributeForm = new \Multiple\Backend\Forms\TourAttributeKeyForm($attributeKey, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($attributeForm->isValid($this->request->getPost())) {
                $attributeKey->keyName = $this->request->getPost('key_name', array('string', 'striptags'));
                $attributeKey->keyStatus = $this->request->getPost('key_status');
                if ($attributeKey->save()) {
                    $this->flash->success("Tour Description Fields has been updated successfuly.");
                    $this->response->redirect('/admin/tours/descfields');
                } else {
                    foreach ($attributeKey->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->tag->prependTitle('Edit Tour Description Field');
        $this->view->form = $attributeForm;
        $this->view->formType = 'edit';
        $this->view->pick("tours/descfieldsform");
    }

    /**
     * Remove tour description field
     * @param type $fieldId
     * @return boolean
     */
    public function removedescfieldsAction($fieldId = '') {
        if (!preg_match_all('/[0-9]+/', $fieldId, $matches)) {
            return false;
        }
        $attributeKey = \Tourpage\Models\ToursAttributeKeys::findFirst($fieldId);
        if (!$attributeKey) {
            return FALSE;
        }
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if ($attributeKey->removeData()) {
                    $this->flash->success("Tour Description Fields has been removed successfuly.");
                    $this->response->redirect('/admin/tours/descfields');
                }
            }
        }
        $this->tag->prependTitle('Remove Tour Description Field');
        $this->view->attributeKey = $attributeKey;
    }

    public function keyhighlightAction() {
        $this->tag->prependTitle('Key highlights');
        $keyHighlights = \Tourpage\Models\KeyHighlight::query();
        $keyHighlights->order("keyhighlightId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $keyHighlights->execute(),
            "page" => $page
        ));
        $this->view->pager = $pager;
    }

    public function managekeyhighlightAction($keyhighlightId = 0, $action = 'add') {
        if ($keyhighlightId == 0 && $action == 'add') {
            $this->tag->prependTitle('Add New Key highlights');
            $keyhightlightForm = new \Multiple\Backend\Forms\KeyHIghlightForm();
            $keyhighlightIconForm = new \Tourpage\Forms\CFilesForm(NULL, array(
                'name' => 'key_icon',
                'label' => 'Key Highlight Icon',
                'messageEmpty' => 'Key Icon is Required',
                'maxResolution' => 0,
                'maxSize' => 0
            ));
            if ($this->request->isPost()) {
                $form = $keyhightlightForm->isValid($this->request->getPost());
                $icon = $keyhighlightIconForm->isValid($_FILES);
                if ($form && $icon) {
                    if ($this->request->hasFiles(TRUE)) {
                        $filesData = $this->request->getUploadedFiles(TRUE);
                        $file = $filesData[0];
                        $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/khicon/';
                        if (!file_exists($baseLocation)) {
                            mkdir($baseLocation);
                            chmod($baseLocation, 0777);
                        }
                        $imageName = time() . md5($file->getName() . rand(0, 1000)) . '.' . $file->getExtension();
                        if ($file->moveTo($baseLocation . $imageName)) {
                            $resizeFile = new \Phalcon\Image\Adapter\GD($baseLocation . $imageName);
                            $resizeFile->resize(45, 45);
                            if ($resizeFile->save($baseLocation . $imageName)) {
                                $keyHighlight = new \Tourpage\Models\KeyHighlight();
                                $keyHighlight->keyhighlightTitle = $this->request->getPost('key_highlight_title', array('string', 'striptags'));
                                $keyHighlight->keyhighlightIcon = $imageName;
                                $keyHighlight->keyhighlightStatus = $this->request->getPost('key_status');
                                $keyHighlight->save();
                                $this->flash->success('Key highlight has been saved successfully');
                            }
                        } else {
                            $this->flash->error((string) $file->getError());
                        }
                    }
                    $this->response->redirect('/admin/tours/keyhighlight');
                }
            }
            $this->view->formType = $action;
            $this->view->form = $keyhightlightForm;
            $this->view->icon = $keyhighlightIconForm;
            $this->view->pick('tours/keyhighlightform');
        } else {
            switch ($action) {
                case 'edit':
                    if (!preg_match_all('/[0-9]+/', $keyhighlightId, $matches)) {
                        return false;
                    }
                    $keyHighlight = \Tourpage\Models\KeyHighlight::findFirst($keyhighlightId);
                    if (!$keyHighlight) {
                        return FALSE;
                    }
                    $keyhightlightForm = new \Multiple\Backend\Forms\KeyHIghlightForm($keyHighlight, array('edit' => TRUE));
                    $keyhighlightIconForm = new \Tourpage\Forms\CFilesForm(NULL, array(
                        'name' => 'key_icon',
                        'label' => 'Key Highlight Icon',
                        'allowEmpty' => TRUE,
                        'maxResolution' => 0,
                        'maxSize' => 0
                    ));
                    if ($this->request->isPost()) {
                        $form = $keyhightlightForm->isValid($this->request->getPost());
                        $icon = $keyhighlightIconForm->isValid($_FILES);
                        if ($form && $icon) {
                            if ($this->request->hasFiles(TRUE)) {
                                $filesData = $this->request->getUploadedFiles(TRUE);
                                $file = $filesData[0];
                                $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/khicon/';
                                if (!file_exists($baseLocation)) {
                                    mkdir($baseLocation);
                                    chmod($baseLocation, 0777);
                                }
                                $imageName = time() . md5($file->getName() . rand(0, 1000)) . '.' . $file->getExtension();
                                if ($file->moveTo($baseLocation . $imageName)) {
                                    $resizeFile = new \Phalcon\Image\Adapter\GD($baseLocation . $imageName);
                                    $resizeFile->resize(45, 45);
                                    if ($resizeFile->save($baseLocation . $imageName)) {
                                        if (!empty($keyHighlight->keyhighlightIcon)) {
                                            if (file_exists($baseLocation . $keyHighlight->keyhighlightIcon)) {
                                                unlink($baseLocation . $keyHighlight->keyhighlightIcon);
                                            }
                                        }
                                        $keyHighlight->keyhighlightIcon = $imageName;
                                    }
                                } else {
                                    $this->flash->error((string) $file->getError());
                                }
                            }
                            $keyHighlight->keyhighlightTitle = $this->request->getPost('key_highlight_title', array('string', 'striptags'));
                            $keyHighlight->keyhighlightStatus = $this->request->getPost('key_status');
                            $keyHighlight->save();
                            $this->flash->success('Key highlight has been saved successfully');
                            $this->response->redirect('/admin/tours/keyhighlight');
                        }
                    }
                    $this->tag->prependTitle('Edit Key highlights');
                    $this->view->formType = $action;
                    $this->view->keyHighlight = $keyHighlight;
                    $this->view->form = $keyhightlightForm;
                    $this->view->icon = $keyhighlightIconForm;
                    $this->view->pick('tours/keyhighlightform');
                    $this->view->pick('tours/keyhighlightform');
                    break;
                case 'remove':
                    if (!preg_match_all('/[0-9]+/', $keyhighlightId, $matches)) {
                        return false;
                    }
                    $keyHighlight = \Tourpage\Models\KeyHighlight::findFirst($keyhighlightId);
                    if (!$keyHighlight) {
                        return FALSE;
                    }
                    $keyHighlight->removeData();
                    $this->flash->success('Key highlight has been removed successfully');
                    $this->response->redirect('/admin/tours/keyhighlight');
                    break;
            }
        }
    }

}
