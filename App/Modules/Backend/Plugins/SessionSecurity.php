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

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

/**
 * Session Security Plugins
 */
class SessionSecurity extends Plugin {

    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
        if (!$this->admin->isLoggedIn()) {
            $controller = $dispatcher->getControllerName();
            if ($controller != 'auth') {
                return $dispatcher->forward(array(
                            'module' => 'backend',
                            'controller' => 'auth',
                            'action' => 'login',
                ));
            }
        }
    }

}
