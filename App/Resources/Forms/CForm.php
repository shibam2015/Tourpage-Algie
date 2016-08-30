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

namespace Tourpage\Forms;

use Phalcon\Forms\Form;

/**
 * Base form class for common forms
 * @author amit
 */
abstract class CForm extends Form {

    protected $entity;
    protected $options;

    protected function setFormEntity($entity = null) {
        $this->entity = $entity;
    }

    protected function setFormOptions($options = null) {
        $this->options = $options;
    }

    public function renderBootstrap($name, $attributes = array()) {
        $messages = $this->getMessagesFor($name);
        echo '<div class="form-group' . (count($messages) > 0 ? '  has-error' : '') . '">';
        $this->renderLabel($name, $attributes, 'col-sm-4 control-label');
        echo '<div class="col-sm-8">';
        $this->renderElement($name, $attributes);
        echo '</div>';
        echo '</div>';
    }

    public function renderHorizental($name, $attributes = array()) {
        $messages = $this->getMessagesFor($name);
        echo '<div class="form-group' . (count($messages) > 0 ? '  has-error' : '') . '">';
        $this->renderLabel($name, $attributes);
        $this->renderElement($name, $attributes);
        echo '</div>';
    }

    public function renderLabel($name, $attributes = array(), $class = 'form-label') {
        $element = $this->get($name);
        $asterisk = '';
        if ($element->getLabel()) {
            if (isset($attributes['required']) && $attributes['required']) {
                $asterisk .= ' <i class="fa fa-asterisk fa-1 text-danger"></i>';
            }
            echo '<label for="' . $element->getName() . '" class="' . $class . '">' . $element->getLabel() . $asterisk . '</label>';
        }
    }

    public function renderElement($name, $attributes = array()) {
        $element = $this->get($name);
        $messages = $this->getMessagesFor($name);
        if (count($messages) > 0) {
            $cssClass = $element->getAttribute('class');
            if (!isset($attributes['class'])) {
                $attributes['class'] = '';
            }
            $attributes['class'] .= ' ' . $cssClass . ' error-field';
        }
        echo $this->render($name, $attributes);
        $this->getElementOptions($name);
        $this->getElementMessages($name);
    }

    public function getElementMessages($name) {
        $messages = $this->getMessagesFor($name);
        if (count($messages) > 0) {
            echo '<span class="help-block error-message">';
            foreach ($messages as $message) {
                echo $message . ". ";
            }
            echo '</span>';
        }
    }

    public function getElementOptions($name) {
        $element = $this->get($name);
        $userOptions = $element->getUserOptions();
        if (count($userOptions) > 0) {
            if (isset($userOptions['hints'])) {
                echo '<span class="hints-block help">' . $userOptions['hints'] . '</span>';
            }
        }
    }

}
