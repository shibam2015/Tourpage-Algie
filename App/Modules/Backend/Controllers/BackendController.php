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

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;

/**
 * Backend Module Base Controller
 * @author amit
 */
abstract class BackendController extends Controller {

    /**
     * Initializing Backend base controllers options
     */
    public function initialize() {
        $this->assets->collection('headerJs')->addJs(COMMON_DIR . 'js/jquery.js');
        $this->assets->collection('headerCss')
                ->addCss(COMMON_DIR . 'css/bootstrap.css')
                ->addCss(COMMON_DIR . 'css/jquery-ui.css')
                ->addCss(COMMON_DIR . 'css/font-awesome.css')
                ->addCss(BACK_END_DIR . 'css/style.css');
        $this->assets->collection('footerJs')
                ->addJs(COMMON_DIR . 'js/jquery-ui.js')
                ->addJs(COMMON_DIR . 'js/angular.js')
                ->addJs(COMMON_DIR . 'js/bootstrap.js')
                ->addJs(COMMON_DIR . 'js/script.js');
        $this->assets->collection('footerCss')->addCss(strtolower(BACK_END_DIR) . 'css/style.css');
        $this->tag->setTitle(' | Administrator');
    }

    /**
     * Initializing layout Backend Controllers
     */
    protected function initializeLayout($layout = 'main') {
        $this->view->setTemplateAfter($layout);
    }

}
