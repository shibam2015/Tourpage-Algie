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
 * Model for Members Saved Credit/Debid Cards
 * with name and address
 * @author amit
 */
class MembersCards extends ApplicationModel {

    /**
     * Initializing model
     */
    public function initialize() {
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
        $this->belongsTo('stateId', '\Tourpage\Models\State', 'stateId', array(
            'alias' => 'state'
        ));
        $this->belongsTo('countryId', '\Tourpage\Models\Country', 'countryId', array(
            'alias' => 'country'
        ));
    }

    /**
     * Getting status string for member saved card
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
     * Remove member saved Cards
     * @return bool
     */
    public function removeData() {
        //Removing Member Saved Card
        return $this->delete();
    }

}
