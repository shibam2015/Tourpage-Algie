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
 * Model for Tours Review
 * @author amit
 */
class ToursReview extends ApplicationModel {

    const REVIEWER_IS_MEMBER_STATUS_CODE = 1;
    const REVIEWER_IS_NOT_MEMBER_STATUS_CODE = 2;

    /**
     * Initializing Model Tours Review
     */
    public function initialize() {
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
        $this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
        $this->belongsTo('reviewId', '\Tourpage\Models\MembersTourReview', 'reviewId', array(
            'alias' => 'memberTourReview'
        ));
    }

    /**
     * Check wheather the reviewer is member or not
     * @return boolean
     */
    public function isMember() {
        return $this->isMember == self::REVIEWER_IS_MEMBER_STATUS_CODE ? TRUE : FALSE;
    }

    /**
     * Getting status string for tour
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->reviewStatus) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Remove Tour Review and all its referances
     */
    public function removeData() {
        //Removing member tour review
        if ($this->memberTourReview && $this->memberTourReview->count() > 0) {
            $this->memberTourReview->delete();
        }
        //Removing tour review
        return $this->delete();
    }

}
