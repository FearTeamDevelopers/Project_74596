<?php

namespace Admin\Etc;

use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Controller\Controller as BaseController;
use THCFrame\Core\StringMethods;
use THCFrame\Request\RequestMethods;
use THCFrame\Core\Lang;

/**
 * Module specific controller class extending framework controller class
 */
class Controller extends BaseController
{

    /**
     * Store security context object
     * @var type 
     * @read
     */
    protected $_security;

    /**
     * Store initialized cache object
     * @var type 
     * @read
     */
    protected $_cache;

    /**
     * Store configuration
     * @var type 
     * @read
     */
    protected $_config;

    /**
     * Store language extension
     * @var type 
     * @read
     */
    protected $_lang;
    
    /**
     * 
     * @param type $string
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
     * 
     * @param type $body
     * @param type $subject
     * @param type $sendTo
     * @param type $sendFrom
     * @return boolean
     */
    protected function _sendEmail($body, $subject, $sendTo = null, $sendFrom = null)
    {
        try {
            require_once APP_PATH . '/vendors/swiftmailer/swift_required.php';
            $transport = \Swift_MailTransport::newInstance();
            $mailer = \Swift_Mailer::newInstance($transport);

            $message = \Swift_Message::newInstance(null)
                    ->setSubject($subject)
                    ->setBody($body, 'text/html');

            if (null === $sendTo) {
                $message->setTo($this->getConfig()->system->adminemail);
            } else {
                $message->setTo($sendTo);
            }

            if (null === $sendFrom) {
                $message->setFrom('info@sokol.cz');
            } else {
                $message->setFrom($sendFrom);
            }

            if ($mailer->send($message)) {
                return true;
            } else {
                Event::fire('admin.log', array('fail', 'No email sent'));
                return false;
            }
        } catch (\Exception $ex) {
            Event::fire('admin.log', array('fail', 'Error while sending email: ' . $ex->getMessage()));
            return false;
        }
    }

    /**
     * Disable view, used for ajax calls
     */
    protected function _disableView()
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = false;
    }

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_security = Registry::get('security');
        $this->_cache = Registry::get('cache');
        $this->_config = Registry::get('configuration');
        $this->_lang = Lang::getInstance();
        
        $this->getLayoutView()
                ->setTitle($this->lang('TITLE_DEFAULT_ADMIN'));

        // schedule disconnect from database 
        Event::add('framework.controller.destruct.after', function($name) {
            Registry::get('database')->disconnectAll();
        });
    }

    /**
     * @protected
     */
    public function _secured()
    {
        $session = Registry::get('session');

        //This line should be present only for DEV env
        //$this->_security->forceLogin(1);

        $user = $this->_security->getUser();

        if (!$user) {
            $this->_willRenderActionView = false;
            $this->_willRenderLayoutView = false;
            self::redirect('/admin/login');
        }

        //5h inactivity till logout
        if (time() - $session->get('lastActive') < 18000) {
            $session->set('lastActive', time());
        } else {
            $view = $this->getActionView();

            $view->infoMessage($this->lang('LOGIN_TIMEOUT'));
            $this->_security->logout();
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
     * 
     * @return boolean
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
        if ($this->_security->getUser() && $this->_security->isGranted('role_member') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * 
     * @return boolean
     */
    protected function isMember()
    {
        if ($this->_security->getUser() && $this->_security->isGranted('role_member') === true) {
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
        if ($this->_security->getUser() && $this->_security->isGranted('role_participant') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * 
     * @return boolean
     */
    protected function isParticipant()
    {
        if ($this->_security->getUser() && $this->_security->isGranted('role_participant') === true) {
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
        if ($this->_security->getUser() && $this->_security->isGranted('role_admin') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * 
     * @return boolean
     */
    protected function isAdmin()
    {
        if ($this->_security->getUser() && $this->_security->isGranted('role_admin') === true) {
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
        if ($this->_security->getUser() && $this->_security->isGranted('role_superadmin') !== true) {
            throw new \THCFrame\Security\Exception\Unauthorized($this->lang('ACCESS_DENIED'));
        }
    }

    /**
     * 
     * @return boolean
     */
    protected function isSuperAdmin()
    {
        if ($this->_security->getUser() && $this->_security->isGranted('role_superadmin') === true) {
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
            $view->set('authUser', $this->_security->getUser())
                    ->set('env', ENV)
                    ->set('isMember', $this->isMember())
                    ->set('isParticipant', $this->isParticipant())
                    ->set('isAdmin', $this->isAdmin())
                    ->set('isSuperAdmin', $this->isSuperAdmin())
                    ->set('submstoken', $this->_mutliSubmissionProtectionToken())
                    ->set('token', $this->_security->getCSRF()->getToken());
        }

        if ($layoutView) {
            $layoutView->set('authUser', $this->_security->getUser())
                    ->set('env', ENV)
                    ->set('isMember', $this->isMember())
                    ->set('isParticipant', $this->isParticipant())
                    ->set('isAdmin', $this->isAdmin())
                    ->set('isSuperAdmin', $this->isSuperAdmin())
                    ->set('submstoken', $this->_mutliSubmissionProtectionToken())
                    ->set('token', $this->_security->getCSRF()->getToken());
        }

        parent::render();
    }

    /**
     * Load user from security context
     */
    public function getUser()
    {
        return $this->_security->getUser();
    }

    /**
     * 
     * @param type $key
     * @param type $args
     * @return type
     */
    public function lang($key, $args = array())
    {
        return $this->getLang()->_get($key, $args);
    }
    
}
