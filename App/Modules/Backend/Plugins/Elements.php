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

namespace Multiple\Backend\Plugins;

use Phalcon\Mvc\User\Component;

/**
 * Elements
 * Helps to build UI elements for the application
 */
class Elements extends Component {

    /**
     * Top Left Nav Menu
     * @var type array
     */
    private $mainMenuLeft = array(
        'home' => array(
            'title' => 'Dashboard',
            'link' => '/',
            'controller' => 'index',
            'action' => 'index',
        ),
        'tours' => array(
            'title' => 'Tours',
            'link' => '/tours',
            'controller' => 'tours',
            'action' => 'index',
            'child' => array(
                'manage_tours' => array(
                    'title' => 'Manage Tours',
                    'link' => '/tours',
                    'controller' => 'tours',
                    'action' => 'index'
                ),
                'manage_tours_category' => array(
                    'title' => 'Category',
                    'link' => '/tours/category',
                    'controller' => 'tours',
                    'action' => 'category'
                ),
                'manage_tours_details_field' => array(
                    'title' => 'Description Fields',
                    'link' => '/tours/descfields',
                    'controller' => 'tours',
                    'action' => 'descfields'
                ),
                'manage_tours_key_highlight' => array(
                    'title' => 'Key highlights',
                    'link' => '/tours/keyhighlight',
                    'controller' => 'tours',
                    'action' => 'keyhighlight'
                ),
            ),
        ),
        'vendors' => array(
            'title' => 'Vendors',
            'link' => '/vendors',
            'controller' => 'vendors',
            'action' => 'index',
            'child' => array(
                'manage_vendors' => array(
                    'title' => 'Manage Vendors',
                    'link' => '/vendors',
                    'controller' => 'vendors',
                    'action' => 'index'
                ),
                'manage_vendors_category' => array(
                    'title' => 'Category',
                    'link' => '/vendors/category',
                    'controller' => 'vendors',
                    'action' => 'category'
                ),
                'manage_vendors_tour_types' => array(
                    'title' => 'Tour & Activities',
                    'link' => '/vendors/touractivities',
                    'controller' => 'vendors',
                    'action' => 'touractivities',
                ),
            ),
        ),
        'customers' => array(
            'title' => 'Customers',
            'link' => '/customers',
            'controller' => 'customers',
            'action' => 'index',
            'child' => array(
                'manage_users' => array(
                    'title' => 'Manage Customers',
                    'link' => '/customers',
                    'controller' => 'customers',
                    'action' => 'index'
                ),
                'manage_reviews' => array(
                    'title' => 'Reviews & Rating',
                    'link' => '/customers/reviews',
                    'controller' => 'customers',
                    'action' => 'reviews'
                ),
                'manage_attractions' => array(
                    'title' => 'Place of Attractions',
                    'link' => '/customers/attractions',
                    'controller' => 'customers',
                    'action' => 'attractions'
                ),
                'manage_activities' => array(
                    'title' => 'Type of activities',
                    'link' => '/customers/activities',
                    'controller' => 'customers',
                    'action' => 'activities'
                ),
				'manage_notifications' => array(
                    'title' => 'Notifications',
                    'link' => '/customers/notifications',
                    'controller' => 'customers',
                    'action' => 'notifications'
                ),
            ),
        ),
        'bookings' => array(
            'title' => 'Bookings',
            'link' => '/booking',
            'controller' => 'booking',
            'action' => 'index',
        ),
		'messages' => array(
            'title' => 'Messages',
            'link' => '/message',
            'controller' => 'message',
            'action' => 'index',
        ),
        'ads' => array(
            'title' => 'Ads & Banner',
            'link' => '/settings/banner/ads/index/home',
            'controller' => 'ads',
            'action' => '',
        ),
    );

    /**
     * Top Right Nav Menu
     * @var type array
     */
    private $mainMenuRight = array(
        'user' => array(
            'title before' => '<i class="fa fa-user"></i> ',
            'link' => '#',
            'controller' => 'accountt',
            'action' => 'index',
            'child' => array(
                'account' => array(
                    'title' => 'Account',
                    'title before' => '<i class="fa fa-cog"></i> ',
                    'link' => '/account',
                    'controller' => 'account',
                    'action' => 'index'
                ),
                'logout' => array(
                    'title' => 'Logout',
                    'title before' => '<i class="fa fa-sign-out"></i> ',
                    'link' => '/auth/logout',
                    'controller' => 'auth',
                    'action' => 'logout'
                ),
            ),
        ),
    );

