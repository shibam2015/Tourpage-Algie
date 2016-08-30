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

namespace Multiple\Frontend\Plugins;

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

/*
 * Session Security Plugins
 */

class SessionSecurity extends Plugin {

    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
        if (!$this->member->isLoggedIn()) {
            if ($this->cookies->has('member_remember_me')) {
                $cookie = $this->cookies->get('member_remember_me');
                $memberId = $cookie->getValue();
                $member = \Tourpage\Models\Members::findFirst($memberId);
                if ($member && $member->count() > 0) {
                    $this->session->set('member', $member);
                    $this->response->redirect($this->router->getRewriteUri());
                }
            }
        }
        $role = 'Guests';
        if ($this->member->isLoggedIn()) {
            $role = 'Members';
        }
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        $acl = $this->getAcl();
        $allowed = $acl->isAllowed($role, $controller, $action);
        if ($allowed != Acl::ALLOW) {
            $dispatcher->forward(array(
                'module' => 'frontend',
                'controller' => 'auth',
                'action' => 'login',
            ));
            return false;
        }
    }

    private function getAcl() {
        $acl = new AclList();
        $acl->setDefaultAction(Acl::DENY);
        $roles = array(
            'members' => new Role('Members'),
            'guests' => new Role('Guests')
        );
        foreach ($roles as $role) {
            $acl->addRole($role);
        }
        $privateResources = array(
            'account' => array('*'),
        );
        foreach ($privateResources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        $publicResources = array(
            'index' => array('*'),
            'store' => array('*'),
            'tour' => array('*'),
            'captcha' => array('*'),
            'auth' => array('*'),
            'ajax' => array('*'),
            'cart' => array('*'),
            'payment' => array('*'),
        );
        foreach ($publicResources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        foreach ($roles as $role) {
            foreach ($publicResources as $resource => $actions) {
                $acl->allow($role->getName(), $resource, '*');
            }
        }
        foreach ($privateResources as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow('Members', $resource, $action);
            }
        }
        return $acl;
    }

}
