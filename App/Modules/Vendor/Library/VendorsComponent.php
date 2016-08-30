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

namespace Multiple\Vendor\Library;

use Phalcon\Mvc\User\Component;

/*
 * VendorComponent library
 */

class VendorsComponent extends Component {

    /**
     * Vendor Data Object
     * @var type object
     */
    private $vendorsData = null;
    private $allowResource = [
        'index' => ['*'],
        'auth' => ['*'],
        'account' => ['index'],
        'site' => ['*'],
        'booking' => ['paymentProcess', 'paymentReturn'],
    ];

    /**
     * Class constructor
     */
    public function __construct() {
        $this->vendorsData = $this->session->get('vendors');
    }

    /**
     * Determine wheather vendor is logged in or not
     * @return boolean
     */
    public function isLoggedIn() {
        if ($this->getVendorData()) {
            if ($this->getVendorData()->vendorId > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Getting vendor Id
     * @return type Int
     */
    public function getId() {
        return $this->getVendorData()->vendorId;
    }

    /**
     * Getting Vendor Data
     * @return type object
     */
    public function getVendorData() {
        return $this->vendorsData;
    }

    /**
     * Getting vendor real name
     * @return type mix
     */
    public function getName() {
        return $this->getVendorData()->firstName;
    }

    /**
     * Getting vendor full real name
     * @return type string
     */
    public function getFullName() {
        return $this->getVendorData()->firstName . ' ' . $this->getVendorData()->lastName;
    }

    /**
     * Getting vendor email address
     * @return type mix
     */
    public function getEmail() {
        return $this->getVendorData()->emailAddress;
    }

    /**
     * Getting vendor status
     * 1 for Active, 2 for Inactive
     * @return type int
     */
    public function getStatus() {
        return $this->getVendorData()->status;
    }

    /**
     * Getting vendor status
     * 1 for Active, 2 for Inactive
     * @return type int
     */
    public function isTripAdvisor() {
        $Adv = array(
            'n' => 'No',
            'y' => 'Yes',
            'ns' => 'Not Sure',
        );
        return isset($Adv[$this->getVendorData()->isTripAdv]) ? $Adv[$this->getVendorData()->isTripAdv] : '';
    }

    /**
     * Absolute pathe to the vendor image folder
     * @param int $id
     * @return string
     */
    public function getTourImagesPath($id = NULL) {
        if (!$id) {
            $id = !$this->getVendorData()->isParent() ? $this->getVendorData()->parentId : $this->getId();
        }
        $tourImagePath = \Tourpage\Models\Vendors::getUploadUri($id, TRUE);
        if (!file_exists($tourImagePath)) {
            mkdir($tourImagePath, 0777, TRUE);
            $indexFile = fopen($tourImagePath . 'index.html', 'w');
            fclose($indexFile);
        }
        return $tourImagePath;
    }

    /**
     * Refresh or Reload vendor data to session
     */
    public function refresh() {
        $this->session->remove('vendors');
        $vendor = \Tourpage\Models\Vendors::findFirst($this->getId());
        $this->session->set('vendors', $vendor);
        return $vendor;
    }

    /**
     * ACL for Vendors
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    public function isAllowed($controller, $action = 'index') {
        $allow = FALSE;
        $vendorData = $this->getVendorData();
        $vendorAcl = $vendorData->acl;
        if (isset($this->allowResource[$controller])) {
            $allow = TRUE;
            if (!in_array('*', $this->allowResource[$controller])) {
                if (!in_array($action, $this->allowResource[$controller])) {
                    $allow = FALSE;
                }
            }
        }
        if ($vendorAcl && $vendorAcl->isAllowed($controller, $action)) {
            $allow = TRUE;
        }
        if ($vendorData->isParent()) {
            $allow = TRUE;
        }
        return $allow;
    }

}
