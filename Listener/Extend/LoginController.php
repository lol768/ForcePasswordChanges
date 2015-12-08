<?php
namespace ForcePasswordChanges\Listener\Extend;


class LoginController {
    public static function callback($class, array &$extend) {
        /** add our class to the list of those overriding @see \XenForo_ControllerPublic_Login */
        $extend[] = 'ForcePasswordChanges\ControllerPublic\Login';
    }
} 
