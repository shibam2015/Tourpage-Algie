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
 * Model KeyHighlight
 * @author amit
 */
class KeyHighlight extends ApplicationModel {

    /**
     * Initializing model
     */
    public function initialize() {
        $this->hasMany('keyhighlightId', '\Tourpage\Models\ToursKeyHighlight', 'keyhighlightId', array(
            'alias' => 'toursKeyHighlight'
        ));
    }

    /**
     * Getting Key Highlight Icon Image
     * @return string
     */
    public function getIconUri() {
        $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/khicon/';
        $iconImage = '';
        if (file_exists($baseLocation . $this->keyhighlightIcon)) {
            $iconImage = $this->getDi()->getUrl()->getStatic('/uploads/khicon/' . $this->keyhighlightIcon);
        }
        return $iconImage;
    }

    /**
     * Getting status string for key highlight
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->keyhighlightStatus) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Remove key highlight
     */
    public function removeData() {
        //Removing tour key highlight
        if ($this->toursKeyHighlight && $this->toursKeyHighlight->count() > 0) {
            $this->toursKeyHighlight->delete();
        }
        $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/khicon/';
        if (file_exists($baseLocation . $this->keyhighlightIcon)) {
            unlink($baseLocation . $this->keyhighlightIcon);
        }
        return $this->delete();
    }

}
