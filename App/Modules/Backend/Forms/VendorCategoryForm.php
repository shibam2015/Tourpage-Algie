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

namespace Multiple\Backend\Forms;

use Tourpage\Forms\CForm;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Radio;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Category Form For Vendor
 * @author amit
 */
class VendorCategoryForm extends CForm {

    public function initialize($entity = null, $options = null) {
        $categoryTitle = new Text('category_title');
        $categoryTitle->setLabel('Category Title');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->categoryTitle)) {
                $categoryTitle->setDefault($entity->categoryTitle);
            }
        }
        $categoryTitle->setAttribute('class', 'form-control');
        $categoryTitle->addValidators(array(
            new PresenceOf(array('message' => 'Category title is required'))
        ));
        $this->add($categoryTitle);

        $categoryParent = new Radio('category_parent');
        $categoryParent->setAttribute('class', 'form-cat');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->categoryParentId)) {
                $categoryParent->setDefault($entity->categoryParentId);
            }
        }
        $this->add($categoryParent);

        $categoryMarkParent = new Check('category_mark_parent');
        $categoryMarkParent->setAttribute('value', 1);
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->categoryParentId)) {
                if ($entity->categoryParentId == 0) {
                    $categoryMarkParent->setDefault(1);
                }
            }
        }
        $this->add($categoryMarkParent);

        $categoryStatus = new Select('category_status');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->categoryStatus)) {
                $categoryStatus->setDefault($entity->categoryStatus);
            }
        }
        $categoryStatus->setLabel('Category Status');
        $categoryStatus->setAttribute('class', 'form-control');
        $categoryStatus->setOptions(array(
            \Tourpage\Models\CategoryTour::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\CategoryTour::INACTIVE_STATUS_CODE => 'Inactive'
        ));
        $this->add($categoryStatus);

        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', ((isset($options['edit']) && $options['edit']) ? 'Update' : 'Submit'));
        $submitButton->setAttribute('class', 'btn btn-danger');
        $this->add($submitButton);
    }

    public function renderCategory($options = null) {
        global $categoryString;
        $categoryString = '';
        $categoryData = \Tourpage\Models\CategoryVendor::findBycategoryParentId(0);
        $this->rederCategoryRecursive($categoryData, $options);
        echo $categoryString;
    }

    private function rederCategoryRecursive($categoryData, $options = null, $class = 'form-category') {
        global $categoryString;
        if ($categoryData->count() > 0) {
            foreach ($categoryData as $category) {
                if ($category->categoryId != $options['esc']) {
                    $categoryString .= '<div class="' . $class . '"><label>';
                    $categoryString .= $this->render('category_parent', array('value' => $category->categoryId)) . ' ' . $category->categoryTitle;
                    $categoryString .= '</label>';
                    if ($category->hasChild()) {
                        $categoryChild = \Tourpage\Models\CategoryVendor::findByCategoryParentId($category->categoryId);
                        $this->rederCategoryRecursive($categoryChild, $options, 'form-sub-category');
                    }
                    $categoryString .= '</div>';
                }
            }
        }
    }

}
