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

use Phalcon\Loader;

//Create a Loader
$loader = new Loader();
$loader->registerNamespaces(array(
    'Tourpage\Models' => RESOURCES_PATH . 'Models/',
    'Tourpage\Helpers' => RESOURCES_PATH . 'Helpers/',
    'Tourpage\Library' => RESOURCES_PATH . 'Library/',
    'Tourpage\Library\Validator' => RESOURCES_PATH . 'Library/Validator/',
    'Tourpage\Forms' => RESOURCES_PATH . 'Forms/',
    'PayPal\Api' => RESOURCES_PATH . 'Library/PayPal/Api/',
    'PayPal\Auth' => RESOURCES_PATH . 'Library/PayPal/Auth/',
    'PayPal\Cache' => RESOURCES_PATH . 'Library/PayPal/Cache/',
    'PayPal\Common' => RESOURCES_PATH . 'Library/PayPal/Common/',
    'PayPal\Converter' => RESOURCES_PATH . 'Library/PayPal/Converter/',
    'PayPal\Core' => RESOURCES_PATH . 'Library/PayPal/Core/',
    'PayPal\Exception' => RESOURCES_PATH . 'Library/PayPal/Exception/',
    'PayPal\Handler' => RESOURCES_PATH . 'Library/PayPal/Handler/',
    'PayPal\Rest' => RESOURCES_PATH . 'Library/PayPal/Rest/',
    'PayPal\Security' => RESOURCES_PATH . 'Library/PayPal/Security/',
    'PayPal\Transport' => RESOURCES_PATH . 'Library/PayPal/Transport/',
    'PayPal\Validation' => RESOURCES_PATH . 'Library/PayPal/Validation/'
));
$loader->register();
