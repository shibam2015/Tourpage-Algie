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

use Phalcon\Mvc\Model\Message as Message;

/**
 * Model CategoryVendor for Vendors Category
 * @author amit
 */
class CategoryVendor extends ApplicationModel {

    /**
     * Initializing Model Vendors Category
     */
    public function initialize() {
        $this->hasMany('categoryId', '\Tourpage\Models\Vendors', 'vendorCategory', array(
            'alias' => 'vendors'
        ));
    }

    /**
     * Check for child category
     * @return bool
     */
    public function hasChild() {
        $numberOfChild = self::count(array(
                    'categoryParentId = :pid:',
                    'bind' => array('pid' => $this->categoryId)
        ));
        return $numberOfChild > 0;
    }

    /**
     * Wheather this category is child or not
     * @return bool
     */
    public function isChild() {
        return $this->categoryParentId > 0;
    }

    /**
     * Get parent category of child category
     * @return type
     */
    public function getParent() {
        return self::findFirst($this->categoryParentId);
    }

    /**
     * Get title for category management list
     * This function will render the formatted list
     * of category by parent child hierarchy
     * @global string $title
     * @return string
     */
    public function getListTitle() {
        global $title;
        $title = '';
        $this->getListTitleRecursive($this, $this->categoryTitle);
        return $title;
    }

    /**
     * Recursive function for format category title
     * by parent child hierarchy
     * @global string $title
     * @param object $category
     * @param string $ctitle
     */
    private function getListTitleRecursive($category, $ctitle) {
        global $title;
        if ($category->isChild()) {
            $parent = $category->getParent();
            $ptitle = $parent->categoryTitle . ' <span class="glyphicon glyphicon-chevron-right"></span> ' . $ctitle;
            $this->getListTitleRecursive($parent, $ptitle);
        } else {
            $title = $ctitle;
        }
    }

    /**
     * Getting status string for category
     * @param bool $escapeHtml
     * @return string
     */
    public function getStatus($escapeHtml = false) {
        switch ($this->categoryStatus) {
            case self::ACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Active' : '<span class="label label-success">Active</span>';
                break;
            case self::INACTIVE_STATUS_CODE:
                return $escapeHtml ? 'Inactive' : '<span class="label label-danger">Inactive</span>';
                break;
        }
    }

    /**
     * Removing vendor category
     * @return boolean
     */
    public function removeData() {
        $error = [];
        if ($this->hasChild()) {
            $error[] = 'has_category';
            $this->appendMessage(new Message('Category has child or sub category'));
        }

        if ($this->vendors->count() > 0) {
            $error[] = 'has_vendor';
            $this->appendMessage(new Message('Category has vendor(s)'));
        }

        if (count($error) == 0) {
            if ($this->delete()) {
                return TRUE;
            }
        }

        return FALSE;
    }

}
