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
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Regex as RegexValidator;

/**
 * Form for Admin Account Management
 * @author amit
 */
class AccountForm extends CForm {

    private function attachElementUserName() {
        $userName = new Text('username');
        $userName->setLabel('User Name');
        $userName->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->userName)) {
                $userName->setDefault($this->entity->userName);
                $userName->setAttribute('readonly', 'readonly');
            }
        }
        $userName->addValidators(array(
            new PresenceOf(array('message' => 'User Name is required')),
        ));
        $this->add($userName);
    }

    private function attachElementFirstName() {
        $firstName = new Text('first_name');
        $firstName->setLabel('First Name');
        $firstName->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->firstName)) {
                $firstName->setDefault($this->entity->firstName);
            }
        }
        $this->add($firstName);
    }

    private function attachElementLastName() {
        $lastName = new Text('last_name');
        $lastName->setLabel('Last Name');
        $lastName->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->lastName)) {
                $lastName->setDefault($this->entity->lastName);
            }
        }
        $this->add($lastName);
    }

    private function attachElementEmail() {
        $emailAddress = new Text('email');
        $emailAddress->setLabel('Email');
        $emailAddress->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->emailAddress)) {
                $emailAddress->setDefault($this->entity->emailAddress);
            }
        }
        $emailAddress->addValidators(array(
            new Email(array('message' => 'Email is not valid', 'allowEmpty' => true))
        ));
        $this->add($emailAddress);
    }

    private function attachElementPhone() {
        $phone = new Text('phone');
        $phone->setLabel('Phone');
        $phone->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->phone)) {
                $phone->setDefault($this->entity->phone);
            }
        }
        $phone->addValidators(array(
            new RegexValidator(array(
                'message' => 'Invalid Phone Numbers',
                'pattern' => '/^[0-9]{10,14}$/',
                'allowEmpty' => true
                    )),
        ));
        $this->add($phone);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        if (isset($this->options['edit']) && $this->options['edit']) {
            $submitButton->setAttribute('value', 'Update');
        }
        $submitButton->setAttribute('class', 'btn btn-primary');
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
        $this->attachElementUserName();
        $this->attachElementFirstName();
        $this->attachElementLastName();
        $this->attachElementEmail();
        $this->attachElementPhone();
        $this->attachElementSubmit();
    }

}
