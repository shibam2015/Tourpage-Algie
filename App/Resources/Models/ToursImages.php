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
 * Model Products Images
 * @author amit
 */
class ToursImages extends ApplicationModel {

    const DEFAULT_STATUS_CODE = 1;
    const NORMAL_STATUS_CODE = 2;

    /**
     * Initializing Model Products Images
     */
    public function initialize() {
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
    }

    /**
     * Tour Image Allowed Mime Type
     * Used during image upload for tour
     * @param \Phalcon\Http\Request\File $file
     * @return bool
     */
    public function allowImageType(\Phalcon\Http\Request\File $file) {
        $mimeTypes = array(
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
        );
        return in_array($file->getType(), $mimeTypes);
    }

    /**
     * Getting image uri
     * @param string $type
     * @return string
     */
    public function getImageUri($type = '', $path = FALSE) {
        return Vendors::getUploadUri($this->tour->tourVendor->vendorId, $path) . ($type == 'thumb' ? 'thumb' . $this->imagePath : $this->imagePath);
    }

    /**
     * Remove Tour image
     */
    public function removeData() {
        if ($this->count() > 0) {
            if (file_exists($this->getImageUri('', TRUE))) {
                unlink($this->getImageUri('', TRUE));
                unlink($this->getImageUri('thumb', TRUE));
            }
            $this->delete();
        }
    }

}
