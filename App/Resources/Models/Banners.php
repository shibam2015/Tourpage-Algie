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
 * Model Banners
 * @author amit
 */
class Banners extends ApplicationModel {

    /**
     * Model depandency injector
     * @var object
     */
    private static $di = null;

    /**
     * Initializing model
     */
    public function initialize() {
        self::$di = $this;
    }

    /**
     * Getting status string for banners
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->bannerStatus) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Banner Upload Directory
     * @param bool $path
     * @return string
     */
    public static function getUploadUri($path = FALSE) {
        self::$di = new self;
        $uploadPath = '/uploads/adsbanner/';
        if (!$path) {
            return self::$di->getDi()->getUrl()->getStatic($uploadPath);
        } else {
            return self::$di->getDi()->getUrl()->getBasePath() . '/public/elements' . $uploadPath;
        }
    }

    /**
     * Getting banner image uri
     * @param boolean $path
     * @return string
     */
    public function getBannerUri($path = false) {
        return self::getUploadUri($path) . $this->bannerImage;
    }

    /**
     * Fully remove banner and their all referances
     */
    public function removeData() {
        $this->removeImage();
        return $this->delete();
    }

    /**
     * Removing banner image
     * @return boolean
     */
    public function removeImage() {
        $bannerUri = $this->getBannerUri(TRUE);
        if (file_exists($bannerUri)) {
            unlink($bannerUri);
            $this->bannerImage = null;
            return TRUE;
        }
    }

}
