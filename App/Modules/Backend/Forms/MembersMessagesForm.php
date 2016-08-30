<?php
namespace Multiple\Backend\Forms;

use Tourpage\Forms\CForm;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Form for MembersMessagesForm
 * @author satya
 */
class MembersMessagesForm extends CForm {

    /**
     * Initializing form
     * @param object $entity
     * @param boolean $options
     */
    public function initialize($entity = null, $options = null) {

        $messageText = new TextArea('message_text');
        $messageText->setLabel('Message Text');
        $messageText->setAttribute('class', 'form-control');
        $messageText->addValidators(array(
            new PresenceOf(array('message' => 'Message Text is required'))
        ));
        $this->add($messageText);
		
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
