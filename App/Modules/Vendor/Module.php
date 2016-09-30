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

namespace Multiple\Vendor;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ModuleDefinitionInterface;

/*
 * Vendor Module
 */

class Module implements ModuleDefinitionInterface {

    /**
     * Register a specific autoloader for the module
     */
    public function registerAutoloaders(\Phalcon\DiInterface $di = NULL) {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'Multiple\Vendor\Controllers' => VENDOR_PATH . 'Controllers/',
            'Multiple\Vendor\Library' => VENDOR_PATH . 'Library/',
            'Multiple\Vendor\Plugins' => VENDOR_PATH . 'Plugins/',
            'Multiple\Vendor\Forms' => VENDOR_PATH . 'Forms/',
        ));
        $loader->register();
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(\Phalcon\DiInterface $di) {
        // Registering a dispatcher
        $di->set('dispatcher', function() use ($di) {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeDispatch', new \Multiple\Vendor\Plugins\SessionSecurity());
            $eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception) {
                switch ($exception->getCode()) {
                    case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                        $dispatcher->forward(array(
                            'controller' => 'site',
                            'action' => 'error404',
                        ));
                        return false;
                        break;
                    case Dispatcher::EXCEPTION_NO_DI:
                    case Dispatcher::EXCEPTION_CYCLIC_ROUTING:
                    case Dispatcher::EXCEPTION_INVALID_HANDLER:
                    case Dispatcher::EXCEPTION_INVALID_PARAMS:
                        $dispatcher->forward(array(
                            'controller' => 'site',
                            'action' => 'error500',
                        ));
                        return false;
                        break;
                }
            });
            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace("Multiple\Vendor\Controllers");
            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(VENDOR_PATH . 'Views/');
            return $view;
        });

        // Register a element
        $di->set('elements', function() {
            return new \Multiple\Vendor\Plugins\Elements();
        });

        // Register a vendor component
        $di->set('vendors', function() {
            return new \Multiple\Vendor\Library\VendorsComponent();
        });
        $di->set('flashSession', function(){
            $flash = new \Phalcon\Flash\Session(array(
                'error' => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice' => 'alert alert-info',
                'warning' => 'alert alert-warning'
            ));
            return $flash;
        });
    }

}
