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
 * Model Tours
 * @author amit
 */
class Tours extends ApplicationModel {

    const BOOKING_OPEN_STATUS_CODE = 1;
    const BOOKING_CLOSE_STATUS_CODE = 2;
    const BOOKING_UPCOMMING_STATUS_CODE = 3;
    const LENGTH_MULTIPLE_DAYS_CODE = 'lmd';
    const LENGTH_FIXED_DAYS_CODE = 'lfd';
    const LENGTH_SINGLE_DAY_CODE = 'lsd';
    const LENGTH_HOURLY = 'lh';
    const LENGTH_MULTIPLE_DAYS_TEXT = 'Multiple Days';
    const LENGTH_FIXED_DAYS_TEXT = 'Fixed days';
    const LENGTH_SINGLE_DAY_TEXT = 'Single day';
    const LENGTH_HOURLY_TEXT = 'Hourly (multiple times a day)';

    /**
     * @var object Tour Length|Duration Data
     */
    public $tourDuration;

    /**
     * @var mix Serialize Tour Length Data
     */
    private $tourLengthData;

    /**
     * Social media links serialize data
     * @var string 
     */
    protected $socialMedia;

    /**
     * Social mecial links array
     * @var array
     */
    public $sm;

    /**
     *setter
     */
    public function setSocialMedia($socialMedia)
    {
        $this->socialMedia = $socialMedia;
    }
    
    /*
     * setter
     */
    public function setTourLengthData($tourLengthData)
    {
        $this->tourLengthData = $tourLengthData;
    }
    
    /**
     * Initializing Model Tours
     */
    public function initialize() {
        $this->belongsTo('tourId', '\Tourpage\Models\VendorsTours', 'tourId', array(
            'alias' => 'tourVendor'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\ToursImages', 'tourId', array(
            'alias' => 'tourImages'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\ToursCategory', 'tourId', array(
            'alias' => 'tourCategories'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\ToursAttributes', 'tourId', array(
            'alias' => 'tourAttributes'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\ToursOptions', 'tourId', array(
            'alias' => 'tourOptions'
        ));
        $this->belongsTo('tourId', '\Tourpage\Models\ToursPrice', 'tourId', array(
            'alias' => 'tourPrice'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\ToursReview', 'tourId', array(
            'alias' => 'tourReviews'
        ));
        $this->belongsTo('tourId', '\Tourpage\Models\ToursRating', 'tourId', array(
            'alias' => 'tourRating'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\ToursKeyHighlight', 'tourId', array(
            'alias' => 'tourKeyHighlights'
        ));
        $this->hasMany('tourId', '\Tourpage\Models\BookingTours', 'tourId', array(
            'alias' => 'bookings'
        ));
        $this->belongsTo('tourStateId', '\Tourpage\Models\State', 'stateId', array(
            'alias' => 'state'
        ));
        $this->belongsTo('tourCountryId', '\Tourpage\Models\Country', 'countryId', array(
            'alias' => 'country'
        ));
		$this->hasMany('tourId', '\Tourpage\Models\ToursAttractions', 'tourId', array(
            'alias' => 'tourAttractions'
        ));
		$this->hasMany('tourId', '\Tourpage\Models\GroupsTours', 'tourId', array(
            'alias' => 'group'
        ));
    }

