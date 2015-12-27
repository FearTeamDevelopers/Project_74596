<?php

namespace Admin\Etc;

use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Controller\Controller as BaseController;
use THCFrame\Core\StringMethods;
use THCFrame\Request\RequestMethods;

/**
 * Module specific controller class extending framework controller class.
 */
class Controller extends BaseController
{

    /**
     * @param type $string
     *
     * @return type
     */
    protected function _createUrlKey($string)
    {
        $neutralChars = array('.', ',', '_', '(', ')', '[', ']', '|', ' ');
        $preCleaned = StringMethods::fastClean($string, $neutralChars, '-');
        $cleaned = StringMethods::fastClean($preCleaned);
        $return = mb_ereg_replace('[\-]+', '-', trim(trim($cleaned), '-'));

        return strtolower($return);
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
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        // schedule disconnect from database 
        Event::add('framework.controller.destruct.after', function ($name) {
            Registry::get('database')->disconnectAll();
        });
    }

    /**
     * @protected
     */
    public function _secured()
    {
        //This line should be present only for DEV env
        //$this->getSecurity()->forceLogin(1);

        $user = $this->getSecurity()->getUser();

        if (!$user) {
            $this->_willRenderActionView = false;
            $this->_willRenderLayoutView = false;
            self::redirect('/admin/login');
        }

        //5h inactivity till logout
        if (time() - $this->getSession()->get('lastActive') < 18000) {
            $this->getSession()->set('lastActive', time());
        } else {
            $view = $this->getActionView();

            $view->infoMessage($this->lang('LOGIN_TIMEOUT'));
            $this->getSecurity()->logout();
            self::redirect('/admin/login');
        }
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
     * @protected
     */
    public function _member()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_member') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * @return bool
     */
    protected function isMember()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_member') === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @protected
     */
    public function _participant()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_participant') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * @return bool
     */
    protected function isParticipant()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_participant') === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @protected
     */
    public function _admin()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_admin') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * @return bool
     */
    protected function isAdmin()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_admin') === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @protected
     */
    public function _superadmin()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_superadmin') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * @return bool
     */
    protected function isSuperAdmin()
    {
        if ($this->getSecurity()->getUser() && $this->getSecurity()->isGranted('role_superadmin') === true) {
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
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        if ($view) {
            $view->set('authUser', $this->getSecurity()->getUser())
                    ->set('env', ENV)
                    ->set('isMember', $this->isMember())
                    ->set('isParticipant', $this->isParticipant())
                    ->set('isAdmin', $this->isAdmin())
                    ->set('isSuperAdmin', $this->isSuperAdmin())
                    ->set('submstoken', $this->_mutliSubmissionProtectionToken())
                    ->set('token', $this->getSecurity()->getCsrf()->getToken());
        }

        if ($layoutView) {
            $layoutView->set('authUser', $this->getSecurity()->getUser())
                    ->set('env', ENV)
                    ->set('isMember', $this->isMember())
                    ->set('isParticipant', $this->isParticipant())
                    ->set('isAdmin', $this->isAdmin())
                    ->set('isSuperAdmin', $this->isSuperAdmin())
                    ->set('submstoken', $this->_mutliSubmissionProtectionToken())
                    ->set('token', $this->getSecurity()->getCsrf()->getToken());
        }

        parent::render();
    }

    /**
     * Load user from security context.
     */
    public function getUser()
    {
        return $this->getSecurity()->getUser();
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
