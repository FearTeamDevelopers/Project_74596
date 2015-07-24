<?php

namespace THCFrame\Security\Rbac;

use THCFrame\Core\Base;
use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry;
use THCFrame\Security\Exception;

class Rbac
{

    private $_permissionList = array();
    private $_roleList = array();
    private $_graph;
    private static $_instance = null;

    private function __construct()
    {
        $this->generateGraph();
    }

    private function isValidPermission($permission)
    {
        
    }

    private function isValidResource($module, $controller, $action)
    {
        
    }

    private function generateGraph()
    {
        
    }

    /**
     * 
     * @return type
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function hasPermission($permission)
    {
        /*
         * userroles
         * result = false
         * foreach userroles
         * 
         *  role getpermissions
         *  foreach permissions
         *      permission is allowed or is denied
         *      result = true or false
         */
    }

    public function checkResource($module, $controller, $action)
    {
        if ($this->isValidResource($module, $controller, $action)) {
            return true;
        }

        return false;
    }

}
