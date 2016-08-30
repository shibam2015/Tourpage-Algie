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
 * Model VendorsRegisteredAgents
 * Registered agent with vendor relation
 * @author amit
 */
class VendorsRegisteredAgents extends ApplicationModel {

    const AGENT_ACTIVE_STATUS_CODE = 1;
    const AGENT_INACTIVE_STATUS_CODE = 2;
    const AGENT_APPROVED_STATUS_CODE = 1;
    const AGENT_REJECTED_STATUS_CODE = 2;
    const AGENT_PENDING_STATUS_CODE = 3;

    /**
     * Initializing model
     */
    public function initialize() {
        $this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
    }

    /**
     * Getting status string
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->status) {
            case self::AGENT_ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::AGENT_INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Getting request status string
     * @param bool $escapeHtml
     * @return string
     */
    public function getRequestStatus($escapeHtml = false) {
        switch ($this->requestStatus) {
            case self::AGENT_APPROVED_STATUS_CODE:
                return $escapeHtml ? 'Approved' : '<span class="label label-success">Approved</span>';
                break;
            case self::AGENT_REJECTED_STATUS_CODE:
                return $escapeHtml ? 'Rejected' : '<span class="label label-danger">Rejected</span>';
                break;
            case self::AGENT_PENDING_STATUS_CODE:
                return $escapeHtml ? 'Pending' : '<span class="label label-warning">Pending</span>';
                break;
        }
    }

    /**
     * Remove data and all its referance
     * @return boolean
     */
    public function removeData() {
        return $this->delete();
    }

}
