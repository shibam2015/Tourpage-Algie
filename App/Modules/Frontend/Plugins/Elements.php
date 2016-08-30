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

namespace Multiple\Frontend\Plugins;

use Phalcon\Mvc\User\Component;

/**
 * Elements
 * Helps to build UI elements for the application
 */
class Elements extends Component {

    public function getBanner() {
        $banners = \Tourpage\Models\Banners::find(array(
                    'conditions' => 'bannerStatus = :status: AND bannerType = :type:',
                    'bind' => array(
                        'status' => \Tourpage\Models\Banners::ACTIVE_STATUS_CODE,
                        'type' => 'home'
                    ),
                    'order' => 'bannerId DESC',
        ));
        return $banners;
    }

    public function getTours($limit = 10) {
        $tours = \Tourpage\Models\Tours::query();
        $tours->leftJoin('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\Tours.tourId', 'vt');
        $tours->where('vt.status = :status:');
        $tours->orderBy('\Tourpage\Models\Tours.tourId DESC');
        $tours->bind(array(
            'status' => \Tourpage\Models\VendorsTours::ACTIVE_STATUS_CODE
        ));
        $tours->limit($limit);
        return $tours->execute();
    }

}
