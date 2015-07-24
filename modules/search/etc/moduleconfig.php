<?php

namespace Search\Etc;

use THCFrame\Module\Module as Module;

/**
 * Class for module specific settings
 */
class ModuleConfig extends Module
{

    /**
     * @read
     */
    protected $_moduleName = 'Search';

    /**
     * @read
     */
    protected $_observerClass = 'Search\Etc\ModuleObserver';
    
    /**
     * @read
     * @var array 
     */
    protected $_routes = array(
        array(
            'pattern' => '/dosearch/:page',
            'module' => 'search',
            'controller' => 'search',
            'action' => 'dosearch',
            'args' => ':page'
        ),
        array(
            'pattern' => '/doadsearch/:page',
            'module' => 'search',
            'controller' => 'search',
            'action' => 'doadsearch',
            'args' => ':page'
        ),
        array(
            'pattern' => '/s/buildindex',
            'module' => 'search',
            'controller' => 'index',
            'action' => 'buildindex'
        ),
        array(
            'pattern' => '/s/updateindex/:model',
            'module' => 'search',
            'controller' => 'index',
            'action' => 'updateindex',
            'args' => ':model'
        )
    );

}
