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
 * Model for Vendors Access Controll Lebel
 * Parent Vendor will have all access.
 * @author amit
 */
class VendorsAcl extends ApplicationModel {

    private $acl = [];
    private $aclData = null;

    /**
     * Initializing model
     */
    public function initialize() {
        $this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
    }

    /**
     * manipulate variable data after fetch from database
     */
    public function afterFetch() {
        if ($this->count() > 0) {
            if (!empty($this->aclData)) {
                $this->acl = unserialize($this->aclData);
            }
        }
    }

    /**
     * Check for resource is allowed for the vendor
     * This check will not be applicable for parent vendor
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    public function isAllowed($controller, $action = 'index') {
        $allowed = FALSE;
        if ($this->count() > 0) {
            if (isset($this->acl[$controller]) && count($this->acl[$controller]) > 0) {
                if (in_array($action, $this->acl[$controller])) {
                    $allowed = TRUE;
                }
            }
        }
        //Skipping Acl for Parent Vendors
        //Parent Vendor will have all the access
        if ($this->vendor && $this->vendor->count() > 0) {
            if ($this->vendor->isParent()) {
                $allowed = TRUE;
            }
        }
        return $allowed;
    }

    /**
     * Access lebel Map
     * @return array
     */
    public function aclMap() {
        $aclMap = [
            'account' => [
                'title' => 'Account',
                'actions' => [
                    //'index' => 'Overview',
                    'edit' => 'Modify',
                    'resetpassword' => 'Reset Password',
                    'store' => 'Store Setting',
                    'storebanner' => 'Banners',
                ]
            ],
            'tours' => [
                'title' => 'Tours',
                'actions' => [
                    'index' => 'List',
                    'add' => 'Create ',
                    'edit' => 'Modify',
                    //'remove' => 'Remove',
                    'promotions' => 'Promotions',
                    'reviews' => 'Reviews',
                    'report' => 'Booking Report',
                ]
            ],
            'booking' => [
                'title' => 'Booking',
                'actions' => [
                    'index' => 'Booking List',
                    'details' => 'Booking Details',
                    'add' => 'Add New Booking'
                ]
            ],
			'groups' => [
                'title' => 'Groups',
                'actions' => [
                    'index' => 'Groups List',
                    'add' => 'Add New Group',
					'map' => 'Map Tours',
					'list' => 'List Mapped Tours'
                ]
            ],
            'members' => [
                'title' => 'Users',
                'actions' => [
                    'index' => 'User List',
                    'view' => 'User Details'
                ]
            ],
            'agents' => [
                'title' => 'Agents',
                'actions' => [
                    'local' => 'Local Agent List',
                    'registered' => 'Registered Agent List',
                    'add' => 'New Local Agents',
                    'edit' => 'Modify Local Agents',
                    //'remove' => 'Remove Local Agents',
                    'requests' => 'Registered Agent Requests',
                    'report' => 'Registered Agent Report',
                ]
            ],
        ];
        return $aclMap;
    }

    /**
     * Remove Data
     * @return boolean
     */
    public function removeData() {
        if ($this->count() > 0) {
            return $this->delete();
        }
    }

}
