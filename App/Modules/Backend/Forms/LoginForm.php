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
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;

/**
 * Login Form
 */
class LoginForm extends CForm {

    public function initialize($entity = null, $options = null) {
        //Username
        $userName = new Text('username');
        $userName->setLabel('Username');
        $userName->setAttribute('class', 'form-control');
        $this->add($userName);

        //Password
        $passWord = new Password('password');
        $passWord->setLabel('Password');
        $passWord->setAttribute('class', 'form-control');
        $this->add($passWord);

        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Login');
        $submitButton->setAttribute('class', 'btn btn-danger');
        $this->add($submitButton);
    }

}
