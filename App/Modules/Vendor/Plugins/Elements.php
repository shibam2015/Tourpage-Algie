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

namespace Multiple\Vendor\Plugins;

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
            'title' => 'Home',
            'title before' => '<i class="fa fa-home"></i> ',
            'link' => '/',
            'controller' => 'index',
            'action' => 'index',
        ),
        'tours' => array(
            'title' => 'Tours',
            'title before' => '<i class="fa fa-plane"></i> ',
            'link' => '/tours',
            'controller' => 'tours',
            'action' => 'index',
            'child' => array(
                'manage_tours' => array(
                    'title' => 'Mange Tours',
                    'title before' => '<i class="fa fa-bars"></i> ',
                    'link' => '/tours',
                    'controller' => 'tours',
                    'action' => 'index'
                ),
                'new_tours' => array(
                    'title' => 'New Tour',
                    'title before' => '<i class="fa fa-plus"></i> ',
                    'link' => '/tours/add',
                    'controller' => 'tours',
                    'action' => 'add'
                ),
                'tours_promotion' => array(
                    'title' => 'Promotional Discount',
                    'title before' => '<i class="fa fa-smile-o"></i> ',
                    'link' => '/tours/promotions',
                    'controller' => 'tours',
                    'action' => 'promotions'
                ),
                'tours_reviews' => array(
                    'title' => 'Reviews',
                    'title before' => '<i class="fa fa-comment-o"></i> ',
                    'link' => '/tours/reviews',
                    'controller' => 'tours',
                    'action' => 'reviews'
                ),
            ),
        ),
        'booking' => array(
            'title' => 'Booking',
            'title before' => '<i class="fa fa-book"></i> ',
            'link' => '/booking',
            'controller' => 'booking',
            'action' => 'index',
            'child' => array(
                'manage_booking' => array(
                    'title' => 'Manage Bookings',
                    'title before' => '<i class="fa fa-bars"></i> ',
                    'link' => '/booking',
                    'controller' => 'booking',
                    'action' => 'index'
                ),
                'new_booking' => array(
                    'title' => 'New Booking',
                    'title before' => '<i class="fa fa-plus"></i> ',
                    'link' => '/booking/add',
                    'controller' => 'booking',
                    'action' => 'add'
                ),
				
                )
                ),
		'groups' => array(
            'title' => 'Groups',
            'title before' => '<i class="fa fa-book"></i> ',
            'link' => '/groups',
            'controller' => 'groups',
            'action' => 'index',
            'child' => array(
                'manage_groups' => array(
                    'title' => 'Manage Groups',
                    'title before' => '<i class="fa fa-bars"></i> ',
                    'link' => '/groups',
                    'controller' => 'groups',
                    'action' => 'index'
                ),
                'new_group' => array(
                    'title' => 'New Group',
                    'title before' => '<i class="fa fa-plus"></i> ',
                    'link' => '/groups/add',
                    'controller' => 'groups',
                    'action' => 'add'
                ),
				'group_order' => array(
                    'title' => 'Group Order',
                    'title before' => '<i class="fa fa-random"></i> ',
                    'link' => '/groups/order',
                    'controller' => 'groups',
                    'action' => 'order'
                ),
                'map_tour' => array(
                    'title' => 'Map Tour',
                    'title before' => '<i class="fa fa-random"></i> ',
                    'link' => '/groups/map',
                    'controller' => 'groups',
                    'action' => 'map'
                ),
                'list_tour' => array(
                    'title' => 'List Mapped Tour',
                    'title before' => '<i class="fa fa-random"></i> ',
                    'link' => '/groups/list',
                    'controller' => 'groups',
                    'action' => 'list'
                )
            )
        ),
        'members' => array(
            'title' => 'Users',
            'title before' => '<i class="fa fa-user"></i> ',
            'link' => '/members',
            'controller' => 'members',
            'action' => 'index',
        ),
        'agents' => array(
            'title' => 'Agents',
            'title before' => '<i class="fa fa-users"></i> ',
            'link' => '/agents',
            'controller' => 'agents',
            'action' => 'index',
            'child' => array(
                'manage_local_agents' => array(
                    'title' => 'Local Agents',
                    'title before' => '<i class="fa fa-bars"></i> ',
                    'link' => '/agents/local',
                    'controller' => 'agents',
                    'action' => 'local'
                ),
                'manage_registered_agents' => array(
                    'title' => 'Registered Agents',
                    'title before' => '<i class="fa fa-bars"></i> ',
                    'link' => '/agents/registered',
                    'controller' => 'agents',
                    'action' => 'registered'
                ),
                'new_local_agents' => array(
                    'title' => 'New Local Agents',
                    'title before' => '<i class="fa fa-plus"></i> ',
                    'link' => '/agents/add',
                    'controller' => 'agents',
                    'action' => 'add'
                ),
            )
        ),
        'employees' => array(
            'title' => 'Employees',
            'title before' => '<i class="fa fa-male"></i> ',
            'link' => '/employees',
            'controller' => 'employees',
            'action' => 'index',
            'child' => array(
                'manage_employees' => array(
                    'title' => 'Manage Employees',
                    'title before' => '<i class="fa fa-bars"></i> ',
                    'link' => '/employees',
                    'controller' => 'employees',
                    'action' => 'index'
                ),
                'new_employees' => array(
                    'title' => 'New Employee',
                    'title before' => '<i class="fa fa-plus"></i> ',
                    'link' => '/employees/add',
                    'controller' => 'employees',
                    'action' => 'add'
                ),
            )
        )
    );

    /**
     * Top Right Nav Menu
     * @var type array
     */
    private $mainMenuRight = array(
        'user' => array(
            'title before' => '<i class="fa fa-user"></i> ',
            'link' => '#',
            'controller' => 'account',
            'action' => 'index',
            'child' => array(
                'store_front' => array(
                    'title' => 'Store Front',
                    'title before' => '<i class="fa fa-external-link"></i> ',
                    'link' => '/store/front',
                    'outer' => true,
                    'controller' => 'store',
                    'action' => 'view',
                    'target' => '_blank',
                ),
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
            if ($this->vendors->isAllowed($menu['controller'], $menu['action'])) {
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
                if (isset($menu['outer'])) {
                    $attributesLink['local'] = FALSE;
                }
                $menuHtml .= '<li' . (count($cssClassList) > 0 ? ' class="' . implode($cssClassList, ' ') . '"' : '') . '>';
                if (isset($menu['title']) && $menu['title'] != '') {
                    $menuHtml .= $this->tag->linkTo(array((isset($menu['link']) ? (isset($menu['outer']) && $menu['outer'] ? '' : '/vendor') . $menu['link'] : '#'), $menu['title'], 'class' => implode($cssClassLink, ' ')) + $attributesLink);
                    if (isset($menu['child']) && count($menu['child']) > 0) {
                        $this->getMenuRecursion($menu['child'], 'dropdown-menu');
                    }
                }
                $menuHtml .= '</li>';
            }
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
                $this->mainMenuRight['user']['child']['store_front']['link'] = $this->vendors->getVendorData()->getStorFrontUri();
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
        return $this->url->get('/vendor' . $menuData['link']);
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
                'title' => 'Account Setting',
                'title before' => '<i class="fa fa-pencil-square-o"></i> ',
                'link' => '/account/edit',
                'controller' => 'account',
                'action' => 'edit',
            ),
            'reset_store' => array(
                'title' => 'Store Setting',
                'title before' => '<i class="fa fa-shopping-cart"></i> ',
                'link' => '/account/storesetting',
                'controller' => 'account',
                'action' => 'store',
            ),
            'banner' => array(
                'title' => 'Banners Setting',
                'title before' => '<i class="fa fa-picture-o"></i> ',
                'link' => '/account/storebanner',
                'controller' => 'account',
                'action' => 'storebanner',
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
            if ($this->vendors->isAllowed($menu['controller'], $menu['action'])) {
                $cssClassLink = array();
                $cssClassLink[] = 'menu-' . $menu['action'];
                $cssClassLink[] = 'list-group-item';
                if ($menu['controller'] == $activeController && $menu['action'] == $activeAction) {
                    $cssClassLink[] = 'active';
                }
                if (!isset($menu['title'])) {
                    $menu['title'] = '';
                }
                $menu['title'] = (isset($menu['title before']) ? $menu['title before'] : '') . $menu['title'];
                $menuHtml .= $this->tag->linkTo(array((isset($menu['link']) ? (isset($menu['outer']) && $menu['outer'] ? '' : '/vendor') . $menu['link'] : '#'), $menu['title'], 'class' => implode($cssClassLink, ' ')));
            }
        }
        $menuHtml .= '</div>';
        return $menuHtml;
    }

}
