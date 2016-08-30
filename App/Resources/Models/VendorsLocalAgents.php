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
 * Model VendorsLocalAgents
 * Vendor local agents
 * @author amit
 */
class VendorsLocalAgents extends ApplicationModel {

    const AGENT_ACTIVE_STATUS_CODE = 1;
    const AGENT_INACTIVE_STATUS_CODE = 2;

    /**
     * Initializing model
     */
    public function initialize() {
        $this->keepSnapshots(true);
        $this->useDynamicUpdate(true);
        $this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
        $this->belongsTo('stateId', '\Tourpage\Models\State', 'stateId', array(
            'alias' => 'state'
        ));
        $this->belongsTo('countryId', '\Tourpage\Models\Country', 'countryId', array(
            'alias' => 'country'
        ));
    }

    /**
     * Implement Event beforeCreate()
     * Validating model data (emailAddress) before save (create)
     * @return boolean
     */
    public function beforeCreate() {
        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array("field" => "emailAddress", "message" => "Email Address must be unique")));
        if ($this->validationHasFailed()) {
            return false;
        }
    }

    /**
     * Implements Event beforeUpdate()
     * Validation model data (emailAddress) before save (update)
     * @return boolean
     */
    public function beforeUpdate() {
        if ($this->hasChanged('emailAddress')) {
            $memberSql = array('emailAddress=:email:', 'bind' => array(
                    'email' => $this->emailAddress,
            ));
            if (self::count($memberSql) > 0) {
                $message = new \Phalcon\Mvc\Model\Message("Email Address already using by someone else");
                $message->setField("emailAddress");
                $message->setType("InvalidCreateAttempt");
                $message->setModel($this);
                $this->appendMessage($message);
                return false;
            }
        }
    }

    /**
     * Getting status string for vendor local agents
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
     * Remove data and all its referance
     * @return boolean
     */
    public function removeData() {
        return $this->delete();
    }

}
