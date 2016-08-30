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

namespace Multiple\Backend\Library;

use Phalcon\Mvc\User\Component;

/**
 * AdminComponent library
 */
class AdminComponent extends Component {

    /**
     * Admin Data Object
     * @var type object
     */
    private $adminData = null;

    /**
     * Class constructor
     */
    public function __construct() {
        $this->adminData = $this->session->get('admin');
    }

    /**
     * Determine wheather admin is logged in or not
     * @return boolean
     */
    public function isLoggedIn() {
        if ($this->getAdminData()) {
            if ($this->getAdminData()->adminId > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Getting admin Id
     * @return type Int
     */
    public function getId() {
        return $this->getAdminData()->adminId;
    }

    /**
     * Getting Admin Data
     * @return type object
     */
    public function getAdminData() {
        return $this->adminData;
    }

    /**
     * 
     * @return type
     */
    public function getUserName() {
        return $this->getAdminData()->userName;
    }

    /**
     * Getting admin real first name
     * @return type mix
     */
    public function getName() {
        return $this->getAdminData()->firstName;
    }

    /**
     * Getting admin full real name
     * @return type string
     */
    public function getFullName() {
        return $this->getAdminData()->firstName . ' ' . $this->getAdminData()->lastName;
    }

    /**
     * Getting admin email address
     * @return type mix
     */
    public function getEmail() {
        return $this->getAdminData()->emailAddress;
    }

    /**
     * Getting admin status
     * @param boolean $escapeHtml
     * @return string|int
     */
    public function getStatus($escapeHtml = false) {
        return $this->getAdminData()->getStatus($escapeHtml);
    }

    /**
     * Getting admin user role title or name
     * @return string
     */
    public function getRole() {
        return $this->getAdminData()->role->getName();
    }

    /**
     * Refresh or Reload admin data to session
     */
    public function refresh() {
        $this->session->remove('admin');
        $admin = \Tourpage\Models\Admins::findFirst($this->getId());
        $this->session->set('admin', $admin);
    }

}
