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

namespace Multiple\Vendor\Forms;

use Tourpage\Forms\CForm;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\StringLength as StringLength;

/**
 * Employee Form for Vendor
 * @author amit
 */
class EmployeeForm extends CForm {
	
	/*private function attachElementJobTitle() {
        $jobTitle = new Text('job_title');
        $jobTitle->setLabel('Job Title');
        $jobTitle->setAttribute('class', 'form-control');
        $jobTitle->addValidators(array(
            new PresenceOf(array('message' => 'Job Title is required'))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->jobTitle)) {
                $jobTitle->setDefault($this->entity->jobTitle);
            }
        }
        $this->add($jobTitle);
    }*/

    private function attachElementFirstName() {
        $firstName = new Text('first_name');
        $firstName->setLabel('First Name');
        $firstName->setAttribute('class', 'form-control');
        $firstName->addValidators(array(
            new PresenceOf(array('message' => 'First Name is required'))
        ));
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
        $lastName->addValidators(array(
            new PresenceOf(array('message' => 'Last Name is required'))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->lastName)) {
                $lastName->setDefault($this->entity->lastName);
            }
        }
        $this->add($lastName);
    }

    private function attachElementCountry() {
        $countryCtiretia = array(
            'condition' => 'status = :status:',
            'bind' => array('status' => \Tourpage\Models\Country::ACTIVE_STATUS_CODE)
        );
        $country = new Select('country', \Tourpage\Models\Country::find($countryCtiretia), array(
            'using' => array('countryId', "name"),
            'useEmpty' => true,
            'emptyText' => 'Please, choose one...',
            'emptyValue' => ''
        ));
        $country->setLabel('Country');
        $country->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->countryId)) {
                $country->setDefault($this->entity->countryId);
            }
        }
        $country->addValidators(array(
            new PresenceOf(array('message' => 'Country is required'))
        ));
        $this->add($country);
    }

    private function attachElementState() {
        $formState = ['' => 'Please, choose one...'];
        $state = new Select('state');
        $state->setLabel('State');
        $state->setAttribute('class', 'form-control');
        $state->addValidators(array(
            new PresenceOf(array('message' => 'State is required')),
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            $selectCountry = $this->entity->countryId;
            $selectState = $this->entity->stateId;
        }
        if ($this->request->isPost()) {
            $selectCountry = $this->request->getPost('country');
            $selectState = $this->request->getPost('state');
        }
        if ($this->request->isPost() || isset($this->options['edit']) && $this->options['edit']) {
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
            if ($selectState > 0) {
                $state->setDefault($selectState);
            }
        }
        $state->setOptions($formState);
        $this->add($state);
    }

    private function attachElementCity() {
        $city = new Text('city');
        $city->setLabel('City');
        $city->setAttribute('class', 'form-control');
        $city->addValidators(array(
            new PresenceOf(array('message' => 'City is required')),
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->city)) {
                $city->setDefault($this->entity->city);
            }
        }
        $this->add($city);
    }

    private function attachElementEmailAddress() {
        $emailAddress = new Text('email_address');
        $emailAddress->setAttribute('class', 'form-control');
        $emailAddress->setLabel('Email');
        $emailAddress->addValidators(array(
            new PresenceOf(array('message' => 'Email Address is required')),
            new Email(array('message' => 'Email Address is not valid', 'allowEmpty' => TRUE))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->emailAddress)) {
                $emailAddress->setDefault($this->entity->emailAddress);
                $emailAddress->setAttribute('readonly', 'readonly');
            }
        }
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
            new DigitValidator(array('message' => 'Phone must be numeric', 'allowEmpty' => true))
        ));
        $this->add($phone);
    }

    private function attachElementPassword() {
        $passwordValidators = [];
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $passwordValidators[] = new PresenceOf(array('message' => 'Password is required'));
        }
        $passwordValidators[] = new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.', 'allowEmpty' => TRUE));
        $password = new Password('password');
        $password->setLabel('Password');
        $password->setAttribute('class', 'form-control');
        $password->setUserOptions(array('hints' => 'Password length should be within 6 to 20 character'));
        $password->addValidators($passwordValidators);
        $this->add($password);
    }

    private function attachElementRetypePassword() {
        $rePasswordValidators = [];
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $rePasswordValidators[] = new PresenceOf(array('message' => 'Retype your password is required'));
        }
        $rePasswordValidators[] = new Confirmation(array('with' => 'password', 'message' => 'Retype your password correctly', 'allowEmpty' => TRUE));
        $rePassword = new Password('re_password');
        $rePassword->setLabel('Retype Password');
        $rePassword->setAttribute('class', 'form-control');
        $rePassword->addValidators($rePasswordValidators);
        $this->add($rePassword);
    }

    private function attachElementStatus() {
        $status = new Select('emp_status');
        $status->setLabel('Status');
        $status->setAttribute('class', 'form-control');
        $status->setDefault(\Tourpage\Models\Vendors::ACTIVE_STATUS_CODE);
        $status->setOptions(array(
            \Tourpage\Models\Vendors::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\Vendors::INACTIVE_STATUS_CODE => 'Inactive',
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->status)) {
                $status->setDefault($this->entity->status);
            }
        }
        $this->add($status);
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
		//$this->attachElementJobTitle();
        $this->attachElementFirstName();
        $this->attachElementLastName();
        $this->attachElementCountry();
        $this->attachElementState();
        $this->attachElementCity();
        $this->attachElementEmailAddress();
        $this->attachElementPhone();
        $this->attachElementPassword();
        $this->attachElementRetypePassword();
        $this->attachElementStatus();
        $this->attachElementSubmit();
    }

}
