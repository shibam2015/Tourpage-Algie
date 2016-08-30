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

namespace Multiple\Frontend\Library;

use Phalcon\Mvc\User\Component;

/**
 * Member Component Library
 * @author amit
 */
class MemberComponent extends Component {

    const MEMBER_SES_KEY = 'member';

    /**
     * Member Object
     * @var object
     */
    private $membersData = null;

    /**
     * Member Account Navigation
     * @var array
     */
    private $accountMenus = array(
        'index' => array(
            'title' => 'Account Settings',
            //'glyphicon' => 'cog',
            'link' => '/account',
            'controller' => 'account',
            'action' => 'index',
            'active action' => ['index', 'paymentoptions', 'settings', 'changepass']
        ),
        'travelpref' => array(
            'title' => 'Travel Preference',
            //'glyphicon' => 'globe',
            'link' => '/account/travelpreference',
            'controller' => 'account',
            'action' => 'travelpreference',
        ),
        'reviews' => array(
            'title' => 'Reviews',
            //'glyphicon' => 'comment',
            'link' => '/account/reviews',
            'controller' => 'account',
            'action' => 'reviews',
        ),
        /* 'offers' => array(
          'title' => 'Points & Awards',
          //'glyphicon' => 'bullhorn',
          'link' => '/account/offers',
          'controller' => 'account',
          'action' => 'offers',
          ), */
        'tours' => array(
            'title' => 'My Tours',
            //'glyphicon' => 'plane',
            'link' => '/account/tours',
            'controller' => 'account',
            'action' => 'tours',
        ),
        'favorites' => array(
            'title' => 'Favorites',
            //'glyphicon' => 'heart',
            'link' => '/account/favorites',
            'controller' => 'account',
            'action' => 'favorites',
        ),
        'alert' => array(
            'title' => 'Alerts',
            //'glyphicon' => 'info-sign',
            'link' => '/account/alerts',
            'controller' => 'account',
            'action' => 'alerts',
        ),
    );

    /**
     * Class constructor
     */
    public function __construct() {
        $this->membersData = $this->session->get(self::MEMBER_SES_KEY);
    }

