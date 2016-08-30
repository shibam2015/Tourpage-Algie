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
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Tourpage\Library\Validator\CreditCardValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Digit as DigitValidator;

/**
 * Description of CardPaymentForm
 * @author amit
 */
class CardPaymentForm extends CForm {

    private function attachElementCardType() {
        $ccList = array_merge(array('' => ' -- Select card type -- '), \Tourpage\Helpers\Utils::getCcList());
        $cardType = new Select('card_type');
        $cardType->setAttribute('class', 'form-input');
        $cardType->setAttribute('style', 'width:42.8%');
        $cardType->setOptions($ccList);
        $this->add($cardType);
    }

    private function attachElementCardNumber() {
        //4148529247832259|Visa|Cvv-012|Exp-Nov(1)/2019
        //4417119669820331|Visa|Cvv-012|Exp-Nov(1)/2019
        $cardNumber = new Text('card_number');
        $cardNumber->setAttribute('class', 'form-input');
        $cardNumber->setAttribute('size', '30');
        $cardNumber->setAttribute('autocomplete', 'off');
        $cardNumber->addValidators(array(
            new CreditCardValidator(array('message' => 'Invalid Credit Card Number'))
        ));
        $this->add($cardNumber);
    }

    private function attachElementCardCvv() {
        $cardCVV = new Password('card_cvv');
        $cardCVV->setAttribute('class', 'form-input');
        $cardCVV->setAttribute('size', '30');
        $cardCVV->addValidators(array(
            new DigitValidator(array('message' => 'Invalid CVV code. CVV should be numeric [0-9].')),
            new StringLength(array('max' => 3, 'min' => 3, 'messageMaximum' => 'Maximum 3 Digit.', 'messageMinimum' => 'Minimum 3 Digit.', 'allowEmpty' => TRUE)),
        ));
        $this->add($cardCVV);
    }

    private function attachElementCardExpiryMonth() {
        $cardExpMonth = new Select('card_exp_month');
        $cardExpMonth->setAttribute('class', 'form-input');
        $cardExpMonth->setAttribute('style', 'width:20.7%');
        $cardExpMonth->setOptions(\Tourpage\Helpers\Utils::getMonths());
        $cardExpMonth->setDefault(\Tourpage\Helpers\Utils::__getCurrentMonth());
        $this->add($cardExpMonth);
    }

    private function attachElementCardExpiryYear() {
        $cardExpYear = new Select('card_exp_year');
        $cardExpYear->setAttribute('class', 'form-input');
        $cardExpYear->setAttribute('style', 'width:20.7%');
        $cardExpYear->setOptions(\Tourpage\Helpers\Utils::getYears(array('range' => array('end' => 20))));
        $cardExpYear->setDefault(\Tourpage\Helpers\Utils::__getCurrentYear());
        $this->add($cardExpYear);
    }

    private function attachElementNameOnCard() {
        $cardName = new Text('card_name');
        $cardName->setAttribute('class', 'form-input');
        $cardName->setAttribute('size', '30');
        $cardName->addValidators(array(
            new PresenceOf(array('message' => 'Name on card is required')),
        ));
        $this->add($cardName);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($options = null) {
        $this->setFormOptions($options);
        $this->attachElementCardType();
        $this->attachElementCardNumber();
        $this->attachElementCardCvv();
        $this->attachElementCardExpiryMonth();
        $this->attachElementCardExpiryYear();
        $this->attachElementNameOnCard();
    }

}
