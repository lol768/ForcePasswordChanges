<?php
namespace ForcePasswordChanges;
use XenForo_Application;

/**
 * Class ForcePasswordChanges\Installer
 *
 * Handles addon installation and uninstallation.
 */
class Installer {

    /**
     * Install the addon.
     */
    public static function install() {
        /** @var \Zend_Db_Adapter_Abstract $db */
        $db = XenForo_Application::get('db');
        $db->query("CREATE TABLE IF NOT EXISTS xf_fpc_pending_changes (user_id INT UNSIGNED NOT NULL UNIQUE PRIMARY KEY, change_reason TEXT NOT NULL, token TEXT NOT NULL)");
    }

    /**
     * Uninstalls the addon.
     */
    public static function uninstall() {
        /** @var \Zend_Db_Adapter_Abstract $db */
        $db = XenForo_Application::get('db');
        $db->query("DROP TABLE IF EXISTS xf_fpc_pending_changes");
    }
}
