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

namespace Tourpage\Library;

use Phalcon\Mvc\User\Component;

/**
 * Component for PayPal Payment Getway
 * @author amit
 */
class Paypal extends Component {

    /**
     * Paypal communication handler
     * @var object 
     */
    private $apiContext = null;

    /**
     * Connect with PayPal API
     */
    public function connect() {
        $mode = $this->getMode();
        $clientId = \Tourpage\Helpers\Utils::getVar("paypal_{$mode}_client_id");
        $clientSecret = \Tourpage\Helpers\Utils::getVar("paypal_{$mode}_client_secret");
        $this->apiContext = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential($clientId, $clientSecret));
        $this->apiContext->setConfig(array(
            'mode' => $mode
        ));

        return $this->apiContext;
    }

    /**
     * Get PayPal Mode
     * Possible values are "sandbox|live"
     * @return string
     */
    public function getMode() {
        return \Tourpage\Helpers\Utils::getVar('paypal_mode');
    }

    /**
     * Setting PayPal Mode
     * Possible values are "sandbox|live"
     * @param string $mode
     */
    public function setMode($mode) {
        \Tourpage\Helpers\Utils::setVar('paypal_mode', $mode);
    }

}
