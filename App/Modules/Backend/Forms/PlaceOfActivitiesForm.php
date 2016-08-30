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
 * Form for PlaceOfAttractionsForm
 * @author amit
 */
class PlaceOfActivitiesForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {

        $selectCountry = $selectState = $selectCity = 0;
        if (isset($options['edit']) && $options['edit']) {
            $selectCountry = $entity->countryId;
            $selectState = $entity->stateId;
            $selectCity = $entity->cityId;
        }
        if ($this->request->isPost()) {
            $selectCountry = $this->request->getPost('country');
            $selectState = $this->request->getPost('state');
            $selectCity = $this->request->getPost('city');
        }

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

        $formCity = ['' => '-- Select City --'];
        $city = new Select('city');
        $city->setLabel('City');
        $city->setAttribute('class', 'form-control');
        if ($this->request->isPost() || (isset($options['edit']) && $options['edit'])) {
            $cities = \Tourpage\Models\City::find(array(
                        "countryId = :country_id: AND stateId = :state_id: AND status = :city_status:",
                        "bind" => array(
                            ":country_id" => $selectCountry,
                            ":state_id" => $selectState,
                            ":city_status" => \Tourpage\Models\City::ACTIVE_STATUS_CODE
                        )
            ));
            if ($cities && $cities->count() > 0) {
                foreach ($cities as $c) {
                    $formCity[$c->cityId] = $c->name;
                }
            }
        }
        $city->setOptions($formCity);
        if ($selectCity > 0) {
            $city->setDefault($selectCity);
        }
        $city->addValidators(array(
            new PresenceOf(array('message' => 'City is required')),
        ));
        $this->add($city);

        $attractionName = new Text('activity_name');
        $attractionName->setLabel('Name of Attraction');
        $attractionName->setAttribute('class', 'form-control');
        if (isset($options['edit']) && $options['edit']) {
            if ($entity && isset($entity->activityName)) {
                $attractionName->setDefault($entity->activityName);
            }
        }
        $attractionName->addValidators(array(
            new PresenceOf(array('message' => 'Name of activity is required'))
        ));
        $this->add($attractionName);

        $status = new Select('activity_status');
        $status->setLabel('Status');
        $status->setAttribute('class', 'form-control');
        $status->setOptions(array(
            \Tourpage\Models\PlaceOfActivities::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\PlaceOfActivities::INACTIVE_STATUS_CODE => 'Inactive',
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
