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

namespace Multiple\Frontend\Plugins;

use Phalcon\Tag;

/**
 * Plugins for HTML Tag Services
 * @author amit
 */
class Tags extends Tag {

    /**
     * Variable for Meta Elements
     * @var array contain HTML meta tag attributes
     */
    private $metaElement = [];

    /**
     * Setting Meta Tag Attributes Value
     * @param array $params
     */
    public function setMeta($params = array()) {
        if (!empty($params)) {
            $this->metaElement[] = $params;
        }
    }

    /**
     * Generating HTML Meta tag
     * @return string
     */
    public function getMeta() {
        $meta = '';
        if (count($this->metaElement) > 0) {
            foreach ($this->metaElement as $metaElement) {
                $meta .= $this->tagHtml('meta', $metaElement, TRUE, FALSE, TRUE);
            }
            $this->metaElement = [];
        }
        return $meta;
    }

    /**
     * Setting Meta Tag With Attribute Name "keyword"
     * @param string $value Content attribute value for meta tag
     */
    public function setKeywords($value = '') {
        if (!empty($value)) {
            $this->setMeta(array(
                'name' => 'keywords',
                'content' => $value
            ));
        }
    }

    /**
     * Setting Meta Tag With Attribute Name "description"
     * @param string $value Content attribute value for meta tag
     */
    public function setDescription($value = '') {
        if (!empty($value)) {
            $this->setMeta(array(
                'name' => 'description',
                'content' => $value
            ));
        }
    }

    /**
     * Setting Meta Tag With Attribute Name "viewport"
     * @param type $value Content attribute value for meta tag
     */
    public function setViewport($value = '') {
        if (!empty($value)) {
            $this->setMeta(array(
                'name' => 'viewport',
                'content' => $value
            ));
        }
    }

}
