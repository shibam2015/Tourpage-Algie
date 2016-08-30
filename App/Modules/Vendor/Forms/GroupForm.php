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
class GroupForm extends CForm {

    private function attachElementVendor() {
        $groupVendor = new Hidden('vendor');
        $groupVendor->setDefault($this->vendors->getId());
        $this->add($groupVendor);
    }
	private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }
	private function attachElementName() {
        $groupName = new Text('group_name');
        $groupName->setLabel('Group Name');
	    $groupName->setAttribute('class', 'form-control');
		
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->groupName)) {
                $groupName->setDefault($this->entity->groupName);
            }
            $groupName->setAttribute('disabled', 'disabled');
        } else {
            $groupName->addValidators(array(
                new PresenceOf(array('message' => 'Group name is required'))
            ));
        }
        $this->add($groupName);
     }
 
  public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementVendor();
		$this->attachElementName();
		/*$this->attachElementMapTour();*/
         $this->attachElementSubmit();
 }
}