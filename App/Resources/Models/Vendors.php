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
 * Members Model
 */
class Vendors extends ApplicationModel {

    const PENDING_APPROVAL_STATUS_CODE = 3;
    const PENDING_EMAIL_VALIDATION_STATUS_CODE = 4;

    /**
     * Model depandency injector
     * @var object
     */
    private static $di = null;

    /**
     * @var array Vendor data for social media links
     */
    public $sm;

    /**
     * Initializing model
     */
    public function initialize() {
        $this->hasMany('vendorId', '\Tourpage\Models\VendorsEvents', 'vendorId', array(
            'alias' => 'events'
        ));
        $this->hasMany('vendorId', '\Tourpage\Models\VendorsTours', 'vendorId', array(
            'alias' => 'vendorTours'
        ));
        $this->belongsTo('stateId', '\Tourpage\Models\State', 'stateId', array(
            'alias' => 'state'
        ));
        $this->belongsTo('countryId', '\Tourpage\Models\Country', 'countryId', array(
            'alias' => 'country'
        ));
        $this->belongsTo('vendorCategory', '\Tourpage\Models\CategoryVendor', 'categoryId', array(
            'alias' => 'category'
        ));
        $this->hasMany('vendorId', '\Tourpage\Models\VendorsTourTypes', 'vendorId', array(
            'alias' => 'vendorTourTypes'
        ));
        $this->hasMany('vendorId', '\Tourpage\Models\VendorsBanner', 'vendorId', array(
            'alias' => 'vendorBanners'
        ));
        $this->hasMany('vendorId', '\Tourpage\Models\MembersToVendor', 'vendorId', array(
            'alias' => 'memberToVendor'
        ));
        $this->hasMany('vendorId', '\Tourpage\Models\VendorsRegisteredAgents', 'vendorId', array(
            'alias' => 'registeredAgents'
        ));
        $this->hasMany('vendorId', '\Tourpage\Models\VendorsLocalAgents', 'vendorId', array(
            'alias' => 'localAgents'
        ));
        $this->hasOne('vendorId', '\Tourpage\Models\VendorsAcl', 'vendorId', array(
            'alias' => 'acl'
        ));
		$this->hasMany('vendorId', '\Tourpage\Models\GroupsVendors', 'vendorId', array(
            'alias' => 'group'
        ));
		$this->hasMany('vendorId', '\Tourpage\Models\GroupsTours', 'vendorId', array(
            'alias' => 'groupTours'
        ));
		$this->hasMany('vendorId', '\Tourpage\Models\VendorsMessages', 'vendorId', array(
            'alias' => 'vendorMessages'
        ));
        $this->keepSnapshots(true);
        $this->useDynamicUpdate(true);

        self::$di = $this;
    }

