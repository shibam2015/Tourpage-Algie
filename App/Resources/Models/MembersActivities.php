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
 * Model of MembersActivities
 * @author amit
 */
class MembersActivities extends ApplicationModel {

    /**
     * Initializing Model MembersActivities
     */
    public function initialize() {
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
        $this->belongsTo('activityId', '\Tourpage\Models\PlaceOfActivities', 'activityId', array(
            'alias' => 'activity'
        ));
    }

    /**
     * Remove members activities and all its referance
     * @return boolean
     */
    public function removeData() {
        //Removing MembersActivities
        return $this->delete();
    }

}
