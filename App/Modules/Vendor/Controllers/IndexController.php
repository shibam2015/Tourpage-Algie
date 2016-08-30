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
 * Index Controller
 * @author Amit
 */
class IndexController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index Acton for Index Controller
     */
    public function indexAction($action = 'list') {
		if($action == 'list'){
			$this->tag->setTitle('Dashboard');
			$vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
			$annountement = \Tourpage\Models\Announcement::findFirst(array(
						'conditions' => 'ancType = :type:',
						'bind' => array('type' => 'vendordashboard')
			));
			$this->view->announcementContent = $annountement ? \Tourpage\Helpers\Utils::decodeString($annountement->ancContent) : '';
			if ($this->vendors->isAllowed('tours')) {
				$bind = [];
				$tours = \Tourpage\Models\VendorsTours::query();
				$tours->where('vendorId = :vendor_id:');
				$bind['vendor_id'] = $vendorId;
				if (!$this->vendors->getVendorData()->isParent()) {
					$tours->andWhere("createBy = :create_by:");
					$bind['create_by'] = $this->vendors->getId();
				}
				if (count($bind) > 0) {
					$tours->bind($bind);
				}
				$tours->order("vendorTourId DESC");
				$tours->limit(5);
				$this->view->tours = $tours->execute();
			}
			if ($this->vendors->isAllowed('booking')) {
				$bind = [];
				$bookings = \Tourpage\Models\BookingTours::query();
				$bookings->where("\Tourpage\Models\BookingTours.vendorId = :vendor_id:");
				$bind['vendor_id'] = $vendorId;
				if (!$this->vendors->getVendorData()->isParent()) {
					$bookings->leftJoin('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\BookingTours.tourId', 'vt');
					$bookings->addWhere("vt.createBy = :create_by:");
					$bind['create_by'] = $this->vendors->getId();
				}
				if (count($bind) > 0) {
					$bookings->bind($bind);
				}
	
				$bookings->order("\Tourpage\Models\BookingTours.bookingId DESC");
				$bookings->groupBy("\Tourpage\Models\BookingTours.bookingId");
				$bookings->limit(5);
				$this->view->bookings = $bookings->execute();
			}
			$this->view->vendorMessages = \Tourpage\Models\VendorsMessages::findFirst(array(
				'conditions' => 'vendorId = :vendor_id: AND vendorMessageStatus = :status:',
				'bind' => array('vendor_id' => $vendorId, 'status' => \Tourpage\Models\VendorsMessages::UNREAD_STATUS_CODE)
			));
		}
		if ($action == 'message') {
			$vendorsMessages = \Tourpage\Models\VendorsMessages::findFirst(array(
        	'conditions' => 'vendorId = :vendor_id: AND vendorMessageStatus = :status:',
            'bind' => array('vendor_id' => $vendorId, 'status' => \Tourpage\Models\VendorsMessages::UNREAD_STATUS_CODE)
        ));
			$vendorsMessages->vendorMessageStatus = \Tourpage\Models\VendorsMessages::READ_STATUS_CODE;
                if ($vendorsMessages->save()) {
				  } else {
					  foreach ($vendorsMessages->getMessages() as $messages) {
						  $this->flash->error((string) $messages);
					  }
				  }
		}
    }

}
