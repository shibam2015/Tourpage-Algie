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
foreach (array('frontend', 'vendor', 'backend') as $modules) {
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

$router->add('/admin/settings/([a-z]+)/:controller/:action/:params', array(
    'namespace' => 'Multiple\Backend\Controllers\Settings',
    'module' => 'backend',
    'controller' => 2,
    'action' => 3,
    'params' => 4
));
$router->handle();
