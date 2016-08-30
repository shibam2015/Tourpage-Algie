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
 * Model TourTypes
 * @author amit
 */
class GroupsTours extends ApplicationModel {
	
	/**
     * Initializing Model Booking
     */
    public function initialize() {
        $this->belongsTo('groupId', '\Tourpage\Models\Groups', 'groupId', array(
            'alias' => 'group'
        ));
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
		$this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
    }
    /**
     * Fully remove tour types and all their referance
     * VendorTourTypes @see \Tourpage\Models\VendorsTourTypes
     */
    public function removeData() {
        $groupTours = GroupsTours::find(array(
                    'groupToursId = :group_tours_id:',
                    'bind' => array(
                        'group_tours_id' => $this->groupToursId
                    )
        ));
        if ($groupTours && $groupTours->count() > 0) {
            foreach ($groupTours as $groupTour) {
                //Removing vendor tour and activities type relation
                $groupTour->delete();
            }
        }
        //Removing tour type
        return $this->delete();
    }

}
