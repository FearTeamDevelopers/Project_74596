<?php
namespace Search\Etc;

use THCFrame\Registry\Registry;
use THCFrame\Events\SubscriberInterface;
use THCFrame\Request\RequestMethods;

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
            'search.log' => 'searchLog',
            'search.log.user' => 'searchUserLog'
        );
    }
    
    /**
     * 
     * @param array $params
     */
    public function searchLog()
    {
        $params = func_get_args();
        
        $router = Registry::get('router');
        $route = $router->getLastRoute();

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

        $log = new \Search\Model\AdminLogModel(array(
            'userId' => 'searchjob',
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
     * @param array $params
     */
    public function searchUserLog()
    {
        $params = func_get_args();
        
        $router = Registry::get('router');
        $route = $router->getLastRoute();
        
        $security = Registry::get('security');
        $user = $security->getUser();
        if ($user === null) {
            $userId = 'annonymous';
        } else {
            $userId = $user->getWholeName() . ':' . $user->getId();
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

        $log = new \Search\Model\AdminLogModel(array(
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
    
}
