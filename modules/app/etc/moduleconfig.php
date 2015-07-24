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
            'pattern' => '/hledat',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'search'
        ),
        array(
            'pattern' => '/admin',
            'module' => 'admin',
            'controller' => 'index',
            'action' => 'index'
        ),
        array(
            'pattern' => '/bazar/hledat',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'search'
        ),
        array(
            'pattern' => '/bazar/filtr',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'filter'
        ),
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
            'pattern' => '/bazar/smazat/:uniquekey',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'ajaxdelete',
            'args' => ':uniquekey'
        ),
        array(
            'pattern' => '/bazar/odstranit/:uniquekey',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'delete',
            'args' => ':uniquekey'
        ),
        array(
            'pattern' => '/bazar/upravit/:uniquekey',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'edit',
            'args' => ':uniquekey'
        ),
        array(
            'pattern' => '/bazar/smazatfoto/:imageid',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'deleteadimage',
            'args' => ':imageid'
        ),
        array(
            'pattern' => '/bazar/prodlouzit/:uniquekey',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'sendavailabilityextendrequest',
            'args' => ':uniquekey'
        ),
        array(
            'pattern' => '/bazar/p/:page',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'index',
            'args' => ':page'
        ),
        array(
            'pattern' => '/bazar/r/:uniquekey',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'detail',
            'args' => ':uniquekey'
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
            'pattern' => '/reportaze/p/:page',
            'module' => 'app',
            'controller' => 'report',
            'action' => 'index',
            'args' => ':page'
        ),
        array(
            'pattern' => '/reportaze/r/:urlkey',
            'module' => 'app',
            'controller' => 'report',
            'action' => 'detail',
            'args' => ':urlkey'
        ),
        array(
            'pattern' => '/archivreportazi/p/:page',
            'module' => 'app',
            'controller' => 'report',
            'action' => 'archive',
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
            'pattern' => '/bazar/filtr/p/:page',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'filter',
            'args' => ':page'
        ),
        array(
            'pattern' => '/bazar/hledat/p/:page',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'search',
            'args' => ':page'
        ),
        array(
            'pattern' => '/bazar/moje-inzeraty/p/:page',
            'module' => 'app',
            'controller' => 'advertisement',
            'action' => 'listbyuser',
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
