<?php

namespace ForcePasswordChanges\DataWriter;

use XenForo_DataWriter;

class PendingPasswordChange extends XenForo_DataWriter {

    protected function _getFields() {
        return array(
            'xf_fpc_pending_changes' => array(
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                ),
                'change_reason' => array(
                    'type' => self::TYPE_STRING,
                ),
                'token' => array(
                    'type' => self::TYPE_STRING,
                ),
            )
        );
    }

    protected function _getExistingData($data) {
        // Check by existing primary key ('user_id')
        if (!$id = $this->_getExistingPrimaryKey($data, 'user_id')) {
            return false;
        }
        return array('xf_fpc_pending_changes' => $this->getPendingPasswordChangeModel()->getEntryByUserId($id));
    }

    protected function _getUpdateCondition($tableName) {
        // primary
        return 'user_id = ' . $this->_db->quote($this->getExisting('user_id'));
    }

    /**
     * @return \ForcePasswordChanges\Model\PendingPasswordChange
     */
    private function getPendingPasswordChangeModel() {
        return $this->getModelFromCache('ForcePasswordChanges\Model\PendingPasswordChange');
    }
}
