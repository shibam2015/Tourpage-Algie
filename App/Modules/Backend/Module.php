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

namespace Multiple\Backend;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Backend Module
 */
class Module implements ModuleDefinitionInterface {

    /**
     * Register a specific autoloader for the module
     */
    public function registerAutoloaders(\Phalcon\DiInterface $di = NULL) {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'Multiple\Backend\Controllers' => BACK_END_PATH . 'Controllers/',
            'Multiple\Backend\Controllers\Settings' => BACK_END_PATH . 'Controllers/Settings/',
            'Multiple\Backend\Library' => BACK_END_PATH . 'Library/',
            'Multiple\Backend\Plugins' => BACK_END_PATH . 'Plugins/',
            'Multiple\Backend\Forms' => BACK_END_PATH . 'Forms/',
        ));
        $loader->register();
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(\Phalcon\DiInterface $di) {
        // Registering a dispatcher
        $di->set('dispatcher', function() {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeDispatch', new \Multiple\Backend\Plugins\SessionSecurity());
            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace("Multiple\Backend\Controllers");
            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(BACK_END_PATH . 'Views/');
            return $view;
        });

        // Register a GUI element
        $di->set('element', function() {
            return new \Multiple\Backend\Plugins\Elements();
        });

        // Register a vendor component
        $di->set('admin', function() {
            return new \Multiple\Backend\Library\AdminComponent();
        });
    }

}
