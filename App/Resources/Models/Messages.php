<?php

namespace Tourpage\Models;

/**
 * Model Messages
 * @author satya
 */
class Messages extends ApplicationModel {
	
    /**
     * Initializing Model Messages
     */
    public function initialize() {
        $this->hasMany('messageId', '\Tourpage\Models\MembersMessages', 'messageId', array(
            'alias' => 'memberMessages'
        ));
		$this->hasMany('messageId', '\Tourpage\Models\VendorsMessages', 'messageId', array(
            'alias' => 'vendorMessages'
        ));
    }
	
    /**
     * Remove messages and all its referance
     * @return boolean
     */
	public function removeData() {
		//Removing messages from members_messages list
        if ($this->memberMessages) {
            $this->memberMessages->delete();
        }
		//Removing messages from vendors_messages list
        if ($this->vendorMessages) {
            $this->vendorMessages->delete();
        }
        //Removing mesages
        return $this->delete();
    }

}
