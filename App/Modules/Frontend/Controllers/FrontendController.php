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

use Phalcon\Mvc\Controller;

/**
 * Frontend Module Base Controller Class
 * @author Amit
 */
abstract class FrontendController extends Controller {

    /**
     * Initializing Frontend Base Controllers Options
     */
    public function initialize() {
        $this->assets->collection('header_css')
                ->addCss(strtolower(COMMON_DIR) . 'css/jquery-ui.css')
                ->addCss(strtolower(COMMON_DIR) . "css/bootstrap.css")
                ->addCss(strtolower(COMMON_DIR) . "css/bootstrap-social.css")
                ->addCss(strtolower(COMMON_DIR) . "css/font-awesome.css");
        $this->assets->collection('header_ie_css')
                ->addCss(strtolower(FRONT_END_DIR) . 'css/ie.css');

        $this->assets->collection('header_ie_js')
                ->addJs(strtolower(FRONT_END_DIR) . 'js/iehtml5.js')
                ->addJs(strtolower(FRONT_END_DIR) . 'js/selectivizr-min.js');

        $this->assets->collection('header_js')
                ->addJs(strtolower(COMMON_DIR) . 'js/jquery-ui.js')
                //->addJs(strtolower(COMMON_DIR) . 'js/bootstrap.js')
                ->addJs(strtolower(COMMON_DIR) . 'js/script.js')
               ->addJs(strtolower(FRONT_END_DIR) . 'js/jquery.easing.js')
               ->addJs(strtolower(FRONT_END_DIR) . 'js/init.js');
        
		$this->assets->collection('header_css')
		        ->addCss(strtolower(FRONT_END_DIR).'assets/css/BeatPicker.min.css')
				->addCss(strtolower(FRONT_END_DIR).'assets/css/bootstrap.css')
				->addCss(strtolower(FRONT_END_DIR).'assets/css/bootstrap-theme.css')
				->addCss(strtolower(FRONT_END_DIR).'assets/css/font-awesome.min.css')
				->addCss(strtolower(FRONT_END_DIR).'assets/css/style.css')
        	->addCss(strtolower(FRONT_END_DIR).'assets/css/style1.css')
				->addCss(strtolower(FRONT_END_DIR).'assets/css/media.css');
				
		$this->assets->collection('header_js')
         ->addJs(strtolower(FRONT_END_DIR) . 'assets/js/banner.js')
         ->addJs(strtolower(FRONT_END_DIR).'assets/js/BeatPicker.min.js')
				->addJs(strtolower(FRONT_END_DIR).'assets/js/bootstrap.js')
				->addJs(strtolower(FRONT_END_DIR).'assets/js/image-scale.js')
				->addJs(strtolower(FRONT_END_DIR).'assets/js/script.js');		
				
        $this->tag->setMeta(array(
            'http-equiv' => 'content-type',
            'content' => 'text/html; charset=UTF-8',
        ));
        $this->tag->setMeta(array(
            'http-equiv' => 'X-UA-Compatible',
            'content' => 'IE=edge,chrome=1',
        ));
        $this->tag->setMeta(array(
            'http-equiv' => 'cleartype',
            'content' => 'on',
        ));
        $this->tag->setViewport('width=device-width, initial-scale=1, maximum-scale=1');
        $this->tag->setDescription('.');
    }

    /**
     * Initializing layout Frontend Controllers
     */
    protected function initializeLayout($layout = 'main') {
        $this->view->setTemplateAfter($layout);
    }
   //$this->view->disable();
}
