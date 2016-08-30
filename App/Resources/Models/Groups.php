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
 * Model Groups
 */
class Groups extends ApplicationModel {
	
	/**
     * Initializing Model Booking
     */
    public function initialize() {
        $this->hasMany('groupId', '\Tourpage\Models\GroupsTours', 'groupId', array(
            'alias' => 'groupTours'
        ));
		$this->hasMany('groupId', '\Tourpage\Models\GroupsVendors', 'groupId', array(
            'alias' => 'groupVendors'
        ));
	}
    public function removeData() {
        //Removing groups
        return $this->delete();
    }

}
