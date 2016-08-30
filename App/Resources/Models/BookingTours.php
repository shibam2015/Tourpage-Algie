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
 * Model for BookingTours
 * @author amit
 */
class BookingTours extends ApplicationModel {

    const TOUR_CONDUCT_PENDING_STATUS_CODE = 3;
    const TOUR_CONDUCT_COMPLETE_STATUS_CODE = 4;
    const TOUR_CONDUCT_CANCEL_STATUS_CODE = 5;
    const TOUR_CONDUCT_PENDING_STATUS_TEXT = 'Tour Pending';
    const TOUR_CONDUCT_COMPLETE_STATUS_TEXT = 'Tour Complete';
    const TOUR_CONDUCT_CANCEL_STATUS_TEXT = 'Tour Cancel';

    /**
     * @var object Price Data
     */
    public $data;

    /**
     * Initializing Model Booking
     */
    public function initialize() {
        $this->belongsTo('bookingId', '\Tourpage\Models\Booking', 'bookingId', array(
            'alias' => 'booking'
        ));
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
        $this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
        $this->belongsTo('employeeId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'employee'
        ));
    }

    /**
     * manipulate variable data after fetch from database
     */
    public function afterFetch() {
        $tourData = $this->getTourData();
        $this->data = new \stdClass();
        $headCountUnit = '';
        if (isset($tourData['head_count']) && count($tourData['head_count']) > 0) {
            $headCountUnit = 'Person(s)';
            $this->data->headCount = $tourData['head_count'];
        }
        if (isset($tourData['group_head_count'])) {
            $headCountUnit = 'Group(s)';
            $this->data->groupHeadCount = $tourData['group_head_count'];
        }
        $this->data->headCountUnit = $headCountUnit;
        if (isset($tourData['tour_opt'])) {
            $this->data->tourOptions = $tourData['tour_opt'];
        }
        if (isset($tourData['time_slot'])) {
            $this->data->timeSlot = $tourData['time_slot'];
        }
        if (isset($tourData['multi_purches_discount'])) {
            $this->data->multiPurches = (object) array(
                        'discount' => $tourData['multi_purches_discount'],
                        'amountSave' => $tourData['multi_purches_save_amount']
            );
        }
    }

    /**
     * Get tour price data
     */
    public function getTourData() {
        return unserialize($this->tourData);
    }

    /**
     * Getting tour booking status text
     * @param bool $escapeHtml
     * @return string
     */
    public function getTourConductStatusText($escapeHtml = true) {
        switch ($this->tourConductStatus) {
            case self::TOUR_CONDUCT_PENDING_STATUS_CODE:
                return $escapeHtml ? self::TOUR_CONDUCT_PENDING_STATUS_TEXT : '<span class="label label-info">' . self::TOUR_CONDUCT_PENDING_STATUS_TEXT . '</span>';
                break;
            case self::TOUR_CONDUCT_COMPLETE_STATUS_CODE:
                return $escapeHtml ? self::TOUR_CONDUCT_COMPLETE_STATUS_TEXT : '<span class="label label-success">' . self::TOUR_CONDUCT_COMPLETE_STATUS_TEXT . '</span>';
                break;
            case self::TOUR_CONDUCT_CANCEL_STATUS_CODE:
                return $escapeHtml ? self::TOUR_CONDUCT_CANCEL_STATUS_TEXT : '<span class="label label-danger">' . self::TOUR_CONDUCT_CANCEL_STATUS_TEXT . '</span>';
                break;
        }
    }

}
