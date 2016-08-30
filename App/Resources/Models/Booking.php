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
 * Model for Booking
 * @author amit
 */
class Booking extends ApplicationModel {

    const COMPLETE_STATUS_CODE = 1;
    const CANCEL_STATUS_CODE = 2;
    const COMPLETE_STATUS_TEXT = 'Complete';
    const CANCEL_STATUS_TEXT = 'Cancel';
    const PAYMENT_PAID_STATUS_CODE = 1;
    const PAYMENT_PENDING_STATUS_CODE = 2;
    const PAYMENT_PAID_STATUS_TEXT = 'Paid';
    const PAYMENT_PENDING_STATUS_TEXT = 'Pending';

    /**
     * Initializing Model Booking
     */
    public function initialize() {
        $this->hasMany('bookingId', '\Tourpage\Models\BookingTours', 'bookingId', array(
            'alias' => 'bookingTours'
        ));
        $this->hasOne('bookingId', '\Tourpage\Models\BookingAddress', 'bookingId', array(
            'alias' => 'bookingAddress'
        ));
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
        $this->belongsTo('agentId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'agent'
        ));
    }

    /**
     * Getting tour booking status text
     * @param bool $escapeHtml
     * @return string
     */
    public function getBookingStatusText($escapeHtml = true) {
        switch ($this->bookingStatus) {
            case self::COMPLETE_STATUS_CODE:
                return $escapeHtml ? self::COMPLETE_STATUS_TEXT : '<span class="label label-success">' . self::COMPLETE_STATUS_TEXT . '</span>';
                break;
            case self::CANCEL_STATUS_CODE:
                return $escapeHtml ? self::CANCEL_STATUS_TEXT : '<span class="label label-danger">' . self::CANCEL_STATUS_TEXT . '</span>';
                break;
        }
    }

    /**
     * Getting tour payment status text
     * @param bool $escapeHtml
     * @return string
     */
    public function getPaymentStatusText($escapeHtml = true) {
        switch ($this->paymentStatus) {
            case self::PAYMENT_PAID_STATUS_CODE:
                return $escapeHtml ? self::PAYMENT_PAID_STATUS_TEXT : '<span class="label label-success">' . self::PAYMENT_PAID_STATUS_TEXT . '</span>';
                break;
            case self::PAYMENT_PENDING_STATUS_CODE:
                return $escapeHtml ? self::PAYMENT_PENDING_STATUS_TEXT : '<span class="label label-danger">' . self::PAYMENT_PENDING_STATUS_TEXT . '</span>';
                break;
        }
    }

    /**
     * Fully remove booking and all referances
     * Referances like
     * Tours @see \Tourpage\Models\BookingTours
     * Adress @see \Tourpage\Modes\BookingAddress
     * @return bool
     */
    public function removeData() {
        //Removing booking tours
        if ($this->bookingTours && $this->bookingTours->count() > 0) {
            $this->bookingTours->delete();
        }
        //Removing booking address
        if ($this->bookingAddress && $this->bookingAddress->count() > 0) {
            $this->bookingAddress->delete();
        }
        //Removing Member
        return $this->delete();
    }

    /**
     * Booking tours by vendor
     * @param int $vendorId
     * @return array
     */
    public function getBookingToursByVendor($vendorId) {
        $bookingToursByVendor = [];
        if ($this->bookingTours && $this->bookingTours->count() > 0) {
            foreach ($this->bookingTours as $bookingTour) {
                if ($bookingTour->vendorId == $vendorId) {
                    $bookingToursByVendor[] = $bookingTour;
                }
            }
        }
        return $bookingToursByVendor;
    }

    /**
     * Booking amount by vendor
     * @param type $vendorId
     * @return type
     */
    public function getTotalAmountByVendor($vendorId) {
        $bookingToursByVendor = $this->getBookingToursByVendor($vendorId);
        $totalAmount = 0;
        if (count($bookingToursByVendor) > 0) {
            foreach ($bookingToursByVendor as $bookingTour) {
                $totalAmount += $bookingTour->finalAmount;
            }
        }
        return $totalAmount;
    }

}
