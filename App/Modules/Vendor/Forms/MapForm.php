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
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex as RegexValidator;

/**
 * Tour Form
 * @author amit
 */
class MapForm extends CForm {

    private function attachElementVendor() {
        $groupVendor = new Hidden('vendor');
        $groupVendor->setDefault($this->vendors->getId());
        $this->add($groupVendor);
    }
	private function attachElementGroupName() {
        $formGroup = ['' => 'Please choose one...'];
        $groupData = \Tourpage\Models\GroupsVendors::query();
		$groupData->where("vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $this->vendors->getId();
		$groupData->bind($modelBind);
		$groupData->order("\Tourpage\Models\GroupsVendors.groupVendorsId DESC");
        $groups = $groupData->execute();
        if ($groups->count() > 0) {
            foreach ($groups as $g) {
                $formGroup[$g->groupId] = $g->group->groupName;
            }
        }
        $groupName = new Select('group_name');
        $groupName->setOptions($formGroup);
        $groupName->setLabel('Select Group');
        $groupName->setAttribute('class', 'form-control');
            $groupName->addValidators(array(
                new PresenceOf(array('message' => 'Group is required')),
            ));
        $this->add($groupName);
    }
	
	/*private function attachElementTourName() {
        $formTour = ['' => 'Please choose one...'];
	    $tourData = \Tourpage\Models\VendorsTours::query();
        $tourData->where("vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $this->vendors->getId();
	    $tourData->bind($modelBind);
	    $tourData->order("\Tourpage\Models\VendorsTours.vendorTourId DESC");
        $tours = $tourData->execute();
        if ($tours->count() > 0) {
            foreach ($tours as $t) {
                $formTour[$t->tourId]=$t->tour->tourTitle;
            }
        }
        $tourName = new Select('tour_name');
        $tourName->setOptions($formTour);
        $tourName->setLabel('Select Tour');
        $tourName->setAttribute('class', 'form-control');
        $tourName->addValidators(array(
                new PresenceOf(array('message' => 'Tour is required')),
            ));
        $this->add($tourName);
    }*/

	private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }
  public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementVendor();
		$this->attachElementGroupName();
		//$this->attachElementTourName();
        $this->attachElementSubmit();
 }
}
     
    