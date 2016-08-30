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

use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

/**
 * Credit Card Validator Class
 * @author amit
 */
class CreditCardValidator extends Validator implements ValidatorInterface {

    const CCV_VISA_REGX = '/^(?:4[0-9]{12}(?:[0-9]{3})?)$/';
    const CCV_MASTER_CARD_REGX = '/^(?:5[1-5][0-9]{14})$/';
    const CCV_AMERICAN_EXPRESS_REGX = '/^(?:3[47][0-9]{13})$/';
    const CCV_MAESTRO_REGX = '/^(?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?$/';
    const CCV_JCB_REGX = '/^(?:(?:2131|1800|35\d{3})\d{11})$/';
    const CCV_DISCOVER_REGX = '/^(?:6(?:011|5[0-9][0-9])[0-9]{12})$/';
    const CCV_DINNERS_CLUB_REGX = '/^(?:3(?:0[0-5]|[68][0-9])[0-9]{11})$/';

    private $validFormat = false;

    /**
     * Executes the validation
     * @param Phalcon\Validation $validator
     * @param string $attribute
     * @return boolean
     */
    public function validate(\Phalcon\Validation $validator, $attribute) {
        $isValidCard = TRUE;
        $processValidation = TRUE;
        $value = $validator->getValue($attribute);
        $mod10 = $this->getOption('mod10');
        if ($this->getOption('allowEmpty')) {
            $processValidation = FALSE;
            if (!empty($value)) {
                $processValidation = TRUE;
            }
        }
        if ($processValidation) {
            if ($this->checkFormat($value)) {
                if ($mod10) {
                    if (!$this->mod10($value)) {
                        $isValidCard = FALSE;
                    }
                }
            } else {
                $isValidCard = FALSE;
            }
        }

        if (!$isValidCard) {
            $message = $this->getOption('message');
            if (!$message) {
                $message = 'Invalid Card Number';
            }
            $validator->appendMessage(new Message($message, $attribute, 'CreditCard'));
        }
        return $isValidCard;
    }

    /**
     * Check a credit card number prefix
     * @access private
     * @param string carNumber
     * @return bool
     */
    private function checkFormat($cardNumber) {
        if (preg_match_all('/^[0-9]+$/', $cardNumber, $matches)) {
            if (preg_match_all(self::CCV_VISA_REGX, $cardNumber, $matches)) {
                $this->validFormat = TRUE;
            } else {
                if (preg_match_all(self::CCV_MASTER_CARD_REGX, $cardNumber, $matches)) {
                    $this->validFormat = TRUE;
                } else {
                    if (preg_match_all(self::CCV_AMERICAN_EXPRESS_REGX, $cardNumber, $matches)) {
                        $this->validFormat = TRUE;
                    } else {
                        if (preg_match_all(self::CCV_JCB_REGX, $cardNumber, $matches)) {
                            $this->validFormat = TRUE;
                        } else {
                            if (preg_match_all(self::CCV_DISCOVER_REGX, $cardNumber, $matches)) {
                                $this->validFormat = TRUE;
                            }
                        }
                    }
                }
            }
        }
        return $this->validFormat;
    }

    /**
     * Check credit card number by Mod 10 algorithm
     * @access private
     * @param number carNumber
     * @return bool
     */
    private function mod10($cardNumber) {
        $cardNumber = strrev($cardNumber);
        $numSum = 0;
        for ($i = 0; $i < strlen($cardNumber); $i ++) {
            $currentNum = substr($cardNumber, $i, 1);
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }
            $numSum += $currentNum;
        }
        return ($numSum % 10 == 0);
    }

}
