<?php

namespace Tourpage\Models;

/**
 * Model MembersMessages
 * @author satya
 */
class MembersMessages extends ApplicationModel {
	
	const READ_STATUS_CODE = 'Read';
    const UNREAD_STATUS_CODE = 'Unread';

    /**
     * Initializing Model MembersMessages
     */
    public function initialize() {
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
		$this->belongsTo('messageId', '\Tourpage\Models\Messages', 'messageId', array(
            'alias' => 'message'
        ));
    }
	
	/**
     * Getting status string for member messages
     * @param bool $escapeHtml
     * @return string
     */
    public function getMemberMessageStatus($escapeHtml = false) {
        switch ($this->memberMessageStatus) {
            case self::READ_STATUS_CODE:
                return $escapeHtml ? 'Read' : '<span class="label label-success">Read</span>';
                break;
            case self::UNREAD_STATUS_CODE:
                return $escapeHtml ? 'Unread' : '<span class="label label-danger">Unread</span>';
                break;
        }
    }

}
