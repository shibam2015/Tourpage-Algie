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
 * Model Members
 * @author amit
 */
class Members extends ApplicationModel {

    const AVATAR_ICON = '45x45';
    const AVATAR_THUMB = '150x150';
    const AVATAR_MEDIUM = '600x450';
    const IS_AGENT_STATUS_CODE = 1;
    const IS_NOT_AGENT_STATUS_CODE = 0;
    const NEWSLETTER_SUBSCRIBE_STATUS_CODE = 1;
    const NEWSLETTER_UNSUBSCRIBE_STATUS_CODE = 2;

    /**
     * @var array Member Data
     */
    public $data;

    /**
     * Initializing model
     */
    public function initialize() {
        $this->keepSnapshots(true);
        $this->useDynamicUpdate(true);
        $this->hasMany('memberId', '\Tourpage\Models\MembersEvents', 'memberId', array(
            'alias' => 'events'
        ));
        $this->belongsTo('stateId', '\Tourpage\Models\State', 'stateId', array(
            'alias' => 'state'
        ));
        $this->belongsTo('countryId', '\Tourpage\Models\Country', 'countryId', array(
            'alias' => 'country'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\Booking', 'memberId', array(
            'alias' => 'bookings'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\MembersTourReview', 'memberId', array(
            'alias' => 'tourReviews'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\MembersToVendor', 'memberId', array(
            'alias' => 'memberToVendor'
        ));
        $this->belongsTo('memberId', '\Tourpage\Models\VendorsRegisteredAgents', 'memberId', array(
            'alias' => 'agentWith'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\MembersCards', 'memberId', array(
            'alias' => 'cards'
        ));
        $this->belongsTo('currencyId', '\Tourpage\Models\Currency', 'currencyId', array(
            'alias' => 'currency'
        ));
        $this->belongsTo('languageId', '\Tourpage\Models\Language', 'languageId', array(
            'alias' => 'language'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\MembersAttractions', 'memberId', array(
            'alias' => 'attractions'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\MembersActivities', 'memberId', array(
            'alias' => 'activities'
        ));
        $this->hasMany('memberId', '\Tourpage\Models\MembersFavoriteTours', 'memberId', array(
            'alias' => 'favoriteTours'
        ));
		$this->hasMany('memberId', '\Tourpage\Models\MembersNotifications', 'memberId', array(
            'alias' => 'memberNotifications'
        ));
		$this->hasMany('memberId', '\Tourpage\Models\MembersMessages', 'memberId', array(
            'alias' => 'memberMessages'
        ));
        //$this->skipAttributesOnUpdate(array('nickName'));
    }

    /**
     * Implement Event beforeCreate()
     * Validating model data (emailAddress, nickName) before save (create)
     * @return boolean
     */
    
    
    ///closed by tarun
    public function beforeCreate() {
        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array("field" => "emailAddress", "message" => "Email Address must be unique")));
        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array("field" => "nickName", "message" => "Nickname must be unique")));
        if ($this->validationHasFailed()) {
            return false;
        }
    }
 ///closed by tarun   
    

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
        $memberData = unserialize($this->memberData);
        $this->data = new \stdClass();
        $this->data->cart = [];
        /*$this->data->favorite = new \stdClass();
        $this->data->favorite->tours = [];
        $this->data->favorite->vendors = [];*/
        if (isset($memberData['cart'])) {
            $this->data->cart = $memberData['cart'];
        }
        /*if (isset($memberData['favorite'])) {
            if (isset($memberData['favorite']['tours'])) {
                $this->data->favorite->tours = $memberData['favorite']['tours'];
            }
            if (isset($memberData['favorite']['vendors'])) {
                $this->data->favorite->vendors = $memberData['favorite']['vendors'];
            }
        }*/
    }

    /**
     * Getting Member Full Name
     * @return string
     */
    public function getName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Getting status string for member
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
        }
    }

    /**
     * Member avatar size map
     * @return array
     */
    public static function avatarSizes() {
        return array(
            'icon' => self::AVATAR_ICON,
            'thumb' => self::AVATAR_THUMB,
            'medium' => self::AVATAR_MEDIUM
        );
    }

    /**
     * Member Avatar Image Uri
     * @param string $size
     * @return string
     */
    public function getAvatarUri($size = '') {
        $baseLocation = $this->getDi()->getUrl()->getBasePath() . '/public/elements/uploads/avtr/' . md5($this->memberId) . '/';
        $avatar = $this->getDi()->getUrl()->getStatic('/uploads/avtr/' . md5($this->memberId) . '/' . $size . $this->avatar);
        if (!file_exists($baseLocation . $size . $this->avatar)) {
            $avatar = $this->getDi()->getUrl()->getStatic(FRONT_END_DIR . 'images/no-avt-1.png');
        }
        return $avatar;
    }

    /**
     * This member is an Agent to some vendor
     * @return boolean
     */
    public function isAgent() {
        $isAgent = FALSE;
        if ($this->isAgent == self::IS_AGENT_STATUS_CODE) {
            $isAgent = TRUE;
        }
        return $isAgent;
    }

    /**
     * Fully remove member and their all referances
     * Referances like
     * Events @see \Tourpage\Models\MembersEvents
     * @return bool
     */
    public function removeData() {
        //Removing Events like create_new_account etc.
        if ($this->events && $this->events->count() > 0) {
            $this->events->delete();
        }
        //Removing Bookings.
        if ($this->bookings && $this->bookings->count() > 0) {
            $this->bookings->removeData();
        }
        //Removing Reviews.
        if ($this->tourReviews && $this->tourReviews->count() > 0) {
            $this->tourReviews->removeData();
        }
        //Removing Members To Vendor Relations.
        if ($this->memberToVendor && $this->memberToVendor->count() > 0) {
            $this->memberToVendor->delete();
        }
        //Removing Agent With Vendor Relations.
        if ($this->agentWith && $this->agentWith->count() > 0) {
            $this->agentWith->removeData();
        }
        //Removing Saved Cards
        if ($this->cards && $this->cards->count() > 0) {
            foreach ($this->cards as $card) {
                $card->removeData();
            }
        }
        //Removing Attractions
        if ($this->attractions && $this->attractions->count() > 0) {
            foreach ($this->attractions as $attraction) {
                $attraction->removeData();
            }
        }
        //Removing Activities
        if ($this->activities && $this->activities->count() > 0) {
            foreach ($this->activities as $activity) {
                $activity->removeData();
            }
        }
        //Removing Favorite Tours
        if ($this->favoriteTours && $this->favoriteTours->count() > 0) {
            foreach ($this->favoriteTours as $favoriteTour) {
                $favoriteTour->removeData();
            }
        }
        //Removing Member
        return $this->delete();
    }

}
