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

use Phalcon\Forms\Element\File;
use Phalcon\Validation\Validator\File as FileValidator;

/**
 * CFilesForm Class
 * Form class for common file upload form
 * @author amit
 */
class CFilesForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {
        $validatorOptions = [];
        $fieldName = 'img';
        if (isset($options['name']) && !empty($options['name'])) {
            $fieldName = $options['name'];
        }
        $fieldLabel = 'Image';
        if (isset($options['label']) && !empty($options['label'])) {
            $fieldLabel = $options['label'];
        }
        if (isset($options['allowEmpty']) && !empty($options['allowEmpty']) && $options['allowEmpty']) {
            $validatorOptions['allowEmpty'] = $options['allowEmpty'];
        }
        $validatorOptions['maxSize'] = '8M';
        if (isset($options['maxSize'])) {
            $validatorOptions['maxSize'] = $options['maxSize'];
            if ($options['maxSize'] == 0) {
                unset($validatorOptions['maxSize']);
            }
        }
        $validatorOptions['allowedTypes'] = array('image/jpeg', 'image/png');
        if (isset($options['allowedTypes']) && !empty($options['allowedTypes'])) {
            $validatorOptions['allowedTypes'] = $options['allowedTypes'];
        }
        $validatorOptions['maxResolution'] = '800x360';
        if (isset($options['maxResolution'])) {
            $validatorOptions['maxResolution'] = $options['maxResolution'];
            if ($options['maxResolution'] == 0) {
                unset($validatorOptions['maxResolution']);
            }
        }
        $validatorOptions['messageEmpty'] = ':field cannot be empty';
        if (isset($options['messageEmpty']) && !empty($options['messageEmpty'])) {
            $validatorOptions['messageEmpty'] = $options['messageEmpty'];
        }
        if (isset($options['allowEmpty']) && !empty($options['allowEmpty']) && $options['allowEmpty']) {
            unset($validatorOptions['messageEmpty']);
        }
        $validatorOptions['messageSize'] = ':field exceeds the max filesize (:max)';
        if (isset($options['messageSize']) && !empty($options['messageSize'])) {
            $validatorOptions['messageSize'] = $options['messageSize'];
        }
        if (isset($options['maxSize']) && $options['maxSize'] == 0) {
            unset($validatorOptions['messageSize']);
        }
        $validatorOptions['messageType'] = 'Invalid file types. Allowed file types are :types';
        if (isset($options['messageType']) && !empty($options['messageType'])) {
            $validatorOptions['messageType'] = $options['messageType'];
        }
        $validatorOptions['messageMaxResolution'] = 'Max resolution(w x h) of :field is :max';
        if (isset($options['messageMaxResolution']) && !empty($options['messageMaxResolution'])) {
            $validatorOptions['messageMaxResolution'] = $options['messageMaxResolution'];
        }
        if (isset($options['maxResolution']) && $options['maxResolution'] == 0) {
            unset($validatorOptions['messageMaxResolution']);
        }
        $imageField = new File($fieldName);
        $imageField->setLabel($fieldLabel);
        if (isset($options['attribute']) && count($options['attribute']) > 0) {
            foreach ($options['attribute'] as $attributeName => $attributeValue) {
                if (!empty($attributeName) && !empty($attributeValue)) {
                    $imageField->setAttribute($attributeName, $attributeValue);
                }
            }
        }
        $imageField->addValidators(array(
            new FileValidator($validatorOptions),
        ));
        $this->add($imageField);
    }

}
