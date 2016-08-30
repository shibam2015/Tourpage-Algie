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
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Digit as DigitValidator;

/**
 * Member Registration Form
 * @author amit
 */
class ContactForm extends CForm {
    private function attachElementSubject() {
        $subject = new Text('subject');
        $subject->setAttribute('class','form-control input-field');
	$subject->setAttribute('placeholder','Subject*');
		//$firstName->setAttribute('size','50px');
		
        $subject->addValidators(array(
            new PresenceOf(array('message' => 'Subject is required'))
        ));
//        if (isset($this->options['first_name']) && $this->options['first_name']) {
//            $firstName->setDefault($this->options['first_name']);
//        }
        
        $this->add($subject);
    }

    private function attachElementName() {
        $name = new Text('name');
        $name->setAttribute('class','form-control input-field');
	$name->setAttribute('placeholder','Your Name*');
		//$firstName->setAttribute('size','50px');
		
        $name->addValidators(array(
            new PresenceOf(array('message' => 'Name is required'))
        ));
//        if (isset($this->options['first_name']) && $this->options['first_name']) {
//            $firstName->setDefault($this->options['first_name']);
//        }
        
        $this->add($name);
    }

//    private function attachElementLastName() {
//        $lastName = new Text('last_name');
//        $lastName->setAttribute('class', 'form-input');
//		$lastName->setAttribute('placeholder','Last Name*');
//		//$lastName->setAttribute('size','50px');
//        $lastName->addValidators(array(
//            new PresenceOf(array('message' => 'Last Name is required'))
//        ));
//        if (isset($this->options['last_name']) && $this->options['last_name']) {
//            $lastName->setDefault($this->options['last_name']);
//        }
//        if (isset($this->options['edit']) && $this->options['edit']) {
//            if ($this->entity && isset($this->entity->lastName)) {
//                $lastName->setDefault($this->entity->lastName);
//            }
//        }
//        $this->add($lastName);
//    }

    private function attachElementEmailAddress() {
        $emailAddress = new Text('email_address');
        $emailAddress->setAttribute('class', 'form-control input-field');
        $emailAddress->setAttribute('placeholder','Email Address*');
		//$emailAddress->setAttribute('size','50px');
		$emailAddress->setLabel('Email');
        $emailAddress->addValidators(array(            
            new Email(array('message' => 'Email Address is not valid'))
        ));
//        if (isset($this->options['email_address']) && $this->options['email_address']) {
//            $emailAddress->setDefault($this->options['email_address']);
//        }
        
        $this->add($emailAddress);
    }

   

    private function attachElementPhone() {
        
            $phone = new Text('phone');
            $phone->setAttribute('class', 'form-control input-field');
            $phone->setAttribute('placeholder','Phone Number*');
//            if (isset($this->options['edit']) && $this->options['edit']) {
//                if ($this->entity && isset($this->entity->phone)) {
//                    $phone->setDefault($this->entity->phone);
//                }
//            }
            $phone->addValidators(array(
                new DigitValidator(array('message' => 'Phone must be numeric', 'allowEmpty' => true))
            ));
            $this->add($phone);
       
    }
    
     private function attachElementMessage() {
        
            $message = new Text('message');
            $message->setAttribute('class', 'form-control input-field');
            $message->setAttribute('placeholder','Your Message*');
            //$message->setAttribute('multiple', TRUE);
            $message->addValidators(array(
            new PresenceOf(array('message' => 'Message is required'))
        ));
             $this->add($message);
     }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementMessage();
        $this->attachElementPhone();
        $this->attachElementSubject();
        $this->attachElementName();
        $this->attachElementEmailAddress();
        
    }

}


