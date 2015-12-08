<?php

namespace ForcePasswordChanges\Model;

use XenForo_Model;

class PendingPasswordChange extends XenForo_Model {

    /**
     * @param int $userId The user's id as an integer.
     * @return array The row, with keys "user_id" and "change_reason".
     */
    public function getEntryByUserId($userId) {
        return $this->_getDb()->fetchRow('SELECT * FROM xf_fpc_pending_changes WHERE user_id = ?', $userId);
    }

    /**
     * @param int $userId The user's id as an integer.
     * @return bool True if the user needs to change their password, false otherwise.
     */
    public function isUserPendingPasswordChange($userId) {
        return $this->_getDb()->fetchOne('SELECT COUNT(user_id) FROM xf_fpc_pending_changes WHERE user_id = ?', $userId) > 0;
    }
}
