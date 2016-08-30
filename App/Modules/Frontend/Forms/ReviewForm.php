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
use Phalcon\Forms\Element\TextArea;

/**
 * Class ReviewForm
 * Form for Tour Review and Rating form
 * @author amit
 */
class ReviewForm extends CForm {

    private function attachElementReviewName() {
        $reviewerName = new Text('rrname');
        $reviewerName->setAttribute('class', 'form-control');
        $reviewerName->setAttribute('autocomplete', 'off');

        $this->add($reviewerName);
    }

    private function attachElementReviewEmailAddress() {
        $reviewerName = new Text('rremail');
        $reviewerName->setAttribute('class', 'form-control');
        $reviewerName->setAttribute('autocomplete', 'off');

        $this->add($reviewerName);
    }

    private function attachElementReviewText() {
        $reviewerName = new TextArea('rrtext');
        $reviewerName->setAttribute('class', 'form-control');
        $reviewerName->setAttribute('style', 'width: 100%;');
        $reviewerName->setAttribute('autocomplete', 'off');

        $this->add($reviewerName);
    }

    /**
     * Initializing Review Form for Tour
     * @param array $options
     */
    public function initialize($options = null) {
        $this->attachElementReviewName();
        $this->attachElementReviewEmailAddress();
        $this->attachElementReviewText();
    }

}
