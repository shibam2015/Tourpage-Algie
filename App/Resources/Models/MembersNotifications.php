<?php

namespace Tourpage\Models;

/**
 * Model MembersNotifications
 * This is to store the memberId and notificationId
 * related to each other through notification
 * @author satya
 */
class MembersNotifications extends ApplicationModel {
	
	const READ_STATUS_CODE = 'Read';
    const UNREAD_STATUS_CODE = 'Unread';

    /**
     * Initializing model
     */
    public function initialize() {
        $this->belongsTo('memberId', '\Tourpage\Models\Members', 'memberId', array(
            'alias' => 'member'
        ));
        $this->belongsTo('notificationId', '\Tourpage\Models\Notifications', 'notificationId', array(
            'alias' => 'notification'
        ));
    }
	
	/**
     * Getting status string for members notifications
     * @param bool $escapeHtml
     * @return string
     */
    public function getMemberNotificationStatus($escapeHtml = false) {
        switch ($this->memberNotificationStatus) {
            case self::READ_STATUS_CODE:
                return $escapeHtml ? 'Read' : '<span class="label label-success">Read</span>';
                break;
            case self::UNREAD_STATUS_CODE:
                return $escapeHtml ? 'Unread' : '<span class="label label-danger">Unread</span>';
                break;
        }
    }

}
