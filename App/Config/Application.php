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

use Phalcon\Mvc\Application;

// Handle the request
$application = new Application($di);

// Register the installed modules
$application->registerModules(array(
    'frontend' => array(
        'className' => 'Multiple\Frontend\Module',
        'path' => FRONT_END_PATH . 'Module.php',
    ),
    'backend' => array(
        'className' => 'Multiple\Backend\Module',
        'path' => BACK_END_PATH . 'Module.php',
    ),
    'vendor' => array(
        'className' => 'Multiple\Vendor\Module',
        'path' => VENDOR_PATH . 'Module.php',
    ),
    'supplier' => array(
        'className' => 'Multiple\Supplier\Module',
        'path' => SUPPLIER_PATH . 'Module.php',
    )
));

echo $application->handle()->getContent();
