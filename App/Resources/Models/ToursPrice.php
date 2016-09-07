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

namespace Tourpage\Models;

/**
 * Model Products Price
 * @author amit
 */
class ToursPrice extends ApplicationModel {

    const DISCOUNT_STATUS_ON_GOING = 1;
    const DISCOUNT_STATUS_UP_COMMING = 2;
    const DISCOUNT_STATUS_EXPIRED = 3;
    const PRICE_TYPE_PER_PERSON_CODE = 'pp';
    const PRICE_TYPE_PER_GROUP_CODE = 'pg';
    const PRICE_TYPE_PER_PERSON_TEXT = 'Per Person';
    const PRICE_TYPE_PER_GROUP_TEXT = 'Per Group';

    /**
     * @var object Price Data
     */
    public $data;

    /**
     * @var mix Serialize Price Data
     */
    private $priceData;

    /**
     * Offer Data
     * @var object
     */
    private $offerData = null;

    /**
     * priceData setter
     */
    public function setPriceData($priceData)
    {
        $this->priceData = $priceData;
    }
    
    /**
     * Initializing Model Products Price
     */
    public function initialize() {
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
    }

    /**
     * manipulate variable data after fetch from database
     */
    public function afterFetch() {
        $priceData = $this->getPriceData();
        $this->data = new \stdClass();
        $this->data->priceGroup = isset($priceData['tour_price_group']) ? $priceData['tour_price_group'] : [];
        $this->data->priceDefault = isset($this->data->priceGroup['a']['price']) ? $this->data->priceGroup['a']['price'] : 0;
        $this->data->priceType = isset($priceData['tour_price_type']) ? $priceData['tour_price_type'] : ToursPrice::PRICE_TYPE_PER_PERSON_CODE;
        $this->data->priceTypeText = ToursPrice::PRICE_TYPE_PER_PERSON_TEXT;
        //$this->data->priceType = Tours::PRICE_TYPE_PER_PERSON_CODE;
        if ($this->data->priceType == ToursPrice::PRICE_TYPE_PER_GROUP_CODE) {
            $this->data->priceDefault = isset($priceData['tour_price']) ? $priceData['tour_price'] : 0;
            $this->data->priceTypeText = ToursPrice::PRICE_TYPE_PER_GROUP_TEXT;
        }
        //$this->data->priceForChild = isset($priceData['tour_price_for_child']) ? $priceData['tour_price_for_child'] : 0;
        $this->data->discount = new \stdClass();
        $this->data->discount->price = isset($priceData['discount']) ? (isset($priceData['discount']['price']) ? $priceData['discount']['price'] : 0) : 0;
        $this->data->discount->start = isset($priceData['discount']) ? (isset($priceData['discount']['start']) ? $priceData['discount']['start'] : '') : '';
        $this->data->discount->end = isset($priceData['discount']) ? (isset($priceData['discount']['end']) ? $priceData['discount']['end'] : '') : '';
        $this->data->offers = isset($priceData['tour_seasonal_offer']) ? $priceData['tour_seasonal_offer'] : [];

        $this->data->discount->multiplePurchase = new \stdClass();
        $this->data->discount->multiplePurchase->percentage = isset($priceData['discount']) ? (isset($priceData['discount']['mp_percentage']) ? $priceData['discount']['mp_percentage'] : 0) : 0;
        $this->data->discount->multiplePurchase->count = isset($priceData['discount']) ? (isset($priceData['discount']['mp_count']) ? $priceData['discount']['mp_count'] : 0) : 0;
    }

    /**
     * Get tour price data
     */
    public function getPriceData() {
        return unserialize($this->priceData);
    }

