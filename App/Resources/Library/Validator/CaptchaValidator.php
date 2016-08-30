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

namespace Tourpage\Library\Validator;

/**
 * Captcha Validator Class
 * @author amit
 */
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;
use Phalcon\DI;

class CaptchaValidator extends Validator implements ValidatorInterface {

    /**
     * Executes the validation
     * @param Phalcon\Validation $validator
     * @param string $attribute
     * @return boolean
     */
    public function validate(\Phalcon\Validation $validator, $attribute) {
        $value = $validator->getValue($attribute);
        $di = DI::getDefault();
        $session = $di->getShared('session');
        $session_var = (new \Tourpage\Library\SimpleCaptcha())->session_var;
        $userValue = $session->get($session_var);
        if ($userValue != $value) {
            $message = $this->getOption('message');
            if (!$message) {
                $message = 'Captcha is not valid';
            }
            $validator->appendMessage(new Message($message, $attribute, 'Captcha'));
            return false;
        }
        return true;
    }

}
