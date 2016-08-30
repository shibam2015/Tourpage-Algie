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
 * Category Form
 * @author amit
 */
class CategoryForm extends CForm {

    private function attachElementTitle() {
        $categoryTitle = new Text('category_title');
        $categoryTitle->setLabel('Category Title');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->categoryTitle)) {
                $categoryTitle->setDefault($this->entity->categoryTitle);
            }
        }
        $categoryTitle->setAttribute('class', 'form-control');
        $categoryTitle->addValidators(array(
            new PresenceOf(array('message' => 'Category title is required'))
        ));
        $this->add($categoryTitle);
    }

    private function attachElementParent() {
        $categoryParent = new Radio('category_parent');
        $categoryParent->setAttribute('class', 'form-cat');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->categoryParentId)) {
                $categoryParent->setDefault($this->entity->categoryParentId);
            }
        }
        $this->add($categoryParent);
    }

    private function attachElementMarkParent() {
        $categoryMarkParent = new Check('category_mark_parent');
        $categoryMarkParent->setAttribute('value', 1);
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->categoryParentId)) {
                if ($this->entity->categoryParentId == 0) {
                    $categoryMarkParent->setDefault(1);
                }
            }
        }
        $this->add($categoryMarkParent);
    }

    private function attachElementStatus() {
        $categoryStatus = new Select('category_status');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->categoryStatus)) {
                $categoryStatus->setDefault($this->entity->categoryStatus);
            }
        }
        $categoryStatus->setLabel('Category Status');
        $categoryStatus->setAttribute('class', 'form-control');
        $categoryStatus->setOptions(array(
            \Tourpage\Models\CategoryTour::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\CategoryTour::INACTIVE_STATUS_CODE => 'Inactive'
        ));
        $this->add($categoryStatus);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', ((isset($this->options['edit']) && $this->options['edit']) ? 'Update' : 'Submit'));
        $submitButton->setAttribute('class', 'btn btn-danger');
        $this->add($submitButton);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementTitle();
        $this->attachElementParent();
        $this->attachElementMarkParent();
        $this->attachElementStatus();
        $this->attachElementSubmit();
    }

    public function renderCategory($options = null) {
        global $categoryString;
        $categoryString = '';
        $categoryData = \Tourpage\Models\CategoryTour::findBycategoryParentId(0);
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
                        $categoryChild = \Tourpage\Models\CategoryTour::findByCategoryParentId($category->categoryId);
                        $this->rederCategoryRecursive($categoryChild, $options, 'form-sub-category');
                    }
                    $categoryString .= '</div>';
                }
            }
        }
    }

}
