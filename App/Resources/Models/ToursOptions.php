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

namespace Tourpage\Models;

/**
 * Model Tours Options
 * @author amit
 */
class ToursOptions extends ApplicationModel {

    const OPTION_PER_PERSON_CODE = 'opp';
    const OPTION_PER_GROUP_CODE = 'opg';
    const OPTION_PER_PERSON_TEXT = 'Per Person';
    const OPTION_PER_GROUP_TEXT = 'Per Group';

    public $optionPriceTypeText = '';

    /**
     * Initializing Model Tours Options
     */
    public function initialize() {
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId', array(
            'alias' => 'tour'
        ));
    }

    /**
     * Implements afterFetch() Event
     * manipulate variable data after fetch from database
     */
    public function afterFetch() {
        $this->optionPriceTypeText = self::OPTION_PER_PERSON_TEXT;
        if ($this->optionPriceType == self::OPTION_PER_GROUP_CODE) {
            $this->optionPriceTypeText = self::OPTION_PER_GROUP_TEXT;
        }
    }

}