    /**
     * Determine wheather member is logged in or not
     * @return boolean
     */
    public function isLoggedIn() {
        if ($this->getResource()) {
            if ($this->getId() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Getting member Id
     * @return type Int
     */
    public function getId() {
        return $this->getResource()->memberId;
    }

    /**
     * Getting member Data
     * @return type object
     */
    public function getResource() {
        return $this->membersData;
    }

    /**
     * Getting member full real name
     * @return type string
     */
    public function getFullName() {
        return $this->getResource()->getName();
    }

    /**
     * Getting member nick name (unique)
     * @return string
     */
    public function getNickname() {
        return $this->getResource()->nickName;
    }

    /**
     * Getting member email address
     * @return type mix
     */
    public function getEmail() {
        return $this->getResource()->emailAddress;
    }

    /**
     * Getting member status
     * 1 for Active, 2 for Inactive
     * @return type int
     */
    public function getStatus() {
        return $this->getResource()->status;
    }

    /**
     * Refresh or Reload member data to session
     */
    public function refresh() {
        $this->session->remove(self::MEMBER_SES_KEY);
        $member = \Tourpage\Models\Members::findFirst($this->getId());
        $this->session->set(self::MEMBER_SES_KEY, $member);
        return $member;
    }

    /**
     * Check for tour in member favorite list
     * @param string $tourId
     * @return boolean
     */
    public function isFavoriteTour($tourId) {
        if ($this->isLoggedIn()) {
            $criteria = array(
                'tourId = :tour_id: AND memberId = :member_id:',
                'bind' => array(
                    'tour_id' => $tourId,
                    'member_id' => $this->getId()
                )
            );
            if (\Tourpage\Models\MembersFavoriteTours::count($criteria) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check for vendor in member favorite list
     * @param int $vendorId
     * @return boolean
     */
    public function isFavoriteVendor($vendorId) {
        return TRUE;
    }

    /**
     * Add tour or vendor to favorite list
     * @param mix $favoriteId
     * @param string $type
     */
    public function addToFavorite($favoriteId, $type = 'tour') {
        if ($this->isLoggedIn()) {
            if (!empty($favoriteId) && !empty($type)) {
                switch ($type) {
                    case 'tour':
                        $favoriteTours = new \Tourpage\Models\MembersFavoriteTours();
                        $favoriteTours->memberId = $this->getId();
                        $favoriteTours->tourId = $favoriteId;
                        $favoriteTours->addedOn = \Tourpage\Helpers\Utils::currentDate();
                        $favoriteTours->save();
                        break;
                    case 'vendor':
                        break;
                }
            }
        }
    }

    /**
     * Remove tour or vendor from favorite list
     * @param mix $favoriteId
     * @param string $type
     */
    public function removeFromFavorite($favoriteId, $type = 'tour') {
        if ($this->isLoggedIn()) {
            if (!empty($favoriteId) && !empty($type)) {
                switch ($type) {
                    case 'tour':
                        $criteria = array(
                            'tourId = :tour_id: AND memberId = :member_id:',
                            'bind' => array(
                                'tour_id' => $favoriteId,
                                'member_id' => $this->getId()
                            )
                        );
                        $favoriteTours = \Tourpage\Models\MembersFavoriteTours::findFirst($criteria);
                        if ($favoriteTours && $favoriteTours->count() > 0) {
                            return $favoriteTours->removeData();
                        }
                        break;
                    case 'vendor':
                        break;
                }
            }
        }
        return false;
    }

    /**
     * Clear or remove all items from favorite list
     */
    public function clearFavorites() {
        if ($this->isLoggedIn()) {
            $criteria = array(
                'memberId = :member_id:',
                'bind' => array(
                    'member_id' => $this->getId()
                )
            );
            $favoriteTours = \Tourpage\Models\MembersFavoriteTours::find($criteria);
            if ($favoriteTours && $favoriteTours->count() > 0) {
                foreach ($favoriteTours as $favoriteTour) {
                    $favoriteTour->removeData();
                }
            }
        }
    }
	
    /**
     * Get total number of item from favorite list
     * @return int
     */
    public function getTotalFavoriteItems() {
        $favoriteCount = 0;
        if ($this->isLoggedIn()) {
            $criteria = array(
                'memberId = :member_id:',
                'bind' => array(
                    'member_id' => $this->getId()
                )
            );
            $favoriteCount += \Tourpage\Models\MembersFavoriteTours::count($criteria);
        }
        return $favoriteCount;
    }
	
	/**
     * Get total number of item from alert list
     * @return int
     */
    public function getTotalAlertsItems() {
        $alertCount = 0;
        if ($this->isLoggedIn()) {
            $criteria = array(
                'memberId = :member_id: AND memberNotificationStatus = :notification_status:',
                'bind' => array(
                    'member_id' => $this->getId(),
					'notification_status' => \Tourpage\Models\MembersNotifications::UNREAD_STATUS_CODE
                )
            );
            $alertCount += \Tourpage\Models\MembersNotifications::count($criteria);
        }
        return $alertCount;
    }
	

    /**
     * Get all items from favorite list
     * @return array object
     */
    public function getFavoriteItems() {
        $favoriteItems = [];
        if ($this->isLoggedIn()) {
            if ($this->getTotalFavoriteItems() > 0) {
                $criteria = array(
                    'memberId = :member_id:',
                    'bind' => array(
                        'member_id' => $this->getId()
                    )
                );
                $favoriteTours = \Tourpage\Models\MembersFavoriteTours::find($criteria);
                if ($favoriteTours && $favoriteTours->count() > 0) {
                    foreach ($favoriteTours as $favoriteTour) {
                        $favoriteItems[strtotime($favoriteTour->addedOn)] = (object) [
                                    'id' => $favoriteTour->favoriteToursId,
                                    'type' => 'tour',
                                    'addedOn' => $favoriteTour->addedOn,
                                    'model' => $favoriteTour->tour
                        ];
                    }
                }
            }
            if (count($favoriteItems) > 0) {
                krsort($favoriteItems);
            }
        }
        return $favoriteItems;
    }

    /**
     * Member Account Menu
     * Generating menu element for member account
     * @return string
     */
    public function accountMenu() {
        $menuHtml = '';
        if (count($this->accountMenus) > 0) {
            $activeController = $this->view->getControllerName();
            $activeAction = $this->view->getActionName();
            $menuHtml .= '<ul class="menu_tap">';
            foreach ($this->accountMenus as $accountMenu) {
                $cssClassList = $attributesLink = array();
                $cssClassLink[] = 'menu-' . $accountMenu['controller'] . '-' . $accountMenu['action'];
                if ($accountMenu['controller'] == $activeController) {
                    if (isset($accountMenu['active action'])) {
                        if (in_array($activeAction, $accountMenu['active action'])) {
                            $cssClassList[] = 'active';
                        }
                    } else {
                        if ($accountMenu['action'] == $activeAction) {
                            $cssClassList[] = 'active';
                        }
                    }
                }
                $menuHtml .= '<li' . (count($cssClassList) > 0 ? ' class="' . implode($cssClassList, ' ') . '"' : '') . '>';
                if (isset($accountMenu['title']) && $accountMenu['title'] != '') {
                    if (isset($accountMenu['glyphicon']) && !empty($accountMenu['glyphicon'])) {
                        $accountMenu['title'] = '<i class="glyphicon glyphicon-' . $accountMenu['glyphicon'] . '"></i> ' . $accountMenu['title'];
                    }
                    $menuHtml .= $this->tag->linkTo(array((isset($accountMenu['link']) ? $accountMenu['link'] : '#'), $accountMenu['title'], 'class' => implode($cssClassLink, ' ')));
                }
                $menuHtml .= '</li>';
            }
            $menuHtml .= '</ul>';
        }
        return $menuHtml;
    }

    /**
     * Getting the member avatar image uri
     * @param string $size
     * @return string
     */
    public function avatarUri($size = '') {
        return $this->getResource()->getAvatarUri($size);
    }

}
