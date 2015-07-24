<?php

namespace Admin\Etc;

use THCFrame\Module\Module;

/**
 * Class for module specific settings
 */
class ModuleConfig extends Module
{

    /**
     * @read
     */
    protected $_moduleName = 'Admin';

    /**
     * @read
     */
    protected $_observerClass = 'Admin\Etc\ModuleObserver';

    /**
     * @read
     * @var array 
     */
    protected $_routes = array(
        array(
            'pattern' => '/admin/login',
            'module' => 'admin',
            'controller' => 'user',
            'action' => 'login',
        ),
        array(
            'pattern' => '/admin/logout',
            'module' => 'admin',
            'controller' => 'user',
            'action' => 'logout',
        ),
        array(
            'pattern' => '/admin/email/loadtemplate/:id/:lang',
            'module' => 'admin',
            'controller' => 'email',
            'action' => 'loadtemplate',
            'args' => array(':id', ':lang')
        )
    );

}
