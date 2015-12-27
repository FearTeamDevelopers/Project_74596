<?php

namespace App\Etc;

use THCFrame\Events\Events as Event;
use THCFrame\Controller\Controller as BaseController;
use THCFrame\Registry\Registry;
use THCFrame\Core\StringMethods;

/**
 * Module specific controller class extending framework controller class.
 */
class Controller extends BaseController
{

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

        $metaData = $this->getCache()->get('global_meta_data');

        if (null !== $metaData) {
            $metaData = $metaData;
        } else {
            $metaData = array(
                'metadescription' => $this->getConfig()->meta_description,
                'metarobots' => $this->getConfig()->meta_robots,
                'metatitle' => $this->getConfig()->meta_title,
                'metaogurl' => $this->getConfig()->meta_og_url,
                'metaogtype' => $this->getConfig()->meta_og_type,
                'metaogimage' => $this->getConfig()->meta_og_image,
                'metaogsitename' => $this->getConfig()->meta_og_site_name,
            );

            $this->getCache()->set('global_meta_data', $metaData);
        }

        $this->getLayoutView()
                ->set('metatitle', $metaData['metatitle'])
                ->set('metarobots', $metaData['metarobots'])
                ->set('metadescription', $metaData['metadescription'])
                ->set('metaogurl', $metaData['metaogurl'])
                ->set('metaogtype', $metaData['metaogtype'])
                ->set('metaogimage', $metaData['metaogimage'])
                ->set('metaogsitename', $metaData['metaogsitename']);
    }

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
     * @param type $pageCount
     * @param type $page
     * @param type $path
     */
    protected function _pagerMetaLinks($pageCount, $page, $path)
    {
        if ($pageCount > 1) {
            $prevPage = $page - 1;
            $nextPage = $page + 1;

            if ($nextPage > $pageCount) {
                $nextPage = 0;
            }

            $this->getLayoutView()
                    ->set('pagedprev', $prevPage)
                    ->set('pagedprevlink', $path . $prevPage)
                    ->set('pagednext', $nextPage)
                    ->set('pagednextlink', $path . $nextPage);
        }
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
     * @param type $body
     * @param type $subject
     * @param type $sendTo
     * @param type $sendFrom
     *
     * @return bool
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
                $message->setFrom('info@hastrman.cz');
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
     * @protected
     */
    public function _secured()
    {
        $user = $this->getSecurity()->getUser();

        if (!$user) {
            $this->_willRenderActionView = false;
            $this->_willRenderLayoutView = false;
            self::redirect('/prihlasit');
        }

        //1h inactivity till logout
        if (time() - $this->getSession()->get('lastActive') < 3600) {
            $this->getSession()->set('lastActive', time());
        } else {
            $view = $this->getActionView();

            $view->infoMessage('Byl jste odhlášen z důvodu dlouhé neaktivity');
            $this->getSecurity()->logout();
            self::redirect('/');
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

    /**
     * 
     */
    public function render()
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        if ($view) {
            $view->set('authUser', $this->getSecurity()->getUser())
                    ->set('deviceType', $this->getDeviceType())
                    ->set('env', ENV)
                    ->set('isMember', $this->isMember())
                    ->set('isParticipant', $this->isParticipant())
                    ->set('submstoken', $this->_mutliSubmissionProtectionToken())
                    ->set('token', $this->getSecurity()->getCsrf()->getToken());
        }

        if ($layoutView) {
            $layoutView->set('authUser', $this->getSecurity()->getUser())
                    ->set('deviceType', $this->getDeviceType())
                    ->set('env', ENV)
                    ->set('isMember', $this->isMember())
                    ->set('isParticipant', $this->isParticipant())
                    ->set('submstoken', $this->_mutliSubmissionProtectionToken())
                    ->set('token', $this->getSecurity()->getCsrf()->getToken());
        }

        parent::render();
    }

}
