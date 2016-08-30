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
 * Model Members Events
 * @author amit
 */
class MembersEvents extends ApplicationModel {

    const EVENT_RESET_PASSWORD = 'member_reset_password';
    const EVENT_CREATE_NEW_ACCOUNT = 'member_create_new_account';
    const EVENT_ACTIVE_STATUS_CODE = 1;
    const EVENT_INACTIVE_STATUS_CODE = 2;

    /**
     * Initializing model
     */
    public function initialize() {
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
    }

    /**
     * Check event stage
     * @return boolean
     */
    public function isActiveEvent() {
        if ($this->eventValidThru > time() && $this->eventStatus == self::EVENT_ACTIVE_STATUS_CODE) {
            return TRUE;
        } else {
            return $this->closeEvent();
        }
    }

    /**
     * Closing event. Changing status to inactive
     * @return boolean
     */
    public function closeEvent() {
        $this->eventStatus = self::EVENT_INACTIVE_STATUS_CODE;
        if ($this->save()) {
            return FALSE;
        }
    }

}
