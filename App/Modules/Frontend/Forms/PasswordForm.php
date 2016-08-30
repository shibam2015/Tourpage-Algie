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
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\StringLength as StringLength;

/**
 * Member Change Password Form
 * @author amit
 */
class PasswordForm extends CForm {

    private function attachElementOldPassword() {
        $oldPassword = new Password('old_password');
        $oldPassword->setLabel('Old Password');
        $oldPassword->addValidators(array(
            new PresenceOf(array('message' => 'Old Password is required')),
            new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.', 'allowEmpty' => true)),
        ));
        $this->add($oldPassword);
    }

    private function attachElementPassword() {
        $password = new Password('password');
        $password->setLabel('Password');
        $password->addValidators(array(
            new PresenceOf(array('message' => 'Password is required')),
            new StringLength(array('max' => 20, 'min' => 6, 'messageMaximum' => 'Password is too long', 'messageMinimum' => 'Password is too short.', 'allowEmpty' => true)),
        ));
        $this->add($password);
    }

    private function attachElementRePassword() {
        $rePassword = new Password('re_password');
        $rePassword->setLabel('Retype Password');
        $rePassword->addValidators(array(
            new Confirmation(array('with' => 'password', 'message' => 'Retype your password correctly'))
        ));
        $this->add($rePassword);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize() {
        $this->attachElementOldPassword();
        $this->attachElementPassword();
        $this->attachElementRePassword();
    }

}
