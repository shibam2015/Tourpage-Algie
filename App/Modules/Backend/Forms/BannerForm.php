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
use Phalcon\Validation\Validator\Url as UrlValidator;

/**
 * Form of BannerForm
 * @author amit
 */
class BannerForm extends CForm {

    private function attachElementLink() {
        $bannerLink = new Text('banner_link');
        $bannerLink->setLabel('Banner link');
        $bannerLink->setAttribute('class', 'form-control');
        $bannerLink->addValidators(array(
            new UrlValidator(array('message' => 'Invalid banner link', 'allowEmpty' => TRUE))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->bannerLink)) {
                $bannerLink->setDefault($this->entity->bannerLink);
            }
        }
        $this->add($bannerLink);
    }

    private function attachElementStatus() {
        $bannerStatus = new Select('banner_status');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->bannerStatus)) {
                $bannerStatus->setDefault($this->entity->bannerStatus);
            }
        }
        $bannerStatus->setLabel('Banner Status');
        $bannerStatus->setAttribute('class', 'form-control');
        $bannerStatus->setOptions(array(
            \Tourpage\Models\Banners::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\Banners::INACTIVE_STATUS_CODE => 'Inactive'
        ));
        $this->add($bannerStatus);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('value', 'Upload');
        if (isset($this->options['edit']) && $this->options['edit']) {
            $submitButton->setAttribute('value', 'Update');
        }
        $submitButton->setAttribute('class', 'btn btn-danger');
        $this->add($submitButton);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = NULL, $options = NULL) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementLink();
        $this->attachElementStatus();
        $this->attachElementSubmit();
    }

}