    /**
     * manipulate variable data after fetch from database
     */
    public function afterFetch() {
        $tourLengthData = unserialize($this->tourLengthData);
        $this->tourDuration = new \stdClass();
        $this->tourDuration->lengthType = $tourLengthData['length_type'];
        $lengthTypeText = '';
        switch ($this->tourDuration->lengthType) {
            case self::LENGTH_MULTIPLE_DAYS_CODE:
                $lengthTypeText = self::LENGTH_MULTIPLE_DAYS_TEXT;
                break;
            case self::LENGTH_SINGLE_DAY_CODE:
                $lengthTypeText = self::LENGTH_SINGLE_DAY_TEXT;
                break;
            case self::LENGTH_FIXED_DAYS_CODE:
                $lengthTypeText = self::LENGTH_FIXED_DAYS_TEXT;
                break;
            case self::LENGTH_HOURLY:
                $lengthTypeText = self::LENGTH_HOURLY_TEXT;
                break;
        }
        $this->tourDuration->lengthTypeText = $lengthTypeText;
        if (isset($tourLengthData['duration'])) {
            $this->tourDuration->totalDays = $tourLengthData['duration'];
        }
        if (isset($tourLengthData['duration_hr']) && isset($tourLengthData['duration_mn'])) {
            $this->tourDuration->singleDayTime = new \stdClass();
            $this->tourDuration->singleDayTime->hours = $tourLengthData['duration_hr'];
            $this->tourDuration->singleDayTime->minutes = $tourLengthData['duration_mn'];
        }
        if (isset($tourLengthData['duration_times'])) {
            $this->tourDuration->times = [];
            if (count($tourLengthData['duration_times']) > 0) {
                foreach ($tourLengthData['duration_times'] as $durationTime) {
                    $this->tourDuration->times[] = (object) array(
                                'start' => (object) array('hours' => $durationTime['start']['hours'], 'minutes' => $durationTime['start']['minutes']),
                                'end' => (object) array('hours' => $durationTime['end']['hours'], 'minutes' => $durationTime['end']['minutes']),
                    );
                }
            }
        }
        if (count($tourLengthData['week_days']) > 0) {
            sort($tourLengthData['week_days']);
        }
        $this->tourDuration->weekDays = $tourLengthData['week_days'];

        $socialMedia = unserialize($this->socialMedia);
        $this->sm = new \stdClass();
        if (isset($socialMedia['links'])) {
            $this->sm->links = [];
            if (isset($socialMedia['links']['facebook'])) {
                $this->sm->links['facebook'] = urldecode($socialMedia['links']['facebook']);
            }
            if (isset($socialMedia['links']['twitter'])) {
                $this->sm->links['twitter'] = urldecode($socialMedia['links']['twitter']);
            }
            if (isset($socialMedia['links']['instagram'])) {
                $this->sm->links['instagram'] = urldecode($socialMedia['links']['instagram']);
            }
        }
    }

    /**
     * Getting tour booking status string
     * @param bool $escapeHtml
     * @return string
     */
    public function getBookingStatus($escapeHtml = false) {
        switch ($this->tourBookingStatus) {
            case self::BOOKING_OPEN_STATUS_CODE:
                return $escapeHtml ? 'Open' : '<span class="label label-success">Open</span>';
                break;
            case self::BOOKING_CLOSE_STATUS_CODE:
                return $escapeHtml ? 'Close' : '<span class="label label-danger">Close</span>';
                break;
            case self::BOOKING_UPCOMMING_STATUS_CODE:
                return $escapeHtml ? 'Up Comming' : '<span class="label label-info">Up Comming</span>';
                break;
        }
    }

