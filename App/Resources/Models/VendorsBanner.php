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
 * Model for Vendor Banner
 * @author amit
 */
class VendorsBanner extends ApplicationModel {

    /**
     * Initializing models relations
     */
    public function initialize() {
        $this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
    }

    /**
     * Getting image uri
     * @param string $type
     * @return string
     */
    public function getBannerUri($path = false) {
        return Vendors::getUploadUri($this->vendorId, $path) . $this->bannerImage;
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
