<?php

namespace Tourpage\Models;

/**
 * Model VendorsMessages
 * @author satya
 */
class VendorsMessages extends ApplicationModel {
	
	const READ_STATUS_CODE = 'Read';
    const UNREAD_STATUS_CODE = 'Unread';

    /**
     * Initializing Model VendorsMessages
     */
    public function initialize() {
		$this->belongsTo('vendorId', '\Tourpage\Models\Vendors', 'vendorId', array(
            'alias' => 'vendor'
        ));
		$this->belongsTo('messageId', '\Tourpage\Models\Messages', 'messageId', array(
            'alias' => 'message'
        ));
    }
	
	/**
     * Getting status string for vendor messages
     * @param bool $escapeHtml
     * @return string
     */
    public function getVendorMessageStatus($escapeHtml = false) {
        switch ($this->vendorMessageStatus) {
            case self::READ_STATUS_CODE:
                return $escapeHtml ? 'Read' : '<span class="label label-success">Read</span>';
                break;
            case self::UNREAD_STATUS_CODE:
                return $escapeHtml ? 'Unread' : '<span class="label label-danger">Unread</span>';
                break;
        }
    }

}
