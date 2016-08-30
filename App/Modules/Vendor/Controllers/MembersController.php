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
 * Controller for vendor member management
 * @author amit
 */
class MembersController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for Members Controller
     */
    public function indexAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $memberToVendor = \Tourpage\Models\MembersToVendor::query();
        $memberToVendor->where('vendorId = :vendor_id:');
        $memberToVendor->bind(array(
            'vendor_id' => !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId()
        ));
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $memberToVendor->execute(),
            "page" => $page,
        ));

        $this->tag->setTitle('Users');
        $this->view->pager = $pager;
    }

    /**
     * Action for view users details
     * @param int $memberId
     */
    public function viewAction($memberId = 0) {
        if (!preg_match_all('/[0-9]+/', $memberId) || $memberId == 0) {
            return FALSE;
        }
        $bookings = new \stdClass();
        $bookings->totalBooking = 0;
        $bookings->totalAmount = 0;
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        $member = \Tourpage\Models\Members::findFirst($memberId);
        if (!$member) {
            return FALSE;
        }
        $bookings->monthsQueue = [];
        foreach (\Tourpage\Helpers\Utils::getMonths() as $monthNumber => $monthTitle) {
            $bookings->monthsQueue[\Tourpage\Helpers\Utils::padInt($monthNumber)] = 0;
        }
        $modelBind = [];
        $memberBookings = \Tourpage\Models\BookingTours::query();
        $memberBookings->where("\Tourpage\Models\BookingTours.vendorId = :vendorId:");
        $modelBind['vendorId'] = $vendorId;
        $memberBookings->join('\Tourpage\Models\Booking', 'b.bookingId = \Tourpage\Models\BookingTours.bookingId', 'b');
        $memberBookings->addWhere("b.memberId = :memberId:");
        $modelBind['memberId'] = $memberId;
        if (count($modelBind) > 0) {
            $memberBookings->bind($modelBind);
        }
        $memberBookings->order("\Tourpage\Models\BookingTours.bookingId DESC");
        $memberBookings->groupBy("\Tourpage\Models\BookingTours.bookingId");
        $bookings->query = $memberBookings->execute();
        
        if ($bookings->query && $bookings->query->count() > 0) {
            foreach ($bookings->query as $bookingTour) {
                $bookings->totalBooking += 1;
                $bookings->totalAmount += $bookingTour->finalAmount;
                $bookingYear = date('Y', strtotime($bookingTour->booking->bookedOn));
                if ($bookingYear == \Tourpage\Helpers\Utils::__getCurrentYear()) {
                    $bookingMonth = date('m', strtotime($bookingTour->booking->bookedOn));
                    $bookings->monthsQueue[$bookingMonth] = $bookings->monthsQueue[$bookingMonth] + $bookingTour->finalAmount;
                }
            }
        }
        
        $this->tag->setTitle('View Users Details');
        $this->view->member = $member;
        $this->view->bookings = $bookings;
    }

}
