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
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Form for Key highlight
 * @author amit
 */
class KeyHIghlightForm extends CForm {

    private function attachElementTitle() {
        $keyHighlightTitle = new Text('key_highlight_title');
        $keyHighlightTitle->setLabel('Key Highlight');
        $keyHighlightTitle->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->keyhighlightTitle)) {
                $keyHighlightTitle->setDefault($this->entity->keyhighlightTitle);
            }
        }
        $keyHighlightTitle->addValidators(array(
            new PresenceOf(array('message' => 'Key Highlight Title is required'))
        ));
        $this->add($keyHighlightTitle);
    }

    private function attachElementStatus() {
        $status = new Select('key_status');
        $status->setLabel('Key Highlight Status');
        $status->setAttribute('class', 'form-control');
        $status->setOptions(array(
            \Tourpage\Models\KeyHighlight::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\KeyHighlight::INACTIVE_STATUS_CODE => 'Inactive',
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->keyhighlightStatus)) {
                $status->setDefault($this->entity->keyhighlightStatus);
            }
        }
        $this->add($status);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-danger');
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
        $this->attachElementTitle();
        $this->attachElementStatus();
        $this->attachElementSubmit();
    }

}
