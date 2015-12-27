<?php

namespace Cron\Etc;

use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Controller\Controller as BaseController;
use THCFrame\Request\RequestMethods;
use THCFrame\Core\Lang;

/**
 * Module specific controller class extending framework controller class.
 */
class Controller extends BaseController
{
    /**
     * Store security context object.
     *
     * @var THCFrame\Security\Security
     * @read
     */
    protected $_security;

    /**
     * Store initialized cache object.
     *
     * @var THCFrame\Cache\Cache
     * @read
     */
    protected $_cache;

    /**
     * Store configuration.
     *
     * @var THCFrame\Configuration\Configuration
     * @read
     */
    protected $_config;

    /**
     * Store language extension.
     *
     * @var THCFrame\Core\Lang
     * @read
     */
    protected $_lang;
    
    /**
     * Store server host name.
     *
     * @var type
     * @read
     */
    protected $_serverHost;

    /**
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_security = Registry::get('security');
        $this->_cache = Registry::get('cache');
        $this->_config = Registry::get('configuration');
        $this->_lang = Lang::getInstance();
        $this->_serverHost = RequestMethods::server('HTTP_HOST');

        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = false;

        // schedule disconnect from database 
        Event::add('framework.controller.destruct.after', function ($name) {
            Registry::get('database')->disconnectAll();
        });
    }

    /**
     * Disable view, used for ajax calls.
     */
    protected function _disableView()
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = false;
        header('Content-Type: text/html; charset=utf-8');
    }

    /**
     * @protected
     */
    public function _cron()
    {
        if (!preg_match('#^Links.*#i', RequestMethods::server('HTTP_USER_AGENT')) &&
                '95.168.206.203' != RequestMethods::server('REMOTE_ADDR')) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * @return bool
     */
    protected function isCron()
    {
        if (preg_match('#^Links.*#i', RequestMethods::server('HTTP_USER_AGENT')) &&
                '95.168.206.203' == RequestMethods::server('REMOTE_ADDR')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     */
    public function render()
    {
        parent::render();
    }

    /**
     * @param type $key
     * @param type $args
     *
     * @return type
     */
    public function lang($key, $args = array())
    {
        return $this->getLang()->_get($key, $args);
    }
}
