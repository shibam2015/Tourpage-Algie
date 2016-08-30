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
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Url;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\StringLength as StringLength;

/*
 * Registration Form
 */

class RegistrationForm extends CForm {

    private function hasAccess() {
        $access = TRUE;
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity) {
                if (!$this->entity->isParent()) {
                    $access = FALSE;
                }
            }
        }
        return $access;
    }

    private function attachElementBusinessName() {
        $vendorBusinessName = new Text('vendor_business_name');
        $vendorBusinessName->setLabel('Business Name');
        $vendorBusinessName->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->businessName)) {
                $vendorBusinessName->setDefault($this->entity->businessName);
            }
        }
//        $vendorBusinessName->addValidators(array(
//            new PresenceOf(array('message' => 'Business Name is required'))
//        ));
        $this->add($vendorBusinessName);
    }

    private function attachElementFirstName() {
        $vendorFirstName = new Text('vendor_first_name');
        $vendorFirstName->setLabel('First Name');
        $vendorFirstName->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->firstName)) {
                $vendorFirstName->setDefault($this->entity->firstName);
            }
        }
        $vendorFirstName->addValidators(array(
            new PresenceOf(array('message' => 'First Name is required'))
        ));
        $this->add($vendorFirstName);
    }

    private function attachElementLastName() {
        $vendorLastName = new Text('vendor_last_name');
        $vendorLastName->setLabel('Last Name');
        $vendorLastName->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->lastName)) {
                $vendorLastName->setDefault($this->entity->lastName);
            }
        }
        $vendorLastName->addValidators(array(
            new PresenceOf(array('message' => 'Last Name is required'))
        ));
        $this->add($vendorLastName);
    }

    private function attachElementEmail() {
        $vendorEmail = new Text('vendor_email');
        $vendorEmail->setLabel('Email');
        $vendorEmail->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->emailAddress)) {
                $vendorEmail->setDefault($this->entity->emailAddress);
                $vendorEmail->setAttribute('readonly', 'readonly');
            }
        }
        $vendorEmail->addValidators(array(
            new PresenceOf(array('message' => 'Email is required')),
            new Email(array('message' => 'Email is not valid'))
        ));
        $this->add($vendorEmail);
    }
    
    private function attachElementSupportEmail() {
        $vendorSupportEmail = new Text('support_email');
        $vendorSupportEmail->setLabel('Support Email');
        $vendorSupportEmail->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->supportEmail)) {
                $vendorSupportEmail->setDefault($this->entity->supportEmail);
                //$vendorSupportEmail->setAttribute('readonly', 'readonly');
            }
        }
////        $vendorSupportEmail->addValidators(array(
////            new PresenceOf(array('message' => 'Support Email is required')),
////            new Email(array('message' => 'Email is not valid'))
////        ));
         $this->add($vendorSupportEmail);
   }

    private function attachElementAvatar() {
        if (isset($this->options['edit']) && $this->options['edit']) {
            $vendorAvatar = new File('vendor_avatar');
            $vendorAvatar->setLabel('Avatar');
            $vendorAvatar->setUserOptions(array('hints' => 'Supported file type are .JPG, .JPEG, .PNG, .GIF. Max allowed size 60KB and max allowed resolution 230px x 230px.'));
            $this->add($vendorAvatar);
        }
    }

    private function attachElementCategory() {
        $vendorCategory = new Select('vendor_category', \Tourpage\Models\CategoryVendor::find(array('categoryStatus = :status:', 'bind' => array('status' => \Tourpage\Models\CategoryVendor::ACTIVE_STATUS_CODE))), array(
            'using' => array('categoryId', 'categoryTitle'),
            'useEmpty' => true,
            'emptyText' => '-- Select Category --',
        ));
        $vendorCategory->setLabel('Category');
        $vendorCategory->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->vendorCategory)) {
                $vendorCategory->setDefault($this->entity->vendorCategory);
            }
        }
//        $vendorCategory->addValidators(array(
//            new PresenceOf(array('message' => 'Category is required')),
//        ));
        $this->add($vendorCategory);
    }

    private function attachElementTourActivityType() {
        $vendorTourActivityType = new Select('vendor_tour_activity_type[]', \Tourpage\Models\TourTypes::find(array('tourTypesStatus = :status:', 'bind' => array('status' => \Tourpage\Models\TourTypes::ACTIVE_STATUS_CODE))), array(
            'using' => array('tourTypesId', 'tourTypesTitle'),
        ));
        $vendorTourActivityType->setLabel('Tour & activities Type');
        $vendorTourActivityType->setAttribute('class', 'form-control');
        $vendorTourActivityType->setAttribute('multiple', TRUE);
        if (isset($this->options['edit']) && $this->options['edit']) {
            $defaultValues = [];
            if ($this->entity->vendorTourTypes->count() > 0) {
                foreach ($this->entity->vendorTourTypes as $vtat) {
                    $defaultValues[] = $vtat->tourTypesId;
                }
            }
            if (count($defaultValues) > 0) {
                $vendorTourActivityType->setDefault($defaultValues);
            }
        }
        $this->add($vendorTourActivityType);
    }

    private function attachElementAddressOne() {
        $vendorAddressOne = new Text('vendor_address_1');
        $vendorAddressOne->setLabel('Address 1');
        $vendorAddressOne->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->addressOne)) {
                $vendorAddressOne->setDefault($this->entity->addressOne);
            }
        }
