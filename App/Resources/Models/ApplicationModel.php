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

namespace Tourpage\Models;

use Phalcon\Mvc\Model;

/**
 * Application Base Model Class
 * @author amit
 */
abstract class ApplicationModel extends Model {

    const ACTIVE_STATUS_CODE = 1;
    const INACTIVE_STATUS_CODE = 2;

    /**
     * Handles method calls when a method is not implemented
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments) {
        /**
         * If the magic method starts with "get" we try to get a service with that name
         */
        if (substr($method, 0, 3) == 'get') {
            $property = lcfirst(str_replace('get', '', $method));
            if (!method_exists($this, $method)) {
                return $this->{$property};
            }
        }

        /**
         * If the magic method starts with "set" we try to set a service using that name
         */
        if (substr($method, 0, 3) == 'set') {
            $property = lcfirst(str_replace('set', '', $method));
            if (!method_exists($this, $method)) {
                return $this->{$property} = $arguments[0];
            }
        }
    }

}
