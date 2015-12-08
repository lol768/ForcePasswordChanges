<?php

namespace ForcePasswordChanges\Routing;

use XenForo_Route_Interface;
use Zend_Controller_Request_Http;
use XenForo_Router;
use XenForo_RouteMatch;
use XenForo_Link;

class RoutesAdmin implements XenForo_Route_Interface {

    /**
     * Handles routing
     * @param $routePath
     * @param Zend_Controller_Request_Http $request
     * @param XenForo_Router $router
     * @return XenForo_RouteMatch
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router) {
        return $router->getRouteMatch('ForcePasswordChanges\ControllerAdmin\ForcePasswordChanges', "forcePasswordChanges");
    }
}
