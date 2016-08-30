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
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * TourAttributeKeyForm Class
 * @author amit
 */
class TourAttributeKeyForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {
        $tourAttributeName = new Text('key_name');
        $tourAttributeName->setLabel('Description Fields Name');
        $tourAttributeName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->keyName)) {
                $tourAttributeName->setDefault($entity->keyName);
            }
        }
        $tourAttributeName->addValidators(array(
            new PresenceOf(array('message' => 'Description Fields Name is require'))
        ));
        $this->add($tourAttributeName);

        $tourAttributeStatus = new Select('key_status');
        $tourAttributeStatus->setLabel('Description Fields Status');
        $tourAttributeStatus->setAttribute('class', 'form-control');
        $tourAttributeStatus->setOptions(array(
            \Tourpage\Models\ToursAttributeKeys::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\ToursAttributeKeys::INACTIVE_STATUS_CODE => 'Inactive',
        ));
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->keyStatus)) {
                $tourAttributeStatus->setDefault($entity->keyStatus);
            }
        }
        $tourAttributeStatus->addValidators(array(
            new PresenceOf(array('message' => 'Description Fields Status is require')),
        ));
        $this->add($tourAttributeStatus);

        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        if (isset($options['edit']) && $options['edit']) {
            $submitButton->setAttribute('value', 'Update');
        }
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }

}
