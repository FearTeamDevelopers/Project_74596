<?php

namespace Cron\Etc;

use THCFrame\Module\Module;

/**
 * Class for module specific settings.
 */
class ModuleConfig extends Module
{
    /**
     * @read
     */
    protected $_moduleName = 'Cron';

    /**
     * @read
     */
    protected $_observerClass = 'Cron\Etc\ModuleObserver';

    /**
     * @read
     *
     * @var array
     */
    protected $_routes = array(
        array(
            'pattern' => '/c/generatesitemap',
            'module' => 'cron',
            'controller' => 'index',
            'action' => 'generatesitemap',
        ),
        array(
            'pattern' => '/c/dbbackup',
            'module' => 'cron',
            'controller' => 'backup',
            'action' => 'dailydatabasebackup',
        ),
        array(
            'pattern' => '/c/monthdbbackup',
            'module' => 'cron',
            'controller' => 'backup',
            'action' => 'monthlydatabasebackup',
        ),
        array(
            'pattern' => '/c/clonedb',
            'module' => 'cron',
            'controller' => 'backup',
            'action' => 'databaseprodtotest',
        ),
        array(
            'pattern' => '/c/systemcheck',
            'module' => 'cron',
            'controller' => 'index',
            'action' => 'systemcheck',
        ),
        array(
            'pattern' => '/c/filehashscan',
            'module' => 'cron',
            'controller' => 'index',
            'action' => 'filehashscan',
        ),
        array(
            'pattern' => '/c/adexpirationcheck',
            'module' => 'cron',
            'controller' => 'advertisement',
            'action' => 'checkadexpirations',
        ),
        array(
            'pattern' => '/c/archivateactions',
            'module' => 'cron',
            'controller' => 'archive',
            'action' => 'archivateactions',
        ),
        array(
            'pattern' => '/c/archivatenews',
            'module' => 'cron',
            'controller' => 'archive',
            'action' => 'archivatenews',
        ),
        array(
            'pattern' => '/c/archivatereports',
            'module' => 'cron',
            'controller' => 'archive',
            'action' => 'archivatereports',
        ),
    );
}
