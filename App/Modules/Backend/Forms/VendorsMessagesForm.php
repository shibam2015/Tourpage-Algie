<?php
namespace Multiple\Backend\Forms;

use Tourpage\Forms\CForm;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Form for VendorsMessageaForm
 * @author satya
 */
class VendorsMessagesForm extends CForm {

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
		
		$formVendor = ['' => '-- Select Vendor --'];
        $vendorData = \Tourpage\Models\Vendors::query();
        $vendorData->columns(array('vendorId','firstName','lastName'));
        $vendors = $vendorData->execute();
        if ($vendors->count() > 0) {
            foreach ($vendors as $vendorDp) {
                $formVendor[$vendorDp->vendorId] = $vendorDp->firstName.' '.$vendorDp->lastName;
            }
        }
        $vendor = new Select('vendor');
        $vendor->setOptions($formVendor);
        $vendor->setLabel('Vendor');
        $vendor->setAttribute('class', 'form-control');
        $vendor->addValidators(array(
            new PresenceOf(array('message' => 'Vendor is required')),
        ));
        $this->add($vendor);

        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }

}
