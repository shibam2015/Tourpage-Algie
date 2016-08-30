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
 * TourTypesForm Class
 * @author amit
 */
class TourTypesForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {
        $tourTypeTitle = new Text('tour_type_title');
        $tourTypeTitle->setLabel('Tour & Activity Type');
        $tourTypeTitle->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->tourTypesTitle)) {
                $tourTypeTitle->setDefault($entity->tourTypesTitle);
            }
        }
        $tourTypeTitle->addValidators(array(
            new PresenceOf(array('message' => 'Tour & Activity Type is require'))
        ));
        $this->add($tourTypeTitle);

        $tourTypeStatus = new Select('tour_type_status');
        $tourTypeStatus->setLabel('Tour & Activity Type Status');
        $tourTypeStatus->setAttribute('class', 'form-control');
        $tourTypeStatus->setOptions(array(
            \Tourpage\Models\TourTypes::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\TourTypes::INACTIVE_STATUS_CODE => 'Inactive',
        ));
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->tourTypesStatus)) {
                $tourTypeStatus->setDefault($entity->tourTypesStatus);
            }
        }
        $tourTypeStatus->addValidators(array(
            new PresenceOf(array('message' => 'Status is require')),
        ));
        $this->add($tourTypeStatus);

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
