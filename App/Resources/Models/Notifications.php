<?php

namespace Tourpage\Models;

/**
 * Model Notifications
 * @author satya
 */
class Notifications extends ApplicationModel {

    /**
     * Initializing Model Notifications
     */
    public function initialize() {
        $this->hasMany('memberNotificationId', '\Tourpage\Models\MembersNotifications', 'memberNotificationId', array(
            'alias' => 'memberNotifications'
        ));
    }

    /**
     * Remove notifications and all its referance
     * @return boolean
     */
    public function removeData() {
        //Removing Notifications
        return $this->delete();
    }

}
