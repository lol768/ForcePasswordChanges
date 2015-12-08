<?php

namespace ForcePasswordChanges\ControllerPublic;

use ForcePasswordChanges\Model\PendingPasswordChange;
use XenForo_Application;
use XenForo_ControllerResponse_Redirect;
use XenForo_DataWriter;
use XenForo_DataWriter_User;
use XenForo_Input;
use XenForo_Link;
use XenForo_Phrase;
use XenForo_Visitor;

/**
 * Class Login
 * @package ForcePasswordChanges\ControllerPublic
 * @see XenForo_ControllerPublic_Login
 */
class Login extends XFCP_Login {

    /*
     * Stop logins from completing if the user needs to change their password.
     */
    public function completeLogin($userId, $remember, $redirect, array $postData = []) {
        if ($this->getPendingPasswordChangeModel()->isUserPendingPasswordChange($userId)) {
            $entry = $this->getPendingPasswordChangeModel()->getEntryByUserId($userId);
            $this->setForcedPasswordChangeSessionCheck($entry, $userId);
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('login/forced-password-change', null, array(
                    'redirect' => $redirect,
                    'remember' => $remember ? 1 : 0
                ))
            );
        }
        return parent::completeLogin($userId, $remember, $redirect, $postData);
    }

    public function actionForcedPasswordChange() {
        $user = $this->getUserForPasswordChangeCheck();

        $redirect = $this->getDynamicRedirectIfNot(XenForo_Link::buildPublicLink('login'));
        if ($user === null) {
            $this->clearForcedPasswordChangeSessionCheck();
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirect);
        }

        $userId = $user['user_id'];
        if ($this->_request->isPost()) {
            $data = $this->_input->filter(array(
                'password' => XenForo_Input::STRING,
                'password_confirm' => XenForo_Input::STRING,
                'remember' => XenForo_Input::UINT
            ));
            /** @var XenForo_DataWriter_User $userDw */
            $userDw = XenForo_DataWriter::create("XenForo_DataWriter_User");
            $auth = $this->_getUserModel()->getUserAuthenticationObjectByUserId($userId);
            if ($auth->authenticate($userId, $data['password'])) {
                return $this->responseError(new XenForo_Phrase('must_use_new_password'));
            }
            $userDw->setExistingData($userId);
            $userDw->setPassword($data['password'], $data['password_confirm']);
            $userDw->save();

            $pwChangeDw = XenForo_DataWriter::create("ForcePasswordChanges\\DataWriter\\PendingPasswordChange");
            $pwChangeDw->setExistingData($userId);
            $pwChangeDw->delete();
            return parent::completeLogin($userId, $data['remember'], $redirect);
        } else {
            $entry = $this->getPendingPasswordChangeModel()->getEntryByUserId($userId);
            return $this->responseView('ForcePasswordChanges\\ViewPublic\\ForcedChange', 'fpc_login_forced_password_change', $entry);
        }
    }

    private function setForcedPasswordChangeSessionCheck(array $entry, $userId) {
        $session = XenForo_Application::getSession();
        $session->set("fpcLoginDate", time());
        $session->set("fpcLoginUserId", $userId);
        $session->set("fpcToken", $entry['token']);
    }

    private function clearForcedPasswordChangeSessionCheck() {
        $session = XenForo_Application::getSession();
        $session->remove("fpcLoginDate");
        $session->remove("fpcLoginUserId");
        $session->remove("fpcToken");
    }

    /*
     * Very similar to how the two-step auth stuff works.
     */
    private function getUserForPasswordChangeCheck() {
        $session = XenForo_Application::getSession();
        $loginUserId = $session->get('fpcLoginUserId');

        if (XenForo_Visitor::getUserId() || !$loginUserId) {
            return null;
        }

        $fpcLoginDate = $session->get('fpcLoginDate');
        if (!$fpcLoginDate || time() - $fpcLoginDate > 900) {
            return null;
        }

        $user = $this->_getUserModel()->getFullUserById($loginUserId);
        if (!$user) {
            return null;
        }

        $token = $session->get("fpcToken");
        if ($token !== $this->getPendingPasswordChangeModel()->getEntryByUserId($loginUserId)['token']) {
            return null;
        }

        return $user;
    }

    /**
     * @return PendingPasswordChange
     */
    private function getPendingPasswordChangeModel() {
        return $this->getModelFromCache("ForcePasswordChanges\\Model\\PendingPasswordChange");
    }
}
