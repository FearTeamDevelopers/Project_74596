<?php

namespace THCFrame\Logger;

use THCFrame\Core\Base;
use THCFrame\Logger\Exception;
use THCFrame\Logger\LoggerInterface;

/**
 * Factory allows many different kinds of configuration driver classes to be used, 
 * we need a way to share code across all driver classes.
 */
abstract class Driver extends Base implements LoggerInterface
{

    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    const SQL = 'sql';
    const CRON = 'cron';

    /**
     * @return \THCFrame\Logger\Driver
     */
    public function initialize()
    {
        return $this;
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\Session\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf('%s method not implemented', $method));
    }

}
