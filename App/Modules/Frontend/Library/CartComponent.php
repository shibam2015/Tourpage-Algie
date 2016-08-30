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

namespace Multiple\Frontend\Library;

/**
 * Component Cart
 * @author amit
 */
use Phalcon\Mvc\User\Component;

class CartComponent extends Component {

    /**
     * @var array cart tour items
     */
    private $cartItems = NULL;

    /**
     * @var int total item count in cart
     */
    public $totalItem = 0;

    /**
     * @var float total amount in cart
     */
    public $totalAmount = 0;

    /**
     * Session cart key
     */
    const CART_SES_KEY = 'cart';

    /**
     * Initializing Cart Component
     */
    public function __construct() {
        $this->cartItems = $this->session->get(self::CART_SES_KEY);
        if (count($this->cartItems) > 0) {
            $this->totalItem = count($this->cartItems);
            foreach ($this->cartItems as $cartItem) {
                $this->totalAmount += $cartItem['final_amount'];
            }
        }
    }

    /**
     * Add item to the cart
     * @param array $item
     */
    public function addItem(array $item) {
        foreach ($item as $itemKey => $itemData) {
            if (!$this->hasItem($itemKey)) {
                $this->cartItems[$itemKey] = [];
            }
            $this->cartItems[$itemKey] = $itemData;
        }
        $this->session->set(self::CART_SES_KEY, $this->cartItems);
    }

    /**
     * Get single item from cart
     * @param string $itemId
     * @return array
     */
    public function getItem($itemId) {
        if (isset($this->cartItems[$itemId])) {
            return $this->cartItems[$itemId];
        }
    }

    /**
     * Get all items from cart
     * @return array
     */
    public function getItems() {
        return $this->cartItems;
    }

    /**
     * Remove single item from cart
     * @param string $itemId
     */
    public function removeItem($itemId) {
        if (isset($this->cartItems[$itemId])) {
            unset($this->cartItems[$itemId]);
            $this->session->set(self::CART_SES_KEY, $this->cartItems);
        }
    }

    /**
     * Check for chart has item or not
     * @return boolean
     */
    public function isEmpty() {
        return $this->totalItem == 0;
    }

    /**
     * Check for this item exists in cart
     * @param string $itemId
     * @return blloean
     */
    public function hasItem($itemId) {
        return isset($this->cartItems[$itemId]);
    }

    /**
     * Adding Customer Information to session
     * @param array $customerInfo
     */
    public function setCustomerInfo(array $customerInfo) {
        if (!empty($customerInfo) && count($customerInfo) > 0) {
            $this->session->set(self::CART_SES_KEY . '_customer_info', $customerInfo);
        }
    }

    /**
     * Getting customer information from session
     * @return array Customer Details
     */
    public function getCustomerInfo() {
        return $this->session->get(self::CART_SES_KEY . '_customer_info');
    }

    /**
     * Adding member selected payment option to session
     * @param string $paymentOption
     */
    public function setPaymentOption($paymentOption) {
        if (!empty($paymentOption)) {
            $this->session->set(self::CART_SES_KEY . '_payment', $paymentOption);
        }
    }

    /**
     * Getting member choosed payment option from session
     * @return string Payment Option (paypal|Credit Card)
     */
    public function getPaymentOption() {
        return $this->session->get(self::CART_SES_KEY . '_payment');
    }

    /**
     * Adding credit card details to session
     * @param array $creditCardDetails
     */
    public function setCardDetails(array $creditCardDetails) {
        if (!empty($creditCardDetails) && count($creditCardDetails) > 0) {
            $this->session->set(self::CART_SES_KEY . '_cc_details', $creditCardDetails);
        }
    }

    /**
     * Getting credit card details from session
     * @return array
     */
    public function getCardDetails() {
        return $this->session->get(self::CART_SES_KEY . '_cc_details');
    }

    /**
     * Clear/Remove all item from cart
     */
    public function clear() {
        $this->cartItems = NULL;
        //$this->session->set(self::CART_SES_KEY, $this->cartItems);
        $this->session->remove(self::CART_SES_KEY);
        $this->session->remove(self::CART_SES_KEY . '_customer_info');
        $this->session->remove(self::CART_SES_KEY . '_payment');
        $this->session->remove(self::CART_SES_KEY . '_cc_details');
    }

}
