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

$router = new \Phalcon\Mvc\Router(false);
$router->setDefaultModule("frontend");
$i = 0;
foreach (array('frontend', 'vendor', 'backend','supplier') as $modules) {
    $modulesUrl = '/';
    if ($modules != 'frontend') {
        $modulesUrl .= ($modules != 'backend' ? $modules : 'admin');
    }

    $router->add($modulesUrl, array(
        'module' => $modules,
        'controller' => "index",
        'action' => "index"
    ));

    if ($modules != 'frontend') {
        if ($i > 0) {
            $modulesUrl .= '/';
            $router->add($modulesUrl, array(
                'module' => $modules,
                'controller' => "index",
                'action' => "index"
            ));
        }
    }

    $router->add($modulesUrl . ':controller', array(
        'module' => $modules,
        'controller' => 1,
        'action' => "index"
    ));

    $router->add($modulesUrl . ':controller/', array(
        'module' => $modules,
        'controller' => 1,
        'action' => "index"
    ));

    $router->add($modulesUrl . ':controller/:action/', array(
        'module' => $modules,
        'controller' => 1,
        'action' => 2
    ));

    $router->add($modulesUrl . ':controller/:action/:params', array(
        'module' => $modules,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ));
    $i++;
}


// ----------------- storefront route begin  -------------------------
// about us route
$router->add('/:params/aboutus', array(
    'module' => 'frontend',
    'controller' => 'store',
    'action' => 'aboutus',
    'params' => 1
));

// store us route
$router->add('/:params/store', array(
    'module' => 'frontend',
    'controller' => 'store',
    'action' => 'index',
    'params' => 1
));

// out tours
// store us route
$router->add('/:params/ourtours', array(
    'module' => 'frontend',
    'controller' => 'store',
    'action' => 'tours',
    'params' => 1
));

//our tour details
$router->add('/([a-z0-9_-]+){2}/ourtours/([a-z0-9_-]+)', array(
    'module' => 'frontend',
    'controller' => 'tour',
    'action' => 'index',
));

// contacts route
$router->add('/([a-z0-9_-]+){2}/contacts', array(
    'module' => 'frontend',
    'controller' => 'store',
    'action' => 'contacts'
));

// contacts route
$router->add('/([a-z0-9_-]+){2}/toursgallery', array(
    'module' => 'frontend',
    'controller' => 'store',
    'action' => 'toursgallery'
));
// ---------------- storefront route end -----------------------------

$router->add('/admin/settings/([a-z]+)/:controller/:action/:params', array(
    'namespace' => 'Multiple\Backend\Controllers\Settings',
    'module' => 'backend',
    'controller' => 2,
    'action' => 3,
    'params' => 4
));

//$router->add('/supplier/settings/([a-z]+)/:controller/:action/:params', array(
//    'namespace' => 'Multiple\Supplier\Controllers\Settings',
//    'module' => 'supplier',
//    'controller' => 2,
//    'action' => 3,
//    'params' => 4
//));
$router->handle();