    /**
     * Callback recursive menu function
     */
    private function getMenuRecursion($mainMenu, $ulCssClass = '') {
        global $menuHtml;
        $activeController = $this->view->getControllerName();
        $activeAction = $this->view->getActionName();
        $menuHtml .= '<ul class="' . ($ulCssClass != '' ? $ulCssClass : 'nav navbar-nav') . '">';
        foreach ($mainMenu as $menu) {
            $cssClassList = $cssClassLink = $attributesLink = array();
            $cssClassLink[] = 'menu-' . $menu['action'];
            if ($menu['controller'] == $activeController && $menu['action'] == $activeAction) {
                //$cssClassList[] = 'active';
            }
            if (!isset($menu['title'])) {
                $menu['title'] = '';
            }
            $menu['title'] = (isset($menu['title before']) ? $menu['title before'] : '') . $menu['title'];
            if (isset($menu['child']) && count($menu['child']) > 0) {
                $cssClassList[] = 'dropdown';
                $cssClassLink[] = 'dropdown-toggle';
                $menu['link'] = '#';
                $menu['title'] = $menu['title'] . ' <span class="caret"></span>';
                $attributesLink['data-toggle'] = 'dropdown';
                $attributesLink['role'] = 'button';
                $attributesLink['aria-haspopup'] = 'true';
                $attributesLink['aria-expanded'] = 'false';
            }
            if (isset($menu['target'])) {
                $attributesLink['target'] = $menu['target'];
            }
            $menuHtml .= '<li' . (count($cssClassList) > 0 ? ' class="' . implode($cssClassList, ' ') . '"' : '') . '>';
            if (isset($menu['title']) && $menu['title'] != '') {
                $menuHtml .= $this->tag->linkTo(array((isset($menu['link']) ? (isset($menu['outer']) && $menu['outer'] ? '' : '/admin') . $menu['link'] : '#'), $menu['title'], 'class' => implode($cssClassLink, ' ')) + $attributesLink);
                if (isset($menu['child']) && count($menu['child']) > 0) {
                    $this->getMenuRecursion($menu['child'], 'dropdown-menu');
                }
            }
            $menuHtml .= '</li>';
        }
        $menuHtml .= '</ul>';
    }

    /**
     * Generating Main Menu
     */
    public function getMenu() {
        global $menuHtml;
        $this->getMenuRecursion($this->mainMenuLeft);
        if (isset($this->mainMenuRight['user']['child'])) {
            if (isset($this->mainMenuRight['user']['child']['store_front'])) {
                $this->mainMenuRight['user']['child']['store_front']['link'] = '/store/index/' . $this->admin->getId();
            }
        }
        $this->getMenuRecursion($this->mainMenuRight, 'nav navbar-nav navbar-right');
        return $menuHtml;
    }

    /**
     * Getting single menu item url
     * @param string $itemKey
     * @return string
     */
    public function getMainMenuItemUrl($itemKey) {
        $keys = explode('|', $itemKey);
        $keys = array_filter($keys);
        $menuData = $this->mainMenuLeft[$keys[0]];
        if (count($keys) > 1) {
            if (isset($this->mainMenuLeft[$keys[0]]['child'][$keys[1]])) {
                $menuData = $this->mainMenuLeft[$keys[0]]['child'][$keys[1]];
            }
        }
        return $this->url->get('/admin' . $menuData['link']);
    }

    /**
     * Account Setting Menu
     * @return string
     */
    public function getAccountMenu() {
        $accountMenu = array(
            'accont_overview' => array(
                'title' => 'Account Overview',
                'title before' => '<i class="fa fa-server"></i> ',
                'link' => '/account',
                'controller' => 'account',
                'action' => 'index',
            ),
            'reset_account' => array(
                'title' => 'Account Edit',
                'title before' => '<i class="fa fa-pencil-square-o"></i> ',
                'link' => '/account/edit',
                'controller' => 'account',
                'action' => 'edit',
            ),
            'reset_password' => array(
                'title' => 'Reset Password',
                'title before' => '<i class="fa fa-shield"></i> ',
                'link' => '/account/resetpassword',
                'controller' => 'account',
                'action' => 'resetpassword',
            ),
        );
        $activeController = $this->view->getControllerName();
        $activeAction = $this->view->getActionName();
        $menuHtml = '<div class="list-group">';
        foreach ($accountMenu as $menu) {
            $cssClassLink = array();
            $cssClassLink[] = 'menu-' . $menu['action'];
            $cssClassLink[] = 'list-group-item';
            $cssClassLink[] = 'list-group-item-danger';
            if ($menu['controller'] == $activeController && $menu['action'] == $activeAction) {
                //$cssClassLink[] = 'active';
            }
            if (!isset($menu['title'])) {
                $menu['title'] = '';
            }
            $menu['title'] = (isset($menu['title before']) ? $menu['title before'] : '') . $menu['title'];
            $menuHtml .= $this->tag->linkTo(array((isset($menu['link']) ? (isset($menu['outer']) && $menu['outer'] ? '' : '/admin') . $menu['link'] : '#'), $menu['title'], 'class' => implode($cssClassLink, ' ')));
        }
        $menuHtml .= '</div>';
        return $menuHtml;
    }

}
