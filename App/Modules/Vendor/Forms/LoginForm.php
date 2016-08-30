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
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Submit;

/*
 * Login Form
 */

class LoginForm extends CForm {

    private function attachElementEmail() {
        //Email
        $userEmail = new Text('useremail');
        $userEmail->setLabel('Email');
        $userEmail->setAttribute('class', 'form-control');
        $this->add($userEmail);
    }

    private function attachElementPassword() {
        //Password
        $passWord = new Password('password');
        $passWord->setLabel('Password');
        $passWord->setAttribute('class', 'form-control');
        $this->add($passWord);
    }

    private function attachElementRemember() {
        //Remember Me
        $rememberMe = new Check('remember_me');
        $rememberMe->setDefault(1);
        $this->add($rememberMe);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Sign in');
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        $this->attachElementEmail();
        $this->attachElementPassword();
        $this->attachElementRemember();
        $this->attachElementSubmit();
    }

}