//        $vendorAddressOne->addValidators(array(
//            new PresenceOf(array('message' => 'Address 1 is required')),
//        ));
        $this->add($vendorAddressOne);
    }

    private function attachElementAddressTwo() {
        $vendorAddressTwo = new Text('vendor_address_2');
        $vendorAddressTwo->setLabel('Address 2');
        $vendorAddressTwo->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->addressTwo)) {
                $vendorAddressTwo->setDefault($this->entity->addressTwo);
            }
        }
        $this->add($vendorAddressTwo);
    }

    private function attachElementCountry() {
        $formCountry = ['' => '-- Select your country --'];
        $vendorCountries = $countryRank = [];
        $vendors = \Tourpage\Models\Vendors::find();
        if ($vendors->count() > 0) {
            foreach ($vendors as $vendorCountry) {
                if (!array_key_exists($vendorCountry->countryId, $countryRank)) {
                    $countryRank[$vendorCountry->countryId] = 1;
                } else {
                    $countryRank[$vendorCountry->countryId] = $countryRank[$vendorCountry->countryId] + 1;
                }
            }

            if (count($countryRank) > 0) {
                arsort($countryRank);
                foreach ($countryRank as $rankKey => $rankValue) {
                    $vendorCountries[] = $rankKey;
                    $countryData = \Tourpage\Models\Country::findFirst($rankKey);
                    $formCountry[$countryData->countryId] = $countryData->name;
                }
            }
        }

        $countryData = \Tourpage\Models\Country::query();
        $countryData->columns(array('countryId', 'name'));
        $countryData->where("status = :status:", array('status' => \Tourpage\Models\Country::ACTIVE_STATUS_CODE));
        if (count($vendorCountries) > 0) {
            $countryData->notInWhere('countryId', $vendorCountries);
        }
        $countries = $countryData->execute();
        if ($countries->count() > 0) {
            foreach ($countries as $countryDp) {
                $formCountry[$countryDp->countryId] = $countryDp->name;
            }
        }
        $vendorCountry = new Select('vendor_country');
        $vendorCountry->setOptions($formCountry);
        $vendorCountry->setLabel('Country');
        $vendorCountry->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->countryId)) {
                $vendorCountry->setDefault($this->entity->countryId);
            }
        }
//        $vendorCountry->addValidators(array(
//            new PresenceOf(array('message' => 'Country is required')),
//        ));
        $this->add($vendorCountry);
    }

    private function attachElementState() {
        $formState = ['' => '-- Select Your State --'];
        $vendorState = new Select('vendor_state');
        $vendorState->setLabel('Province / State / County');
        $vendorState->setAttribute('class', 'form-control');
        $vendorSelectCountry = $vendorSelectState = 0;
        if (isset($this->options['edit']) && $this->options['edit']) {
            $vendorSelectCountry = $this->entity->countryId;
            $vendorSelectState = $this->entity->stateId;
        }
        if ($this->request->isPost()) {
            $vendorSelectCountry = $this->request->getPost('vendor_country');
            $vendorSelectState = $this->request->getPost('vendor_state');
        }
        if ($this->request->isPost() || (isset($this->options['edit']) && $this->options['edit'])) {
            $states = \Tourpage\Models\State::find(array(
                        "countryId = :country_id: AND status = :state_status:",
                        "bind" => array(
                            ":country_id" => $vendorSelectCountry,
                            ":state_status" => \Tourpage\Models\State::ACTIVE_STATUS_CODE
                        )
            ));
            if ($states && $states->count() > 0) {
                foreach ($states as $state) {
                    $formState[$state->stateId] = $state->name;
                }
            }
        }
        $vendorState->setOptions($formState);
        if ($vendorSelectState > 0) {
            $vendorState->setDefault($vendorSelectState);
        }
//        $vendorState->addValidators(array(
//            new PresenceOf(array('message' => 'State is required')),
//        ));
        $this->add($vendorState);
    }

    private function attachElementCity() {
        $vendorCity = new Text('vendor_city');
        $vendorCity->setLabel('City');
        $vendorCity->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->city)) {
                $vendorCity->setDefault($this->entity->city);
            }
        }
