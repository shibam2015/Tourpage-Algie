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

namespace Multiple\Supplier;

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
            'Multiple\Supplier\Controllers' => SUPPLIER_PATH . 'Controllers/',
//            'Multiple\Supplier\Controllers\Settings' => SUPPLIER_PATH . 'Controllers/Settings/',
//            'Multiple\Supplier\Library' => SUPPLIER_PATH . 'Library/',
            'Multiple\Supplier\Plugins' => SUPPLIER_PATH . 'Plugins/'
//            'Multiple\Supplier\Forms' => SUPPLIER_PATH . 'Forms/',
        ));
        $loader->register();
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(\Phalcon\DiInterface $di) {
        $di->set('dispatcher', function() {
            $eventsManager = new EventsManager();
            //$eventsManager->attach('dispatch:beforeDispatch', new \Multiple\Supplier\Plugins\SessionSecurity());
            $dispatcher = new Dispatcher();
            //$dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace("Multiple\Supplier\Controllers");
            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(SUPPLIER_PATH . 'Views/');
            return $view;
        });

       
    }
}
