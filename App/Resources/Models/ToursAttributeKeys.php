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
 * Model Tours Description Attrubutes Key
 * @author amit
 */
class ToursAttributeKeys extends ApplicationModel {

    /**
     * Getting status string for attribute key
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->keyStatus) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Remove tour attribute key
     * @return boolean
     */
    public function removeData() {
        $tourAttributes = ToursAttributes::find(array(
                    'conditions' => 'keyId = :key_id:',
                    'bind' => array(
                        'key_id' => $this->keyId
                    )
        ));
        if ($tourAttributes && $tourAttributes->count() > 0) {
            foreach ($tourAttributes as $tourAttribute) {
                //Removing tour attribute relations
                $tourAttribute->delete();
            }
        }
        //Removing Attribute Key
        return $this->delete();
    }

}