//        $vendorCity->addValidators(array(
//            new PresenceOf(array('message' => 'City is required')),
//        ));
        $this->add($vendorCity);
    }

    private function attachElementZip() {
        $vendorZip = new Text('vendor_zip');
        $vendorZip->setLabel('ZIP Code');
        $vendorZip->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->zipCode)) {
                $vendorZip->setDefault($this->entity->zipCode);
            }
        }
//        $vendorZip->addValidators(array(
//            new DigitValidator(array('message' => 'ZIP must be numeric', 'allowEmpty' => TRUE))
//        ));
        $this->add($vendorZip);
    }

    private function attachElementJobTitle() {
        $vendorJobTitle = new Text('vendor_job_title');
        $vendorJobTitle->setLabel('Job Title');
        $vendorJobTitle->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->jobTitle)) {
                $vendorJobTitle->setDefault($this->entity->jobTitle);
            }
        }
//        $vendorJobTitle->addValidators(array(
//            new PresenceOf(array('message' => 'Job Title is required')),
//        ));
        $this->add($vendorJobTitle);
    }

    private function attachElementPhone() {
        $vendorPhone = new Text('vendor_phone');
        $vendorPhone->setLabel('Phone');
        $vendorPhone->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->phone)) {
                $vendorPhone->setDefault($this->entity->phone);
            }
        }
//        $vendorPhone->addValidators(array(
//            new DigitValidator(array('message' => 'Phone must be numeric', 'allowEmpty' => TRUE))
//        ));
        $this->add($vendorPhone);
    }

    private function attachElementPassword() {
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $vendorPassword = new Password('vendor_password');
            $vendorPassword->setLabel('Password');
            $vendorPassword->setAttribute('class', 'form-control');
            $vendorPassword->addValidators(array(
                new PresenceOf(array('message' => 'Password is required')),
                new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.')),
            ));
            $this->add($vendorPassword);
        }
    }

    private function attachElementRePassword() {
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $vendorRePassword = new Password('vendor_re_password');
            $vendorRePassword->setLabel('Retype Password');
            $vendorRePassword->setAttribute('class', 'form-control');
            $vendorRePassword->addValidators(array(
                new Confirmation(array('with' => 'vendor_password', 'message' => 'Retype your password correctly'))
            ));
            $this->add($vendorRePassword);
        }
    }

    private function attachElementCaptcha() {
        if (!isset($this->options['edit']) || !$this->options['edit']) {
            $captcha = new Text('captcha');
            $captcha->addValidators(array(
                new \Tourpage\Library\Validator\CaptchaValidator(array('message' => 'Invalid captcha text')),
            ));
            $captcha->setAttribute('class', 'form-control');
            $this->add($captcha);
        }
    }

    private function attachElementTripAdvisorLink() {
        $vendorTripAdvisorLink = new Text('vendor_trip_advisor_link');
        $vendorTripAdvisorLink->setLabel('Trip Advisor Link');
        $vendorTripAdvisorLink->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tripAdvLink)) {
                $vendorTripAdvisorLink->setDefault($this->entity->tripAdvLink);
            } else {
                $vendorTripAdvisorLink->setAttribute('disabled', 'disabled');
            }
        } else {
            if ($this->request->isPost()) {
                if ($this->request->getPost('vendor_is_trip_advisor') != 'y') {
                    $vendorTripAdvisorLink->setAttribute('disabled', 'disabled');
                }
            } else {
                $vendorTripAdvisorLink->setAttribute('disabled', 'disabled');
            }
        }
        if ($this->request->isPost()) {
            if ($this->request->getPost('vendor_is_trip_advisor') == 'y') {
                $vendorTripAdvisorLink->addValidators(array(
                    new PresenceOf(array('message' => 'Tour Advisor Link is required')),
                ));
                $vendorTripAdvisorLink->addValidators(array(
                    new Url(array('message' => 'Invalid Tour Advisor Link', 'allowEmpty' => TRUE)),
                ));
            }
        }

        $this->add($vendorTripAdvisorLink);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Register Now');
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
        $access = $this->hasAccess();
        if ($access) {
            $this->attachElementBusinessName();
        }
        $this->attachElementFirstName();
        $this->attachElementLastName();
        $this->attachElementEmail();
        $this->attachElementSupportEmail();
        $this->attachElementAvatar();
        if ($access) {
            $this->attachElementCategory();
        }
        if ($access) {
            $this->attachElementTourActivityType();
        }
        $this->attachElementAddressOne();
        $this->attachElementAddressTwo();
        $this->attachElementCountry();
        $this->attachElementState();
        $this->attachElementCity();
        $this->attachElementZip();
        if ($access) {
            $this->attachElementJobTitle();
        }
       $this->attachElementPhone();
        $this->attachElementPassword();
        $this->attachElementRePassword();
        $this->attachElementCaptcha();
        if ($access) {
            $this->attachElementTripAdvisorLink();
        }
        $this->attachElementSubmit();
    }

}