    /**
     * Implement Event beforeCreate()
     * Validating model data (emailAddress) before save (create)
     * @return boolean
     */
    public function beforeCreate() {
        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array("field" => "emailAddress", "message" => "User already exists with this email. " . self::$di->getDi()->getTag()->linkTo('/vendor/auth/reset/password', "Click here") . " if you forget password")));
        if ($this->validationHasFailed()) {
            return false;
        }
    }

    /**
     * Implements Event beforeUpdate()
     * Validation model data (emailAddress, nickName) before save (update)
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
     * Implements Event afterFetch()
     */
    public function afterFetch() {
        $socialMedia = unserialize($this->socialMedia);
        $this->sm = new \stdClass();
        $this->sm->links = [];
        if (isset($socialMedia['links'])) {
            if (isset($socialMedia['links']['facebook'])) {
                $this->sm->links['facebook'] = urldecode($socialMedia['links']['facebook']);
            }
            if (isset($socialMedia['links']['twitter'])) {
                $this->sm->links['twitter'] = urldecode($socialMedia['links']['twitter']);
            }
            if (isset($socialMedia['links']['instagram'])) {
                $this->sm->links['instagram'] = urldecode($socialMedia['links']['instagram']);
            }
        }
    }

    /**
     * Getting list of vendor tour types
     * @return array
     */
    public function getVTTypes() {
        $vtTypes = [];
        if (count($this->vendorTourTypes) > 0) {
            foreach ($this->vendorTourTypes as $vtType) {
                $vtTypes[] = \Tourpage\Models\TourTypes::findFirst($vtType);
            }
        }
        return $vtTypes;
    }

    /**
     * Getting Vendor Full Name
     * @return string
     */
    public function getName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Vendor Logo Uri
     * @param string $logo
     * @param bool $path
     * @return string
     */
    public function getLogoUri($logo = NULL, $path = FALSE) {
        if (!$logo) {
            $logo = $this->logo;
        }
        return $logo ? self::getUploadUri($this->vendorId, $path) . $logo : '';
    }
    
    public function getAboutUsBannerUri($aboutUsBanner = NULL, $path = FALSE) {
        if (!$aboutUsBanner) {
            $aboutUsBanner = $this->aboutUsBanner;
        }
        return $aboutUsBanner ? self::getUploadUri($this->vendorId, $path) . $aboutUsBanner : '';
    }

    /**
     * Vendor Avatar Uri
     * @param string $avatar
     * @param bool $path
     * @return string
     */
    public function getAvatarUri($avatar = NULL, $path = FALSE) {
        if (!$avatar) {
            $avatar = $this->avatar;
        }
        if ($avatar) {
            if (!file_exists(self::getUploadUri($this->vendorId, TRUE) . $avatar)) {
                $avatar = null;
            }
        }
        return $avatar ? self::getUploadUri($this->vendorId, $path) . $avatar : $this->getDi()->getUrl()->getStatic(FRONT_END_DIR . 'images/no-avt-1.png');
    }

    /**
     * Vendor Upload Directory
     * @param int $vendorId
     * @param bool $path
     * @return string
     */
    public static function getUploadUri($vendorId, $path = FALSE) {
        self::$di = new self;
        $uploadPath = '/uploads/tours/' . md5($vendorId) . '/';
        if (!$path) {
            return self::$di->getDi()->getUrl()->getStatic($uploadPath);
        } else {
            return self::$di->getDi()->getUrl()->getBasePath() . '/public/elements' . $uploadPath;
        }
    }

    /**
     * Getting Vendor Store Front Url
     * @return string
     */
    public function getStorFrontUri() {
        return $this->getDi()->getUrl()->getBaseUri() . '/store/index/' . ($this->isParent() ? $this->vendorId : $this->parentId);
    }

    /**
     * Getting status string for vendor
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->status) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
            case self::PENDING_APPROVAL_STATUS_CODE:
                return $escapeHtml ? 'Pending Approval' : '<span class="label label-info">Pending Approval</span>';
                break;
            case self::PENDING_EMAIL_VALIDATION_STATUS_CODE:
                return $escapeHtml ? 'Email Pending' : '<span class="label label-warning">Email Pending</span>';
                break;
        }
    }

    /**
     * Gatharing the status wheather this vendor
     * is main or parent vendor or is a child or employee
     * @return boolean
     */
    public function isParent() {
        return $this->parentId == 0;
    }

    /**
     * Fully remove vendor and their all referances
     */
    public function removeData() {
        //Removing Events like create_new_account etc.
        if ($this->events && $this->events->count() > 0) {
            $this->events->delete();
        }
        //Removing storefront banner
        if ($this->vendorBanners && $this->vendorBanners->count() > 0) {
            foreach ($this->vendorBanners as $banner) {
                $bannerUri = $banner->getBannerUri(TRUE);
                if (file_exists($bannerUri)) {
                    unlink($bannerUri);
                }
                $banner->delete();
            }
        }
        //Removing vendor TourTypes
        if ($this->vendorTourTypes && $this->vendorTourTypes->count() > 0) {
            $this->vendorTourTypes->delete();
        }
        //Removing tours
        if ($this->vendorTours && $this->vendorTours->count() > 0) {
            foreach ($this->vendorTours as $vendorTour) {
                $vendorTour->tour->removeData();
            }
            $uploadDir = self::getUploadUri($this->vendorId, true);
            if (file_exists($uploadDir)) {
                chmod($uploadDir, 0777);
                unlink($uploadDir);
            }
        }
        //Removing Logo
        $logoUri = $this->getLogoUri(NULL, TRUE);
        if (file_exists($logoUri)) {
            unlink($logoUri);
        }
        //Removing Banner
        $aboutUsBannerUri = $this->getAboutUsBannerUri(NULL, TRUE);
        if (file_exists($aboutUsBannerUri)) {
            unlink($aboutUsBannerUri);
        }
        //Removing Avatar
        $avatarUri = $this->getAvatarUri(NULL, TRUE);
        if (file_exists($avatarUri)) {
            unlink($avatarUri);
        }
        //Removing Members To Vendor Relations.
        if ($this->memberToVendor && $this->memberToVendor->count() > 0) {
            $this->memberToVendor->delete();
        }
        //Removing Registered Agents.
        if ($this->registeredAgents && $this->registeredAgents->count() > 0) {
            foreach ($this->registeredAgents as $registeredAgent) {
                $registeredAgent->removeData();
            }
        }
        //Removing Local Agents.
        if ($this->localAgents && $this->localAgents->count() > 0) {
            foreach ($this->localAgents as $localAgent) {
                $localAgent->removeData();
            }
        }
        //Removing Members To Vendor Relations.
        if ($this->acl && $this->acl->count() > 0) {
            $this->acl->removeData();
        }
        
        //Removing Employee If This Vendor Is Parent
        if ($this->isParent()) {
            $employees = self::find(array(
                'conditions' => 'parentId = :parent_id:',
                'bind' => array('parent_id' => $this->vendorId)
            ));
            if ($employees && $employees->count() > 0) {
                foreach ($employees as $employee) {
                    $employee->removeData();
                }
            }
        }
        //Removing vendor
        return $this->delete();
    }

}
