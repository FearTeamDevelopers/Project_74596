<?php

namespace App\Etc;

use THCFrame\Module\Module as Module;

/**
 * Class for module specific settings
 */
class ModuleConfig extends Module
{

    /**
     * @read
     */
    protected $_moduleName = 'App';

    /**
     * @readwrite
     */
    protected $_checkForRedirects = true;

    /**
     * @read
     */
    protected $_observerClass = 'App\Etc\ModuleObserver';

    /**
     * @read
     * @var array
     */
    protected $_routes = array(
        array(
            'pattern' => '/page/:urlkey',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'loadcontent',
            'args' => ':urlkey'
        ),
        array(
            'pattern' => '/aktivovatucet/:key',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'activateaccount',
            'args' => ':key'
        ),
        array(
            'pattern' => '/hledat/p/:page',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'search',
            'args' => ':page'
        ),
        array(
            'pattern' => '/galerie/r/:urlkey',
            'module' => 'app',
            'controller' => 'gallery',
            'action' => 'detail',
            'args' => ':urlkey'
        ),
        array(
            'pattern' => '/galerieslideshow/r/:urlkey',
            'module' => 'app',
            'controller' => 'gallery',
            'action' => 'slideshow',
            'args' => ':urlkey'
        ),
        array(
            'pattern' => '/galerie/p/:page',
            'module' => 'app',
            'controller' => 'gallery',
            'action' => 'index',
            'args' => ':page'
        ),
        array(
            'pattern' => '/akce/p/:page',
            'module' => 'app',
            'controller' => 'action',
            'action' => 'index',
            'args' => ':page'
        ),
        array(
            'pattern' => '/akce/r/:urlkey',
            'module' => 'app',
            'controller' => 'action',
            'action' => 'detail',
            'args' => ':urlkey'
        ),
        array(
            'pattern' => '/archivakci/p/:page',
            'module' => 'app',
            'controller' => 'action',
            'action' => 'archive',
            'args' => ':page'
        ),
        array(
            'pattern' => '/probehleakce/p/:page',
            'module' => 'app',
            'controller' => 'action',
            'action' => 'oldactions',
            'args' => ':page'
        ),
        array(
            'pattern' => '/novinky/p/:page',
            'module' => 'app',
            'controller' => 'news',
            'action' => 'index',
            'args' => ':page'
        ),
        array(
            'pattern' => '/novinky/r/:urlkey',
            'module' => 'app',
            'controller' => 'news',
            'action' => 'detail',
            'args' => ':urlkey'
        ),
        array(
            'pattern' => '/archivnovinek/p/:page',
            'module' => 'app',
            'controller' => 'news',
            'action' => 'archive',
            'args' => ':page'
        ),
        array(
            'pattern' => '/galerie/:urlkey/p/:page',
            'module' => 'app',
            'controller' => 'gallery',
            'action' => 'detail',
            'args' => array(':urlkey',':page')
        )
    );

}
