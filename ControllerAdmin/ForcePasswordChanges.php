<?php

namespace ForcePasswordChanges\ControllerAdmin;

use XenForo_ControllerResponse_Redirect;
use XenForo_DataWriter;
use XenForo_Db;
use XenForo_Input;
use XenForo_Link;
use XenForo_Visitor;

class ForcePasswordChanges extends \XenForo_ControllerAdmin_Abstract {

    protected function _preDispatchFirst($action) {
        if (XenForo_Visitor::getInstance()->hasAdminPermission("user")) {
            throw $this->getNoPermissionResponseException();
        }
    }

    public function actionForcePasswordChanges() {
        if (!$this->_request->isPost()) {
            return $this->responseView('ForcePasswordChanges\\ViewAdmin\\ForcePasswordChanges', 'fpc_force_changes', []);
        } else {
            $data = $this->_input->filter([
                "user_ids" => XenForo_Input::STRING,
                "change_reason" => XenForo_Input::STRING
            ]);
            return $this->handleAddPendingChanges($data);
        }
    }

    private function handleAddPendingChanges($data) {
        XenForo_Db::beginTransaction();
        $changeReason = $data['change_reason'];
        foreach (explode("\n", $data['user_ids']) as $userId) {
            $dw = XenForo_DataWriter::create('\ForcePasswordChanges\DataWriter\PendingPasswordChange');
            $dw->bulkSet(["user_id" => $userId, "change_reason" => $changeReason, "token" => bin2hex(openssl_random_pseudo_bytes(8))]);
            $dw->save();
        }
        XenForo_Db::commit();
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('force-password-changes'), "Saved!"
        );
    }

}
