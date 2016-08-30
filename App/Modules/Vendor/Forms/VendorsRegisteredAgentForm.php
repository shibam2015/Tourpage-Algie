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
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Between;

/**
 * Form for vendor registered agent
 * @author amit
 */
class VendorsRegisteredAgentForm extends CForm {

    private function attachElementCommission() {
        $commission = new Text('commission');
        $commission->setLabel('Commission');
        $commission->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->commission)) {
                $commission->setDefault($this->entity->commission);
            }
        }
        $commission->addValidators(array(
            new PresenceOf(array('message' => 'Commission is required')),
            new Numericality(array('message' => 'Commission must be numeric', 'allowEmpty' => true)),
            new Between(array('minimum' => 0, 'maximum' => 100, 'message' => 'Commission must be between 0 and 100'))
        ));
        $this->add($commission);
    }

    private function attachElementStatus() {
        $status = new Select('status');
        $status->setLabel('Agent Status');
        $status->setAttribute('class', 'form-control');
        $status->setDefault(\Tourpage\Models\VendorsRegisteredAgents::AGENT_ACTIVE_STATUS_CODE);
        $status->setOptions(array(
            \Tourpage\Models\VendorsRegisteredAgents::AGENT_ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\VendorsRegisteredAgents::AGENT_INACTIVE_STATUS_CODE => 'Inactive'
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->status)) {
                $status->setDefault($this->entity->status);
            }
        }
        $this->add($status);
    }

    private function attachElementRequestStatus() {
        $status = new Select('request_status');
        $status->setLabel('Agentship');
        $status->setAttribute('class', 'form-control');
        $status->setDefault(\Tourpage\Models\VendorsRegisteredAgents::AGENT_PENDING_STATUS_CODE);
        $status->setOptions(array(
            \Tourpage\Models\VendorsRegisteredAgents::AGENT_APPROVED_STATUS_CODE => 'Approve',
            \Tourpage\Models\VendorsRegisteredAgents::AGENT_REJECTED_STATUS_CODE => 'Reject',
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->requestStatus)) {
                $status->setDefault($this->entity->requestStatus);
            }
        }
        $this->add($status);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        if (isset($this->options['edit']) && $this->options['edit']) {
            $submitButton->setAttribute('value', 'Update');
        }
        $submitButton->setAttribute('class', 'btn btn-primary');
        $this->add($submitButton);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementCommission();
        $this->attachElementStatus();
        $this->attachElementRequestStatus();
        $this->attachElementSubmit();
    }

}
