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

/**
 * Form for vendor
 * This is use for create new vendor or edit
 * existing vendor data
 * @access backend
 * @author amit
 */
class VendorForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {
        $vendorBusinessName = new Text('vendor_business_name');
        $vendorBusinessName->setLabel('Business Name');
        $vendorBusinessName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->businessName)) {
                $vendorBusinessName->setDefault($entity->businessName);
            }
        }
        $vendorBusinessName->addValidators(array(
            new PresenceOf(array('message' => 'Business Name is required'))
        ));
        $this->add($vendorBusinessName);

        $vendorCategory = new Select('vendor_category', \Tourpage\Models\CategoryVendor::find(array('categoryStatus = :status:', 'bind' => array('status' => \Tourpage\Models\CategoryVendor::ACTIVE_STATUS_CODE))), array(
            'using' => array('categoryId', 'categoryTitle'),
            'useEmpty' => true,
            'emptyText' => '-- Select Category --',
        ));
        $vendorCategory->setLabel('Category');
        $vendorCategory->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->vendorCategory)) {
                $vendorCategory->setDefault($entity->vendorCategory);
            }
        }
        $vendorCategory->addValidators(array(
            new PresenceOf(array('message' => 'Category is required')),
        ));
        $this->add($vendorCategory);

        $vendorTourActivityType = new Select('vendor_tour_activity_type[]', \Tourpage\Models\TourTypes::find(array('tourTypesStatus = :status:', 'bind' => array('status' => \Tourpage\Models\TourTypes::ACTIVE_STATUS_CODE))), array(
            'using' => array('tourTypesId', 'tourTypesTitle'),
        ));
        $vendorTourActivityType->setLabel('Tour & activities Type');
        $vendorTourActivityType->setAttribute('class', 'form-control');
        $vendorTourActivityType->setAttribute('multiple', TRUE);
        if (isset($options['edit']) && $options['edit']) {
            $defaultValues = [];
            if ($entity->vendorTourTypes->count() > 0) {
                foreach ($entity->vendorTourTypes as $vtat) {
                    $defaultValues[] = $vtat->tourTypesId;
                }
            }
            if (count($defaultValues) > 0) {
                $vendorTourActivityType->setDefault($defaultValues);
            }
        }
        $this->add($vendorTourActivityType);

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
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->countryId)) {
                $vendorCountry->setDefault($entity->countryId);
            }
        }
        $vendorCountry->addValidators(array(
            new PresenceOf(array('message' => 'Country is required')),
        ));
        $this->add($vendorCountry);

        $formState = ['' => '-- Select Your State --'];
        $vendorState = new Select('vendor_state');
        $vendorState->setLabel('Province / State / County');
        $vendorState->setAttribute('class', 'form-control');
        $vendorSelectCountry = $vendorSelectState = 0;
        if (isset($options['edit']) && $options['edit']) {
            $vendorSelectCountry = $entity->countryId;
            $vendorSelectState = $entity->stateId;
        }
        if ($this->request->isPost()) {
            $vendorSelectCountry = $this->request->getPost('vendor_country');
            $vendorSelectState = $this->request->getPost('vendor_state');
        }
        if ($this->request->isPost() || (isset($options['edit']) && $options['edit'])) {
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
        $vendorState->addValidators(array(
            new PresenceOf(array('message' => 'State is required')),
        ));
        $this->add($vendorState);

        $vendorCity = new Text('vendor_city');
        $vendorCity->setLabel('City');
        $vendorCity->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->city)) {
                $vendorCity->setDefault($entity->city);
            }
        }
        $vendorCity->addValidators(array(
            new PresenceOf(array('message' => 'City is required')),
        ));
        $this->add($vendorCity);

        $vendorStatus = new Select('vendor_status');
        $vendorStatus->setLabel('Status');
        $vendorStatus->setAttribute('class', 'form-control');
        $vendorStatus->setOptions(array(
            '' => '-- select status --',
            \Tourpage\Models\Vendors::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\Vendors::INACTIVE_STATUS_CODE => 'Inactive',
        ));
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->status)) {
                $vendorStatus->setDefault($entity->status);
            }
        }
        $vendorStatus->addValidators(array(
            new PresenceOf(array('message' => 'Status is require')),
        ));
        $this->add($vendorStatus);

        $vendorJobTitle = new Text('vendor_job_title');
        $vendorJobTitle->setLabel('Job Title');
        $vendorJobTitle->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->jobTitle)) {
                $vendorJobTitle->setDefault($entity->jobTitle);
            }
        }
        $vendorJobTitle->addValidators(array(
            new PresenceOf(array('message' => 'Job Title is required')),
        ));
        $this->add($vendorJobTitle);

        $vendorFirstName = new Text('vendor_first_name');
        $vendorFirstName->setLabel('First Name');
        $vendorFirstName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->firstName)) {
                $vendorFirstName->setDefault($entity->firstName);
            }
        }
        $vendorFirstName->addValidators(array(
            new PresenceOf(array('message' => 'First Name is required'))
        ));
        $this->add($vendorFirstName);

        $vendorLastName = new Text('vendor_last_name');
        $vendorLastName->setLabel('Last Name');
        $vendorLastName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->lastName)) {
                $vendorLastName->setDefault($entity->lastName);
            }
        }
        $vendorLastName->addValidators(array(
            new PresenceOf(array('message' => 'Last Name is required'))
        ));
        $this->add($vendorLastName);

        $vendorEmail = new Text('vendor_email');
        $vendorEmail->setLabel('Email');
        $vendorEmail->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->emailAddress)) {
                $vendorEmail->setDefault($entity->emailAddress);
                $vendorEmail->setAttribute('readonly', 'readonly');
            }
        }
        $vendorEmail->addValidators(array(
            new PresenceOf(array('message' => 'Email is required')),
            new Email(array('message' => 'Email is not valid'))
        ));
        $this->add($vendorEmail);

        $vendorPhone = new Text('vendor_phone');
        $vendorPhone->setLabel('Phone');
        $vendorPhone->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->phone)) {
                $vendorPhone->setDefault($entity->phone);
            }
        }
        $this->add($vendorPhone);

        $vendorTripAdvisorLink = new Text('vendor_trip_advisor_link');
        $vendorTripAdvisorLink->setLabel('Trip Advisor Link');
        $vendorTripAdvisorLink->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->tripAdvLink)) {
                if ($entity->isTripAdv == 'y') {
                    $vendorTripAdvisorLink->setDefault($entity->tripAdvLink);
                } else {
                    $vendorTripAdvisorLink->setAttribute('disabled', 'disabled');
                }
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
        $this->add($vendorTripAdvisorLink);

        $vendorPassword = new Password('vendor_password');
        $vendorPassword->setLabel('Password');
        $vendorPassword->setAttribute('class', 'form-control');
        if (isset($options['form_type']) && $options['form_type'] == 'new') {
            $vendorPassword->addValidators(array(
                new PresenceOf(array('message' => 'Password is required')),
                new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.'))
            ));
        }
        $this->add($vendorPassword);

        $vendorRePassword = new Password('vendor_re_password');
        $vendorRePassword->setLabel('Retype Password');
        $vendorRePassword->setAttribute('class', 'form-control');
        if (isset($options['form_type']) && $options['form_type'] == 'new') {
            $vendorRePassword->addValidators(array(
                new Confirmation(array('with' => 'vendor_password', 'message' => 'Retype your password correctly'))
            ));
        }
        $this->add($vendorRePassword);

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
