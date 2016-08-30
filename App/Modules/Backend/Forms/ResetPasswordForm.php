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
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Confirmation;

/**
 * Form for Reset Password
 * @author amit
 */
class ResetPasswordForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {
        if (isset($options['edit']) && $options['edit']) {
            $currentPassword = new Password('current_password');
            $currentPassword->setLabel('Current Password');
            $currentPassword->setAttribute('class', 'form-control');
            $currentPassword->addValidators(array(
                new PresenceOf(array('message' => 'Current Password is Required')),
            ));
            $this->add($currentPassword);
        }

        $newPassword = new Password('new_password');
        $newPassword->setLabel('New Password');
        $newPassword->setAttribute('class', 'form-control');
        $newPassword->addValidators(array(
            new PresenceOf(array('message' => 'New Password is Required')),
        ));
        $this->add($newPassword);

        $repeatPassword = new Password('repeat_password');
        $repeatPassword->setLabel('Repeat Password');
        $repeatPassword->setAttribute('class', 'form-control');
        $repeatPassword->addValidators(array(
            new Confirmation(array('with' => 'new_password', 'message' => 'Retype your new password correctly'))
        ));
        $this->add($repeatPassword);

        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Reset');
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }

}
