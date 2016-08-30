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
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Member login form
 * @author amit
 */
class MemberLoginForm extends CForm {

    private function attachElementEmail() {
        $userEmail = new Text('useremail');
        $userEmail->setLabel('Email');
        $userEmail->setAttribute('placeholder', 'Email Address');
		//$userEmail->setAttribute('size', '50px');
        $userEmail->addValidators(array(
            new PresenceOf(array('message' => 'Email Address is required'))
        ));
        $this->add($userEmail);
    }

    private function attachElementPassword() {
        $passWord = new Password('password');
        $passWord->setLabel('Password');
        $passWord->setAttribute('placeholder', 'Password');
	    //$passWord->setAttribute('size', '50px');
        $passWord->addValidators(array(
            new PresenceOf(array('message' => 'Password is required'))
        ));
        $this->add($passWord);
    }

    private function attachElementRemember() {
        $rememberMe = new Check('remember_me');
        $rememberMe->setDefault(1);
        $this->add($rememberMe);
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
    }

}
