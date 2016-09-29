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

/*
 * Vendor Module Base Controller
 */

namespace Multiple\Vendor\Controllers;

use Phalcon\Mvc\Controller;

abstract class VendorController extends Controller {

    /**
     * Initializing Frontend Base Controllers Options
     */
    public function initialize() {
        $styles = $this->assets->collection('header')
                ->addCss(COMMON_DIR . 'css/bootstrap.css')
                ->addCss(COMMON_DIR . 'css/jquery-ui.css')
                ->addCss(COMMON_DIR . 'css/font-awesome.css');
        if ($this->dispatcher->getControllerName() == 'auth' && $this->dispatcher->getActionName() == 'pricing') {
            $styles->addCss(VENDOR_DIR . 'css/pricing.css');
        } else {
            $styles->addCss(VENDOR_DIR . 'css/style.css');
        }
        $assets = $this->assets->collection('footer')
                ->addJs(COMMON_DIR . 'js/bootstrap.js')
                ->addJs(COMMON_DIR . 'js/script.js');
        if ($this->dispatcher->getControllerName() != 'account' && $this->dispatcher->getActionName() != 'edit') {
            $assets->addJs(VENDOR_DIR . 'js/common.js');
        }
    }

    /**
     * Initializing layout Vendor Controllers
     */
    protected function initializeLayout($layout = 'main') {
        $this->view->setTemplateAfter($layout);
    }

}
