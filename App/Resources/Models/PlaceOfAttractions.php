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
 * Model PlaceOfAttractions
 * @author amit
 */
class PlaceOfAttractions extends ApplicationModel {

    /**
     * Initializing Model PlaceOfAttractions
     */
    public function initialize() {
        $this->belongsTo('countryId', '\Tourpage\Models\Country', 'countryId', array(
            'alias' => 'country'
        ));
        $this->belongsTo('stateId', '\Tourpage\Models\State', 'stateId', array(
            'alias' => 'state'
        ));
        $this->belongsTo('cityId', '\Tourpage\Models\City', 'cityId', array(
            'alias' => 'city'
        ));
    }

    /**
     * Getting status string for place of attractions
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->status) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Remove place of attraction and all its referance
     * @return boolean
     */
    public function removeData() {
        //Removing PlaceofAttraction
        return $this->delete();
    }

}