    /**
     * Determine this tour has discount
     * @return boolean
     */
    public function hasDiscount($data = FALSE, $promotion = FALSE) {
        $hasDiscount = FALSE;
        if ($this->data->discount->price > 0 && !empty($this->data->discount->start) && !empty($this->data->discount->end)) {
            if (!$data) {
                if ($this->discountStatus() === self::DISCOUNT_STATUS_ON_GOING) {
                    $hasDiscount = TRUE;
                }
            } else {
                if ($promotion) {
                    $startDate = strtotime($this->data->discount->start);
                    $endDate = strtotime($this->data->discount->end);
                    $currentDate = strtotime(\Tourpage\Helpers\Utils::currentDate());
                    if ($startDate <= $currentDate) {
                        if ($currentDate <= $endDate) {
                            $hasDiscount = TRUE;
                        }
                    }
                } else {
                    $hasDiscount = TRUE;
                }
            }
        }
        return $hasDiscount;
    }

    public function hasMultiPurchesDiscount() {
        $hasDiscount = FALSE;
        if ($this->data->discount->multiplePurchase->percentage > 0 && $this->data->discount->multiplePurchase->count > 0) {
            $hasDiscount = TRUE;
        }
        return $hasDiscount;
    }

    /**
     * Getting discount status for tour
     */
    public function discountStatus($statusText = FALSE) {
        $discountStatus = 0;
        $discountStatusText = '';
        if ($this->data->discount->price > 0 && !empty($this->data->discount->start) && !empty($this->data->discount->end)) {
            $startDate = strtotime($this->data->discount->start);
            $endDate = strtotime($this->data->discount->end);
            $currentDate = strtotime(\Tourpage\Helpers\Utils::currentDate());
            if ($currentDate >= $startDate) {
                if ($currentDate <= $endDate) {
                    $discountStatus = self::DISCOUNT_STATUS_ON_GOING;
                    $discountStatusText = 'on going';
                }
            }
            if ($currentDate < $startDate) {
                $discountStatus = self::DISCOUNT_STATUS_UP_COMMING;
                $discountStatusText = 'up comming';
            }
            if ($currentDate > $endDate) {
                $discountStatus = self::DISCOUNT_STATUS_EXPIRED;
                $discountStatusText = 'expired';
            }
        }
        return $statusText ? $discountStatusText : $discountStatus;
    }

    /**
     * Expired siscount status
     * @return boolean
     */
    public function discountHasExpired() {
        return $this->discountStatus() === self::DISCOUNT_STATUS_EXPIRED;
    }

    /**
     * Populating offer data for current time period
     */
    private function populateOffer() {
        if (count($this->data->offers) > 0) {
            foreach ($this->data->offers as $offer) {
                $startDate = strtotime($offer['start']);
                $endDate = strtotime($offer['end']);
                $currentDate = strtotime(\Tourpage\Helpers\Utils::currentDate());
                if ($currentDate >= $startDate) {
                    if ($currentDate <= $endDate) {
                        $this->offerData = (object) $offer;
                    }
                }
            }
        }
    }

    /**
     * Determine the tour has special promotional price
     * @return boolean
     */
    public function hasOffers() {
        $this->populateOffer();
        return $this->offerData != NULL;
    }

    /**
     * Get current special offer or promotion
     * @return object
     */
    public function getCurrentOffer() {
        $this->populateOffer();
        return $this->offerData;
    }

    /**
     * Get tour final price
     * @return float
     */
    public function getPrice($showReduced = FALSE) {
        $price = $this->getDefaultPrice();
        if ($showReduced) {
            if ($this->hasOffers()) {
                $price = $this->offerData->price;
            }
            if ($this->hasDiscount()) {
                $price = $this->getDiscountedPrice($price);
            }
        }
        return $price;
    }

    /**
     * Get raw or default price, excluding of all offer and discount
     * @return float
     */
    public function getDefaultPrice() {
        return $this->data->priceDefault;
    }

    /**
     * Getting discounted price
     * @return float
     */
    public function getDiscountedPrice($price = 0) {
        if ($price == 0) {
            $price = $this->getDefaultPrice();
        }
        $totalDiscountPrice = 0;
        if ($this->hasDiscount()) {
            if ($this->data->discount->price > 0) {
                $totalDiscountPrice = $price - (($price * $this->data->discount->price) / 100);
            }
        }
        return $totalDiscountPrice;
    }

    /**
     * Getting price for child
     * @return int
     */
    public function getPriceForChild() {
        return $this->data->priceForChild;
    }

}