    /**
     * Check for member can book this tour
     * @return boolean
     */
    public function isBookingActive() {
		$activeStatus = self::BOOKING_CLOSE_STATUS_CODE;
        $currentDate = new \DateTime(\Tourpage\Helpers\Utils::currentDate());
        $startDate = new \DateTime($this->tourStartFrom);
        $endDate = new \DateTime($this->tourEndTo);
		$vendortour = \Tourpage\Models\VendorsTours::findFirst(array('tourId'=>$this->tourId));
        if ($vendortour->status == self::ACTIVE_STATUS_CODE) {
            if ($this->tourBookingStatus == self::BOOKING_OPEN_STATUS_CODE){
				if ($currentDate > $endDate) {
					$activeStatus = self::BOOKING_CLOSE_STATUS_CODE;
				} else { 
                switch ($this->tourDuration->lengthType) {
                    case self::LENGTH_MULTIPLE_DAYS_CODE:
                        $endDate->sub(new \DateInterval('P' . $this->tourDuration->totalDays . 'D'));
                        if ($currentDate <= $endDate) {
                            if ($currentDate >= $startDate) {
                                if (in_array($currentDate->format('w'), $this->tourDuration->weekDays)) {
									$activeStatus = self::BOOKING_OPEN_STATUS_CODE;
                                }
                            } else {
								$activeStatus = self::BOOKING_OPEN_STATUS_CODE;
                            }
                        }
                        break;
                    case self::LENGTH_FIXED_DAYS_CODE:
                        if ($currentDate < $startDate) {
							$activeStatus = self::BOOKING_OPEN_STATUS_CODE;
                        }
                        break;
                    case self::LENGTH_SINGLE_DAY_CODE:
                    case self::LENGTH_HOURLY:
                        $endDate->sub(new \DateInterval('P1D'));
                        if ($currentDate <= $endDate) {
                            if ($currentDate >= $startDate) {
                                if (in_array($currentDate->format('w'), $this->tourDuration->weekDays)) {
									$activeStatus = self::BOOKING_OPEN_STATUS_CODE;
                                }
                            } else {
								$activeStatus = self::BOOKING_OPEN_STATUS_CODE;
                            }
                        }
                        break;
                }
			  }
            } elseif($this->tourBookingStatus == self::BOOKING_UPCOMMING_STATUS_CODE) {
				$activeStatus  = self::BOOKING_UPCOMMING_STATUS_CODE;
			} else {
				$activeStatus  = self::BOOKING_CLOSE_STATUS_CODE;
			}
        } else {
			$activeStatus  = self::BOOKING_CLOSE_STATUS_CODE;
		}
        return $activeStatus;
    }

    /**
     * Getting status string for tour
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->tourVendor->status) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Getting Default image of tour
     * @return boolean
     */
    public function getDefaultImage() {
        $defaultImage = \Tourpage\Models\ToursImages::findFirst(array(
                    'tourId = :tour_id: AND imageDefault = :image_default:',
                    'bind' => array('tour_id' => $this->tourId, 'image_default' => \Tourpage\Models\ToursImages::DEFAULT_STATUS_CODE)
        ));
        if ($defaultImage && $defaultImage->count() > 0) {
            return $defaultImage;
        }
        return FALSE;
    }

    /**
     * Generating Tour Url for Tour Details
     * @return string
     */
    public function getUri() {
        //return $this->getDi()->getUrl()->getBaseUri() . '/tour/index/' . $this->tourVendor->vendorId . '/' . $this->tourId . '/' . $this->tourSlug;
        return $this->getDi()->getUrl()->getBaseUri() . '/tour/index/' . $this->tourId . '/' . $this->tourSlug;
    }

    /**
     * Remove Tour and all its referances
     */
    public function removeData() {
        //Removing tour attributes
        if ($this->tourAttributes && $this->tourAttributes->count() > 0) {
            $this->tourAttributes->delete();
        }
        //Removing tour categories
        if ($this->tourCategories && $this->tourCategories->count() > 0) {
            $this->tourCategories->delete();
        }
        //Removing tour price
        if ($this->tourPrice) {
            $this->tourPrice->delete();
        }
        //Removing tour from vendor list
        if ($this->tourVendor) {
            $this->tourVendor->delete();
        }
        //Removing tour images
        if ($this->tourImages && $this->tourImages->count() > 0) {
            foreach ($this->tourImages as $image) {
                $image->removeData();
            }
        }
        //Removing tour booking
        if ($this->bookings && $this->bookings->count() > 0) {
            foreach ($this->bookings as $booking) {
                $booking->delete();
            }
        }
        //Removing tour options
        if ($this->tourOptions && $this->tourOptions->count() > 0) {
            $this->tourOptions->delete();
        }
        //Removing tour reviews
        if ($this->tourReviews && $this->tourReviews->count() > 0) {
            $this->tourReviews->delete();
        }
        //Removing tour rating
        if ($this->tourRating && $this->tourRating->count() > 0) {
            $this->tourRating->delete();
        }
        //Removing tour key highlight
        if ($this->TourKeyHighlights && $this->TourKeyHighlights->count() > 0) {
            $this->TourKeyHighlights->delete();
        }
        //Removing tour
        return $this->delete();
    }

}
