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

namespace Multiple\Supplier\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

/*
 * Session Security Plugins
 */

class SessionSecurity extends Plugin {

public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
        //$controller = $dispatcher->getControllerName();
        //$action = $dispatcher->getActionName();
        //if (!$this->supplier->isLoggedIn()) {
//            if ($this->cookies->has('remember_me')) {
//                $cookie = $this->cookies->get('remember_me');
//                $vendorId = $cookie->getValue();
//                $vendors = \Tourpage\Models\Vendors::findFirstByVendorId($vendorId);
//                if ($vendors && $vendors->count() > 0) {
//                    $this->session->set('vendors', $vendors);
//                    $this->response->redirect($this->router->getRewriteUri());
//                }
//            }
//            if ($controller != 'auth') {
//                return $dispatcher->forward(array(
//                            'module' => 'vendor',
//                            'controller' => 'auth',
//                            'action' => 'login',
//                ));
//            }
//        } else {
//            if (!$this->vendors->isAllowed($controller, $action)) {
//                return $dispatcher->forward(array(
//                            'module' => 'vendor',
//                            'controller' => 'site',
//                            'action' => 'error404',
//                ));
//            }
        //}
        
//        return $dispatcher->forward(array(
//                            'module' => 'supplier',
//                            'controller' => 'index',
//                            'action' => 'index',
//                ));
        
        
//                return $dispatcher->forward(array(
//                            'module' => 'supplier',
//                            'controller' => 'auth',
//                            'action' => 'login',
//                ));
}
}
