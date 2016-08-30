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
 * Model for Tours Rating
 * @author amit
 */
class ToursRating extends ApplicationModel {

    /**
     * Initializing Model Tours Rating
     */
    public function initialize() {
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
    }

    /**
     * Getting individual star rating count
     * @param int $star
     * @return int
     */
    public function getRating($star = 1) {
        return $star > 0 && $star <= 5 ? $this->{'star_' . $star} : 0;
    }

    /**
     * Rating star calculation
     * Getting total star count for tour
     * @return int
     */
    public function getStar() {
        $totalStar = 0;
        $starSum = 0;
        for ($s = 1; $s <= 5; $s++) {
            $totalStar += $this->getRating($s);
            $starSum += ($s * $this->getRating($s));
        }
        $strCount = $totalStar > 0 ? ($starSum / $totalStar) : 0;
        if ($strCount > 5) {
            $strCount = 5;
        }
        return $strCount;
    }

}
