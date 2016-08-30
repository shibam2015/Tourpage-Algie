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

namespace Multiple\Frontend\Controllers;

/**
 * Class Index Controller or Home Controller
 */
class IndexController extends FrontendController {

    /**
     * Initializing Store Controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index Action
     */
    public function indexAction() {
        $this->tag->setTitle('Tourpage');
        /*$this->view->banners = \Tourpage\Models\Banners::find(array(
                    'conditions' => 'bannerStatus = :status: AND bannerType = :type:',
                    'bind' => array(
                        'status' => \Tourpage\Models\Banners::ACTIVE_STATUS_CODE,
                        'type' => 'home'
                    ),
                    'order' => 'bannerId DESC',
        ));*/

        $this->view->vendors = \Tourpage\Models\Vendors::find(array(
                    'conditions' => 'status = :status: AND parentId = :parent_id:',
                    'bind' => array(
                        'status' => \Tourpage\Models\Vendors::ACTIVE_STATUS_CODE,
                        'parent_id' => 0
                    ),
                    'order' => 'vendorId DESC',
                    'limit' => \Tourpage\Helpers\Utils::DEFAULT_PAGE_SIZE
        ));

        $this->view->vendorTours = \Tourpage\Models\VendorsTours::find(array(
                    'conditions' => 'status = :status:',
                    'bind' => array(
                        'status' => \Tourpage\Models\VendorsTours::ACTIVE_STATUS_CODE
                    ),
                    'order' => 'vendorTourId DESC',
                    'limit' => \Tourpage\Helpers\Utils::DEFAULT_PAGE_SIZE
        ));
    }

}
