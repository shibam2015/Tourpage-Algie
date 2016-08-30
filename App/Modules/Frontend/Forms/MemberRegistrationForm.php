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

namespace Multiple\Frontend\Forms;

use Tourpage\Forms\CForm;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Digit as DigitValidator;

/**
 * Member Registration Form
 * @author amit
 */
class MemberRegistrationForm extends CForm {

    private function attachElementFirstName() {
        $firstName = new Text('first_name');
        $firstName->setAttribute('class','form-input');
		$firstName->setAttribute('placeholder','First Name*');
		//$firstName->setAttribute('size','50px');
		
        $firstName->addValidators(array(
            new PresenceOf(array('message' => 'First Name is required'))
        ));
        if (isset($this->options['first_name']) && $this->options['first_name']) {
            $firstName->setDefault($this->options['first_name']);
        }
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->firstName)) {
                $firstName->setDefault($this->entity->firstName);
            }
        }
        $this->add($firstName);
    }

    private function attachElementLastName() {
        $lastName = new Text('last_name');
        $lastName->setAttribute('class', 'form-input');
		$lastName->setAttribute('placeholder','Last Name*');
		//$lastName->setAttribute('size','50px');
        $lastName->addValidators(array(
            new PresenceOf(array('message' => 'Last Name is required'))
        ));
        if (isset($this->options['last_name']) && $this->options['last_name']) {
            $lastName->setDefault($this->options['last_name']);
        }
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->lastName)) {
                $lastName->setDefault($this->entity->lastName);
            }
        }
        $this->add($lastName);
    }

    private function attachElementEmailAddress() {
        $emailAddress = new Text('email_address');
        $emailAddress->setAttribute('class', 'form-input');
        $emailAddress->setAttribute('placeholder','Email Address*');
		//$emailAddress->setAttribute('size','50px');
		$emailAddress->setLabel('Email');
        $emailAddress->addValidators(array(
            //new PresenceOf(array('message' => 'Email Address is required')),
            new Email(array('message' => 'Email Address is not valid'))
        ));
        if (isset($this->options['email_address']) && $this->options['email_address']) {
            $emailAddress->setDefault($this->options['email_address']);
        }
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->emailAddress)) {
                $emailAddress->setDefault($this->entity->emailAddress);
            }
        }
        $this->add($emailAddress);
    }

    private function attachElementCountry() {
        $formCountry = ['' => '-- Select Country --'];
        $memberCountries = $countryRank = [];
        $members = \Tourpage\Models\Members::find();
        if ($members->count() > 0) {
            foreach ($members as $memberCountry) {
                if (!array_key_exists($memberCountry->countryId, $countryRank)) {
                    $countryRank[$memberCountry->countryId] = 1;
                } else {
                    $countryRank[$memberCountry->countryId] = $countryRank[$memberCountry->countryId] + 1;
                }
            }

            if (count($countryRank) > 0) {
                arsort($countryRank);
                foreach ($countryRank as $rankKey => $rankValue) {
                    $memberCountries[] = $rankKey;
                    $countryData = \Tourpage\Models\Country::findFirst($rankKey);
                    $formCountry[$countryData->countryId] = $countryData->name;
                }
            }
        }

        $countryData = \Tourpage\Models\Country::query();
        $countryData->columns(array('countryId', 'name'));
        $countryData->where("status = :status:", array('status' => \Tourpage\Models\Country::ACTIVE_STATUS_CODE));
        if (count($memberCountries) > 0) {
            $countryData->notInWhere('countryId', $memberCountries);
        }
        $countries = $countryData->execute();
        if ($countries->count() > 0) {
            foreach ($countries as $countryDp) {
                $formCountry[$countryDp->countryId] = $countryDp->name;
            }
        }
        $country = new Select('country');
        $country->setOptions($formCountry);
        $country->setLabel('Country');
        $country->setAttribute('class', 'form-input');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->countryId)) {
                $country->setDefault($this->entity->countryId);
            }
        }
        $country->addValidators(array(
            new PresenceOf(array('message' => 'Country is required')),
        ));
        $this->add($country);
    }

    private function attachElementPhone() {
        if (isset($this->options['edit'])) {
            $phone = new Text('phone');
            $phone->setAttribute('class', 'form-input');
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
    }

    private function attachElementAddressOne() {
        if (isset($this->options['edit'])) {
            $addressOne = new Text('address_1');
            $addressOne->setAttribute('class', 'form-input');
            $addressOne->addValidators(array(
                new PresenceOf(array('message' => 'Address 1 is required'))
            ));
            if (isset($this->options['edit']) && $this->options['edit']) {
                if ($this->entity && isset($this->entity->addressOne)) {
                    $addressOne->setDefault($this->entity->addressOne);
                }
            }
            $this->add($addressOne);
        }
    }

    private function attachElementAddressTwo() {
        if (isset($this->options['edit'])) {
            $addressTwo = new Text('address_2');
            $addressTwo->setAttribute('class', 'form-input');
            if (isset($this->options['edit']) && $this->options['edit']) {
                if ($this->entity && isset($this->entity->addressTwo)) {
                    $addressTwo->setDefault($this->entity->addressTwo);
                }
            }
            $this->add($addressTwo);
        }
    }

    private function attachElementZipCode() {
        if (isset($this->options['edit'])) {
            $zipCode = new Text('zip_code');
            $zipCode->setAttribute('class', 'form-input');
            if (isset($this->options['edit']) && $this->options['edit']) {
                if ($this->entity && isset($this->entity->zipCode)) {
                    $zipCode->setDefault($this->entity->zipCode);
                }
            }
            $zipCode->addValidators(array(
                new DigitValidator(array('message' => 'ZIP Code must be numeric', 'allowEmpty' => true))
            ));
            $this->add($zipCode);
        }
    }

    private function attachElementState() {
        if (isset($this->options['edit'])) {
            $formState = ['' => '-- Select State --'];
            $state = new Select('state');
            $state->setLabel('State');
            $state->setAttribute('class', 'form-input');
            $selectCountry = $selectState = 0;
            if (isset($this->options['edit']) && $this->options['edit']) {
                $selectCountry = $this->entity->countryId;
                $selectState = $this->entity->stateId;
            }
            if ($this->request->isPost()) {
                $selectCountry = $this->request->getPost('country');
                $selectState = $this->request->getPost('state');
            }
            if ($this->request->isPost() || (isset($this->options['edit']) && $this->options['edit'])) {
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
		}
    }

    private function attachElementCity() {
        if (isset($this->options['edit'])) {
            $city = new Text('city');
            $city->setAttribute('class', 'form-input');
            $city->addValidators(array(
                new PresenceOf(array('message' => 'City is required')),
            ));
            $this->add($city);
        }
    }

    private function attachElementNickName() {
        $nickName = new Text('nick_name');
        $nickName->setAttribute('class', 'form-input');
        $nickName->setLabel('Nickname');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->nickName)) {
                $nickName->setDefault($this->entity->nickName);
            }
        }
        $this->add($nickName);
    }

    private function attachElementPassword() {
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $password = new Password('password');
            $password->setLabel('Password');
            $password->setAttribute('class', 'form-input');
			//$password->setAttribute('size','50px');
			$password->setAttribute('placeholder', 'Password*');
			
			
            $password->addValidators(array(
                new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.')),
            ));
            $this->add($password);
        }
    }

    private function attachElementRePassword() {
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $rePassword = new Password('re_password');
            $rePassword->setLabel('Retype Password');
            $rePassword->setAttribute('class', 'form-input');
			$rePassword->setAttribute('placeholder', 'Confirm Password*');
			//$rePassword->setAttribute('size', '50');
			
            
			$rePassword->addValidators(array(
                new Confirmation(array('with' => 'password', 'message' => 'Retype your password correctly'))
            ));
            $this->add($rePassword);
        }
    }

    private function attachElementCaptcha() {
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $captcha = new Text('captcha');
            $captcha->setAttribute('class', 'form-input');
            $captcha->addValidators(array(
                new \Tourpage\Library\Validator\CaptchaValidator(array('message' => 'Invalid captcha text')),
            ));
            //$captcha->setAttribute('class', 'form-control');
            $this->add($captcha);
        }
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementFirstName();
        $this->attachElementLastName();
        $this->attachElementEmailAddress();
       // $this->attachElementCountry();
        //$this->attachElementPhone();
       // $this->attachElementAddressOne();
        //$this->attachElementAddressTwo();
        //$this->attachElementZipCode();
        //$this->attachElementState();
        //$this->attachElementCity();
        //$this->attachElementNickName();
        $this->attachElementPassword();
        $this->attachElementRePassword();
        //$this->attachElementCaptcha();
    }

}
