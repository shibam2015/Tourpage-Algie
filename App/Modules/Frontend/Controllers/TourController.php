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
 * Controller for Tour
 * @author amit
 */
class TourController extends FrontendController {

    /**
     * Initializing Tour Controller
     * Initializing layout and GUI elements
     */
    public function initialize() {
        parent::initialize();
        //$this->initializeLayout('store');
    }

    /**
     * Index Action for Tour Details
     * @param int $vendorId
     * @param string|mix $tourId
     */
    public function indexAction(/* $vendorId = 0, */$tourId = '') {
        /* if ($vendorId <= 0) {
          return FALSE;
          } */

        if ($tourId == '') {
            return FALSE;
        }

        $tourCriteria = array(
            'conditions' => 'tourId = :tour_id:',
            'bind' => array('tour_id' => $tourId),
        );
        $tour = \Tourpage\Models\Tours::findFirst($tourCriteria);
        if (!$tour) {
            return false;
        }
        if ($this->request->isPost()) {
            $formAction = $this->request->getPost('form_action');
            if ($formAction == 'booking') {
                $journeyStart = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('jstd'));
                if ($tour->tourDuration->lengthType == \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE) {
                    $journeyEnd = \Tourpage\Helpers\Utils::formatDatepickerToMySql($this->request->getPost('jedd'));
                }
                if ($tour->tourDuration->lengthType == \Tourpage\Models\Tours::LENGTH_HOURLY) {
                    $timeSlot = $this->request->getPost('tour_time_slot');
                }
                $tourOptions = $this->request->getPost('tour_opt');
                $totalAmount = $this->request->getPost('total_amount');
                $cartData = [];
                $cartKey = $tourId/* .'-' . time()*/;
                $cartData[$cartKey]['key'] = $cartKey;
                $cartData[$cartKey]['tour_id'] = $tourId;
                //$cartData[$cartKey]['vendor_id'] = $vendorId;
                $cartData[$cartKey]['vendor_id'] = $tour->tourVendor->vendorId;
                $cartData[$cartKey]['departure_on'] = $journeyStart;
                if (isset($journeyEnd)) {
                    $cartData[$cartKey]['arival_on'] = $journeyEnd;
                }
                if (isset($timeSlot)) {
                    $cartData[$cartKey]['time_slot'] = unserialize(base64_decode($timeSlot));
                }
                $fullAmount = 0;
                switch ($tour->tourPrice->data->priceType) {
                    case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE:
                        $cartData[$cartKey]['head_count'] = [];
                        $headCount = $this->request->getPost('head_count');
                        if (count($headCount) > 0) {
                            foreach ($headCount as $ag => $c) {
                                if ($c > 0) {
                                    if (!isset($cartData[$cartKey]['head_count'][$ag])) {
                                        $cartData[$cartKey]['head_count'][$ag] = [];
                                    }
                                    $cartData[$cartKey]['head_count'][$ag] = [
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
                        $cartData[$cartKey]['group_head_count'] = $this->request->getPost('group_head_count');
                        $fullAmount += $tour->tourPrice->data->priceDefault;
                        break;
                }

                if (count($tourOptions) > 0) {
                    $cartData[$cartKey]['tour_opt'] = [];
                    foreach ($tourOptions as $optId => $optAmt) {
                        if (!isset($cartData[$cartKey]['tour_opt'][$optId])) {
                            $cartData[$cartKey]['tour_opt'][$optId] = [];
                        }
                        $optMdl = \Tourpage\Models\ToursOptions::findFirst($optId);
                        if ($optMdl) {
                            $cartData[$cartKey]['tour_opt'][$optId] = [
                                'name' => $optMdl->optionName,
                                'type' => $optMdl->optionPriceType,
                                'unit_price' => $optAmt,
                                'amount' => 0
                            ];
                            switch ($optMdl->optionPriceType) {
                                case \Tourpage\Models\ToursOptions::OPTION_PER_PERSON_CODE:
                                    switch ($tour->tourPrice->data->priceType) {
                                        case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE:
                                            $cartData[$cartKey]['tour_opt'][$optId]['head_count'] = 0;
                                            foreach ($headCount as $ag => $c) {
                                                $fullAmount += $optAmt * $c;
                                                $cartData[$cartKey]['tour_opt'][$optId]['head_count'] += $c;
                                                $cartData[$cartKey]['tour_opt'][$optId]['amount'] += $optAmt * $c;
                                            }
                                            break;
                                        case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE:
                                            $c = $cartData[$cartKey]['group_head_count'];
                                            $fullAmount += $optAmt * $c;
                                            $cartData[$cartKey]['tour_opt'][$optId]['head_count'] = $c;
                                            $cartData[$cartKey]['tour_opt'][$optId]['amount'] = $optAmt * $c;
                                            break;
                                    }
                                    break;
                                case \Tourpage\Models\ToursOptions::OPTION_PER_GROUP_CODE:
                                    $fullAmount += $optAmt;
                                    $cartData[$cartKey]['tour_opt'][$optId]['amount'] += $optAmt;
                                    break;
                            }
                        }
                    }
                }
                $cartData[$cartKey]['original_amount'] = $fullAmount;
                $cartData[$cartKey]['final_amount'] = $totalAmount;
                if ($tour->tourPrice->hasDiscount()) {
                    $cartData[$cartKey]['discount'] = $tour->tourPrice->data->discount->price;
                    $cartData[$cartKey]['save_amount'] = (($fullAmount * $tour->tourPrice->data->discount->price) / 100);
                    //$fullAmount = $fullAmount - (($fullAmount * $tour->tourPrice->data->discount->price) / 100);
                }
                if ($tour->tourPrice->hasMultiPurchesDiscount()) {
                    $hdCnt = 0;
                    switch ($tour->tourPrice->data->priceType) {
                        case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE:
                            if (isset($cartData[$cartKey]['head_count']) > 0) {
                                foreach ($cartData[$cartKey]['head_count'] as $ag => $c) {
                                    if (isset($c['count']) && $c['count'] > 0) {
                                        $hdCnt += $c['count'];
                                    }
                                }
                            }
                            break;
                        case \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE:
                            if (isset($cartData[$cartKey]['group_head_count']) > 0) {
                                $hdCnt = $cartData[$cartKey]['group_head_count'];
                            }
                            break;
                    }
                    if ($hdCnt >= $tour->tourPrice->data->discount->multiplePurchase->count) {
                        $cartData[$cartKey]['multi_purches_discount'] = $tour->tourPrice->data->discount->multiplePurchase->percentage;
                        $cartData[$cartKey]['multi_purches_save_amount'] = (($fullAmount * $tour->tourPrice->data->discount->multiplePurchase->percentage) / 100);
                    }
                }
                //$cartData[$tourId]['finall_amount'] = $fullAmount;
                //\Tourpage\Helpers\Utils::printArray($cartData);
                $this->cart->addItem($cartData);
                $this->flash->success("Tour has been added to your cart.");
                $this->response->redirect('/cart');
            }
            if ($formAction == 'review') {
                
            }
        }

        if ($tour->tourVendor->status == \Tourpage\Models\VendorsTours::INACTIVE_STATUS_CODE) {
            $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            echo ("<section class=\"bodysectionpdp\"><div class=\"pdp\" style=\" margin: 0 auto;max-width: 1000px;\"><h1 class=\"alert alert-info\" style=\"text-align: center;\">This tour is either inactive or has been removed by the operator.</h2></div></section>");
        }

        $tourCategories = [];
        if ($tour->tourCategories->count() > 0) {
            foreach ($tour->tourCategories as $category) {
                $tourCategories[] = $category->categoryId;
            }
        }
        $otherToursByVendor = \Tourpage\Models\VendorsTours::find(array(
                    'conditions' => 'vendorId = :vendorId: AND tourId != :tourId: AND status = :status:',
                    'bind' => array(
                        'vendorId' => $tour->tourVendor->vendorId,
                        'tourId' => $tour->tourId,
                        'status' => \Tourpage\Models\Tours::ACTIVE_STATUS_CODE
                    ),
                    'order' => 'vendorTourId DESC',
                    'limit' => \Tourpage\Helpers\Utils::DEFAULT_PAGE_SIZE
        ));

        $otherToursByCategoryQuery = \Tourpage\Models\ToursCategory::query();
        $otherToursByCategoryQuery->where('\Tourpage\Models\ToursCategory.categoryId IN (:categories:)');
        $otherToursByCategoryQuery->andWhere('\Tourpage\Models\ToursCategory.tourId != :tourId:');
        $otherToursByCategoryQuery->leftJoin('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\ToursCategory.tourId', 'vt');
        $otherToursByCategoryQuery->andWhere('vt.status = :status:');
        $otherToursByCategoryQuery->bind(array(
            'categories' => implode(',', $tourCategories),
            'tourId' => $tour->tourId,
            'status' => \Tourpage\Models\Tours::ACTIVE_STATUS_CODE
        ));
        $otherToursByCategoryQuery->orderBy('\Tourpage\Models\ToursCategory.tourCategoryId DESC');
        $otherToursByCategoryQuery->limit(3);
        $otherToursByCategory = $otherToursByCategoryQuery->execute();

        $reviews = \Tourpage\Models\ToursReview::find(array(
                    'conditions' => 'tourId = :tour_id: AND reviewStatus = :status:',
                    'bind' => array('tour_id' => $tourId, 'status' => \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE),
                    'order' => 'reviewId DESC',
                    'limit' => 3
        ));
        
        $bind = $disablDates = $tourTimeSlots = [];
        if ($tour->tourCapacity > 0) {
            $bookingToursQuery = \Tourpage\Models\BookingTours::query();
            $bookingToursQuery->where('tourId = :tour_id:');
            $bind['tour_id'] = $tourId;
            if (count($bind) > 0) {
                $bookingToursQuery->bind($bind);
            }
            $bookingTours = $bookingToursQuery->execute();
            if ($bookingTours && $bookingTours->count() > 0) {
                if ($tour->tourDuration->lengthType == \Tourpage\Models\Tours::LENGTH_HOURLY) {
                    foreach ($tour->tourDuration->times as $time) {
                        $key = $time->start->hours . $time->start->minutes . $time->end->hours . $time->end->minutes;
                        $tourTimeSlots[$key] = $time;
                    }
                    foreach ($bookingTours as $bookingTour) {
                        $timeSlotKey = $bookingTour->data->timeSlot->start->hours . $bookingTour->data->timeSlot->start->minutes . $bookingTour->data->timeSlot->end->hours . $bookingTour->data->timeSlot->end->minutes;
                    }
                } else {
                    foreach ($bookingTours as $bookingTour) {
                        if (!isset($disablDates[$bookingTour->departureOn])) {
                            $disablDates[$bookingTour->departureOn] = 0;
                        }
                        $disablDates[$bookingTour->departureOn] += $bookingTour->headCount;
                    }
                    if (count($disablDates) > 0) {
                        foreach ($disablDates as $disablDate => $head_count) {
                            if ($head_count < $tour->tourCapacity) {
                                unset($disablDates[$disablDate]);
                            }
                        }
                    }
                    if (count($disablDates) > 0) {
                        $disablDateArrayKeys = array_keys($disablDates);
                        $disablDates = [];
                        foreach ($disablDateArrayKeys as $dateKey) {
                            $disablDates[] = '"' . \Tourpage\Helpers\Utils::formatMySqlToDatepicker($dateKey) . '"';
                        }
                    }
                }
            }
        }

        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/new_style.css');
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/new_media.css');
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/font-awesome.min.css');				
		$this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
		$this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/BeatPicker.min.css');		
        $this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
		$this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/BeatPicker.min.js');
		$this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/image-scale.js');
		$this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/script.js');		
        //$this->assets->collection('header_js')->addJs(COMMON_DIR . 'js/ckeditor/ckeditor.js');
        $this->initializeLayout('store');
        $this->tag->setKeywords($tour->tourKeyword);
        $this->tag->setTitle($tour->tourTitle);
        $this->view->storeVendor = $tour->tourVendor->vendor;
        $this->view->otherTours = (object) array(
                    'fromVendor' => $otherToursByVendor,
                    'fromCategory' => $otherToursByCategory,
        );
        $this->view->ageGroups = \Tourpage\Helpers\Utils::getVar('config_tour_age_group');
        $this->view->disablDates = $disablDates;
        $this->view->tour = $tour;
        $this->view->reviews = $reviews;
    }

    /**
     * Action for review list of tour
     * @param string $tourId
     * @param int $page
     */
    public function reviewsAction(/* $vendorId = 0, */$tourId = '', $page = 1) {
        /* if ($vendorId <= 0) {
          return FALSE;
          } */
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }

        if ($tourId == '') {
            return FALSE;
        }
        $tourCriteria = array(
            'conditions' => 'tourId = :tour_id:',
            'bind' => array('tour_id' => $tourId),
        );
        $tour = \Tourpage\Models\Tours::findFirst($tourCriteria);
        if (!$tour) {
            return false;
        }
        $modelBind = [];
        $reviews = \Tourpage\Models\ToursReview::query();
        $reviews->where("tourId = :tour_id:");
        $reviews->andWhere("reviewStatus = :status:");
        $modelBind['tour_id'] = $tourId;
        $modelBind['status'] = \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE;
        if (count($modelBind) > 0) {
            $reviews->bind($modelBind);
        }
        $reviews->order("reviewId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $reviews->execute(),
            "page" => $page,
            'limit' => 20
        ));
        $pager->setUriPattern('/tour/reviews/' . $tour->tourId . '/{page}');
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->initializeLayout('store');
        $this->tag->setTitle($tour->tourTitle);
        $this->view->storeVendor = $tour->tourVendor->vendor;
        $this->view->tour = $tour;
        $this->view->pager = $pager;
    }

    /**
     * Preview action of tour
     * @param int $vendorId
     * @param string $tourId
     */
    /*public function previewAction(/* $vendorId = 0, *\/$tourId = '') {
        /* if ($vendorId <= 0) {
          return FALSE;
          } *\/

        if ($tourId == '') {
            return FALSE;
        }
        $tourCriteria = array(
            'conditions' => 'tourId = :tour_id:',
            'bind' => array('tour_id' => $tourId),
        );
        $tour = \Tourpage\Models\Tours::findFirst($tourCriteria);
        if (!$tour) {
            return false;
        }
        
        if ($tour->tourVendor->status == \Tourpage\Models\VendorsTours::INACTIVE_STATUS_CODE) {
            $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            echo ("<section class=\"bodysectionpdp\"><div class=\"pdp\" style=\" margin: 0 auto;max-width: 1000px;\"><h1 class=\"alert alert-info\" style=\"text-align: center;\">This tour is either inactive or has been removed by the operator.</h2></div></section>");
        }

        $tourCategories = [];
        if ($tour->tourCategories->count() > 0) {
            foreach ($tour->tourCategories as $category) {
                $tourCategories[] = $category->categoryId;
            }
        }
        $otherToursByVendor = \Tourpage\Models\VendorsTours::find(array(
                    'conditions' => 'vendorId = :vendorId: AND tourId != :tourId: AND status = :status:',
                    'bind' => array(
                        'vendorId' => $tour->tourVendor->vendorId,
                        'tourId' => $tour->tourId,
                        'status' => \Tourpage\Models\Tours::ACTIVE_STATUS_CODE
                    ),
                    'order' => 'vendorTourId DESC',
                    'limit' => \Tourpage\Helpers\Utils::DEFAULT_PAGE_SIZE
        ));

        $otherToursByCategoryQuery = \Tourpage\Models\ToursCategory::query();
        $otherToursByCategoryQuery->where('\Tourpage\Models\ToursCategory.categoryId IN (:categories:)');
        $otherToursByCategoryQuery->andWhere('\Tourpage\Models\ToursCategory.tourId != :tourId:');
        $otherToursByCategoryQuery->leftJoin('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\ToursCategory.tourId', 'vt');
        $otherToursByCategoryQuery->andWhere('vt.status = :status:');
        $otherToursByCategoryQuery->bind(array(
            'categories' => implode(',', $tourCategories),
            'tourId' => $tour->tourId,
            'status' => \Tourpage\Models\Tours::ACTIVE_STATUS_CODE
        ));
        $otherToursByCategoryQuery->orderBy('\Tourpage\Models\ToursCategory.tourCategoryId DESC');
        $otherToursByCategoryQuery->limit(\Tourpage\Helpers\Utils::DEFAULT_PAGE_SIZE);
        $otherToursByCategory = $otherToursByCategoryQuery->execute();
        $reviews = \Tourpage\Models\ToursReview::find(array(
                    'conditions' => 'tourId = :tour_id: AND reviewStatus = :status:',
                    'bind' => array('tour_id' => $tourId, 'status' => \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE),
                    'order' => 'reviewId DESC',
                    'limit' => 5
        ));
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->view->storeVendor = $tour->tourVendor->vendor;
        $this->view->otherTours = (object) array(
                    'fromVendor' => $otherToursByVendor,
                    'fromCategory' => $otherToursByCategory,
        );
        $this->view->ageGroups = \Tourpage\Helpers\Utils::getVar('config_tour_age_group');
        $this->view->tour = $tour;
        $this->view->reviews = $reviews;
        $this->view->pick('tour/index');
    }*/

    /**
     * Action for add tour to member favorite list
     * @param string $tourId
     */
    public function addtofavAction(/* $vendorId = 0, */$tourId = '') {
        /* if ($vendorId <= 0) {
          return FALSE;
          } */

        if ($tourId == '') {
            return FALSE;
        }
        $tourCriteria = array(
            'conditions' => 'tourId = :tour_id:',
            'bind' => array('tour_id' => $tourId),
        );
        $tour = \Tourpage\Models\Tours::findFirst($tourCriteria);
        if (!$tour) {
            return false;
        }
        $acturl = $this->request->getQuery('acturl');
        $redirectTo = $this->url->getBaseUri();
        if ($acturl) {
            $redirectTo = base64_decode($acturl);
        }
        if (!$this->member->isLoggedIn()) {
            $redirectTo = $this->url->get('/auth/login?_rt=' . base64_encode('/tour/addtofav/' . $tourId . '?acturl=' . $acturl));
        }
        if ($this->member->isLoggedIn()) {
            if (!$this->member->isFavoriteTour($tourId)) {
                $this->member->addToFavorite($tourId, 'tour');
                $this->flash->success("\"<em>" . $tour->tourTitle . "</em>\" has been added to your favorite list.");
            } else {
                $this->flash->notice("\"<em>" . $tour->tourTitle . "</em>\" already in your favorite list.");
            }
        }
        $this->response->redirect($redirectTo, true);
    }


}
