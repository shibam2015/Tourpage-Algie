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
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Numericality;

/**
 * MemberForm class
 * @author amit
 */
class MemberForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {
        $firstName = new Text('first_name');
        $firstName->setLabel('First Name');
        $firstName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->firstName)) {
                $firstName->setDefault($entity->firstName);
            }
        }
        $firstName->addValidators(array(
            new PresenceOf(array('message' => 'First Name is required'))
        ));
        $this->add($firstName);

        $lastName = new Text('last_name');
        $lastName->setLabel('Last Name');
        $lastName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->lastName)) {
                $lastName->setDefault($entity->lastName);
            }
        }
        $lastName->addValidators(array(
            new PresenceOf(array('message' => 'Last Name is required'))
        ));
        $this->add($lastName);

        $nickName = new Text('nick_name');
        $nickName->setAttribute('class', 'form-control');
        $nickName->setLabel('Nickname');
        $nickName->addValidators(array(
            new PresenceOf(array('message' => 'Nickname is required')),
        ));
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->nickName)) {
                $nickName->setDefault($entity->nickName);
                //$nickName->setAttribute('readonly', 'readonly');
            }
        }
        $this->add($nickName);

        $formCountry = ['' => '-- Select Country --'];
        $countryData = \Tourpage\Models\Country::query();
        $countryData->columns(array('countryId', 'name'));
        $countryData->where("status = :status:", array('status' => \Tourpage\Models\Country::ACTIVE_STATUS_CODE));
        $countries = $countryData->execute();
        if ($countries->count() > 0) {
            foreach ($countries as $countryDp) {
                $formCountry[$countryDp->countryId] = $countryDp->name;
            }
        }
        $country = new Select('country');
        $country->setOptions($formCountry);
        $country->setLabel('Country');
        $country->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->countryId)) {
                $country->setDefault($entity->countryId);
            }
        }
        $country->addValidators(array(
            new PresenceOf(array('message' => 'Country is required')),
        ));
        $this->add($country);

        $formState = ['' => '-- Select State --'];
        $state = new Select('state');
        $state->setLabel('State');
        $state->setAttribute('class', 'form-control');
        $selectCountry = $selectState = 0;
        if (isset($options['edit']) && $options['edit']) {
            $selectCountry = $entity->countryId;
            $selectState = $entity->stateId;
        }
        if ($this->request->isPost()) {
            $selectCountry = $this->request->getPost('country');
            $selectState = $this->request->getPost('state');
        }
        if ($this->request->isPost() || (isset($options['edit']) && $options['edit'])) {
            $states = \Tourpage\Models\State::find(array(
                        "countryId = :country_id: AND status = :state_status:",
                        "bind" => array(
                            ":country_id" => $selectCountry,
                            ":state_status" => \Tourpage\Models\State::ACTIVE_STATUS_CODE
                        )
            ));
            if ($states && $states->count() > 0) {
                foreach ($states as $s) {
                    $formState[$s->stateId] = $s->name;
                }
            }
        }
        $state->setOptions($formState);
        if ($selectState > 0) {
            $state->setDefault($selectState);
        }
        $state->addValidators(array(
            new PresenceOf(array('message' => 'State is required')),
        ));
        $this->add($state);

        $city = new Text('city');
        $city->setLabel('City');
        $city->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->city)) {
                $city->setDefault($entity->city);
            }
        }
        /* $city->addValidators(array(
          new PresenceOf(array('message' => 'City is required')),
          )); */
        $this->add($city);

        $status = new Select('status');
        $status->setLabel('Status');
        $status->setAttribute('class', 'form-control');
        $status->setOptions(array(
            \Tourpage\Models\Members::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\Members::INACTIVE_STATUS_CODE => 'Inactive',
        ));
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->status)) {
                $status->setDefault($entity->status);
            }
        }
        $status->addValidators(array(
            new PresenceOf(array('message' => 'Status is require')),
        ));
        $this->add($status);

        $emailAddress = new Text('email');
        $emailAddress->setLabel('Email');
        $emailAddress->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->emailAddress)) {
                $emailAddress->setDefault($entity->emailAddress);
                $emailAddress->setAttribute('readonly', 'readonly');
            }
        }
        $emailAddress->addValidators(array(
            new Email(array('message' => 'Email is not valid'))
        ));
        $this->add($emailAddress);

        $phone = new Text('phone');
        $phone->setLabel('Phone');
        $phone->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->phone)) {
                $phone->setDefault($entity->phone);
                $phone->setAttribute('readonly', 'readonly');
            }
        }
        $phone->addValidators(array(
            new Numericality(array('message' => 'Phone must be numeric', 'allowEmpty' => true))
        ));
        $this->add($phone);

        $password = new Password('password');
        $password->setLabel('Password');
        $password->setAttribute('class', 'form-control');
        if (isset($options['form_type']) && $options['form_type'] == 'new') {
            $password->addValidators(array(
                new PresenceOf(array('message' => 'Password is required')),
                new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.'))
            ));
        }
        $this->add($password);

        $rePassword = new Password('re_password');
        $rePassword->setLabel('Retype Password');
        $rePassword->setAttribute('class', 'form-control');
        if (isset($options['form_type']) && $options['form_type'] == 'new') {
            $rePassword->addValidators(array(
                new Confirmation(array('with' => 'password', 'message' => 'Retype your password correctly'))
            ));
        }
        $this->add($rePassword);

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
