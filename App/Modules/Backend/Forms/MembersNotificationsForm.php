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
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Form for MembersNotificationsForm
 * @author amit
 */
class MembersNotificationsForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {

        $notificationText = new TextArea('notification_text');
        $notificationText->setLabel('Notification Text');
        $notificationText->setAttribute('class', 'form-control');
        $notificationText->addValidators(array(
            new PresenceOf(array('message' => 'Notification Text is required'))
        ));
        $this->add($notificationText);
		
		$formMember = ['' => '-- Select Member --'];
        $memberData = \Tourpage\Models\Members::query();
        $memberData->columns(array('memberId','firstName','lastName'));
        $members = $memberData->execute();
        if ($members->count() > 0) {
            foreach ($members as $memberDp) {
                $formMember[$memberDp->memberId] = $memberDp->firstName.' '.$memberDp->lastName;
            }
        }
        $member = new Select('member');
        $member->setOptions($formMember);
        $member->setLabel('Member');
        $member->setAttribute('class', 'form-control');
        $member->addValidators(array(
            new PresenceOf(array('message' => 'Member is required')),
        ));
        $this->add($member);

        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }

}
