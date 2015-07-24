<?php

namespace Admin\Etc;

use THCFrame\Registry\Registry;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\SubscriberInterface;
use THCFrame\Security\Model\SecLogModel;

/**
 * Module specific observer class
 */
class ModuleObserver implements SubscriberInterface
{

    /**
     * 
     * @return type
     */
    public function getSubscribedEvents()
    {
        return array(
            'admin.log' => 'adminLog',
            'sec.log' => 'secLog',
        );
    }

    /**
     * 
     * @param array $params
     */
    public function adminLog()
    {
        $params = func_get_args();

        $router = Registry::get('router');
        $route = $router->getLastRoute();
        $security = Registry::get('security');
        
        if ($security->getUser() === null) {
            $userId = 'annonymous';
        } else {
            $userId = $security->getUser()->getWholeName() . ':' . $security->getUser()->getId();
        }

        $module = $route->getModule();
        $controller = $route->getController();
        $action = $route->getAction();

        if (!empty($params)) {
            $result = array_shift($params);

            $paramStr = '';
            if (!empty($params)) {
                $paramStr = join(', ', $params);
            }
        } else {
            $result = 'fail';
            $paramStr = '';
        }

        $log = new \Admin\Model\AdminLogModel(array(
            'userId' => $userId,
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'result' => $result,
            'httpreferer' => RequestMethods::getHttpReferer(),
            'params' => $paramStr
        ));

        if ($log->validate()) {
            $log->save();
        }
    }

    /**
     * 
     */
    public function secLog()
    {
        $params = func_get_args();

        $uip = RequestMethods::getClientIpAddress();
        $ubrowser = RequestMethods::getBrowser();

        $router = Registry::get('router');
        $route = $router->getLastRoute();
        $security = Registry::get('security');

        if ($security->getUser() === null) {
            $userId = 'annonymous';
        } else {
            $userId = $security->getUser()->getWholeName() . ':' . $security->getUser()->getId();
        }

        $module = $route->getModule();
        $controller = $route->getController();
        $action = $route->getAction();

        if (!empty($params)) {
            $paramStr = join(', ', $params);
        } else {
            $paramStr = '';
        }

        $log = new SecLogModel(array(
            'userId' => $userId,
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'userAgent' => $ubrowser,
            'userIp' => $uip,
            'params' => $paramStr
        ));

        if ($log->validate()) {
            $log->save();
        }
    }

}
