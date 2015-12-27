<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;
use THCFrame\Core\StringMethods;

/**
 * Controller for email templates management and mass email sending.
 */
class EmailController extends Controller
{
    /**
     * Check whether action unique identifier already exist or not.
     * 
     * @param string $key
     *
     * @return bool
     */
    private function _checkUrlKey($key)
    {
        $status = \App\Model\ActionModel::first(array('urlKey = ?' => $key));

        if (null === $status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Show list of email templates.
     * 
     * @before _secured, _admin
     */
    public function index()
    {
        $view = $this->getActionView();

        if ($this->isSuperAdmin()) {
            $templates = \Admin\Model\EmailModel::fetchAll();
        } else {
            $templates = \Admin\Model\EmailModel::fetchAllCommon();
        }

        $view->set('emails', $templates);
    }

    /**
     * Send mass email.
     * 
     * @before _secured, _admin
     */
    public function send()
    {
        $view = $this->getActionView();

        if ($this->isSuperAdmin()) {
            $templates = \Admin\Model\EmailModel::fetchAllActive();
        } else {
            $templates = \Admin\Model\EmailModel::fetchAllCommonActive();
        }

        $actions = \App\Model\ActionModel::fetchActiveWithLimit(0);

        $view->set('email', null)
                ->set('templates', $templates)
                ->set('actions', $actions);

        if (RequestMethods::post('submitSendEmail')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/email/send/');
            }

            $errors = array();
            $email = new \Admin\Model\EmailModel(array(
                'type' => RequestMethods::post('type'),
                'subject' => RequestMethods::post('subject'),
                'body' => StringMethods::prepareEmailText(stripslashes(RequestMethods::post('text'))),
            ));

            $email->populate();

            if (empty(RequestMethods::post('singlerecipients')) && empty(RequestMethods::post('grouprecipients')) && $email->type != 3) {
                $errors['recipientlist'] = array($this->lang('EMAIL_NO_RECIPIENTS'));
            }

            if (empty($errors) && $email->type == 1) {
                $recipients = RequestMethods::post('singlerecipients');
                $recipientsArr = explode(',', $recipients);
                array_map('trim', $recipientsArr);
                $email->setRecipients($recipientsArr);

                if ($email->send()) {
                    Event::fire('admin.log', array('success', 'Email sent to: '.$recipients));
                    $view->successMessage($this->lang('EMAIL_SEND_SUCCESS'));
                    self::redirect('/admin/email/send/');
                } else {
                    $view->errorMessage($this->lang('EMAIL_SEND_FAIL'));
                    self::redirect('/admin/email/send/');
                }
            } elseif (empty($errors) && $email->type == 2) {
                $roles = RequestMethods::post('grouprecipients');
                $users = \App\Model\UserModel::all(array('active = ?' => true, 'deleted = ?' => false, 'role in ?' => $roles), array('email'));
                $recipientsArr = array();

                foreach ($users as $user) {
                    $recipientsArr[] = $user->getEmail();
                }

                $email->setRecipients($recipientsArr);

                if ($email->send()) {
                    Event::fire('admin.log', array('success', 'Email sent to: '.implode(',', $recipientsArr)));
                    $view->successMessage($this->lang('EMAIL_SEND_SUCCESS'));
                    self::redirect('/admin/email/send/');
                } else {
                    $view->errorMessage($this->lang('EMAIL_SEND_FAIL'));
                    self::redirect('/admin/email/send/');
                }
            } elseif (empty($errors) && $email->type == 3) {
                $actionId = RequestMethods::post('actionid');
                $recipients = \App\Model\AttendanceModel::fetchUsersByActionId($actionId);
                
                if (!empty($recipients)) {
                    foreach ($recipients as $recipient) {
                        $email->setRecipient($recipient->email);
                    }
                }

                if ($email->send()) {
                    $recipientStr = $email->getRecipientsToString(',');
                    Event::fire('admin.log', array('success', 'Email sent to: '.$recipientStr));
                    $view->successMessage($this->lang('EMAIL_SEND_SUCCESS'));
                    self::redirect('/admin/email/send/');
                } else {
                    $view->errorMessage($this->lang('EMAIL_SEND_FAIL'));
                    self::redirect('/admin/email/send/');
                }
            } else {
                Event::fire('admin.log', array('fail', 'Errors: '));
                $view->set('errors', $errors)
                    ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                    ->set('email', $email);
            }
        }
    }

    /**
     * Ajax - Load template into ckeditor.
     * 
     * @before _secured, _admin
     */
    public function loadTemplate($id, $lang = 'cs')
    {
        $this->_disableView();

        if ($lang == 'en') {
            $fieldName = 'bodyEn';
        } else {
            $fieldName = 'body';
        }

        if ($this->isSuperAdmin()) {
            $template = \Admin\Model\EmailModel::fetchActiveByIdAndLang($id, $fieldName);
        } else {
            $template = \Admin\Model\EmailModel::fetchCommonActiveByIdAndLang($id, $fieldName);
        }

        echo json_encode(array('text' => stripslashes($template->$fieldName), 'subject' => $template->getSubject()));
        exit;
    }

    /**
     * Create new email template.
     * 
     * @before _secured, _admin
     */
    public function add()
    {
        $view = $this->getActionView();

        $view->set('template', null);

        if (RequestMethods::post('submitAddEmailTemplate')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/email/');
            }

            $errors = array();
            $urlKey = $urlKeyCh = $this->_createUrlKey(RequestMethods::post('title'));

            for ($i = 1; $i <= 100; $i+=1) {
                if ($this->_checkUrlKey($urlKeyCh)) {
                    break;
                } else {
                    $urlKeyCh = $urlKey.'-'.$i;
                }

                if ($i == 100) {
                    $errors['title'] = array($this->lang('ARTICLE_UNIQUE_ID'));
                    break;
                }
            }

            $emailTemplate = new \Admin\Model\EmailModel(array(
                'title' => RequestMethods::post('title'),
                'subject' => RequestMethods::post('subject'),
                'urlKey' => $urlKeyCh,
                'body' => stripslashes(RequestMethods::post('text')),
                'bodyEn' => stripslashes(RequestMethods::post('texten')),
                'type' => $this->isSuperAdmin() ? RequestMethods::post('type') : 1,
            ));

            if (empty($errors) && $emailTemplate->validate()) {
                $id = $emailTemplate->save();

                Event::fire('admin.log', array('success', 'Email template id: '.$id));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/admin/email/');
            } else {
                Event::fire('admin.log', array('fail', 'Errors: '.json_encode($errors + $emailTemplate->getErrors())));
                $view->set('errors', $errors + $emailTemplate->getErrors())
                    ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                    ->set('template', $emailTemplate);
            }
        }
    }

    /**
     * Edit exiting email template.
     * 
     * @before _secured, _admin
     */
    public function edit($id)
    {
        $view = $this->getActionView();

        $emailTemplate = \Admin\Model\EmailModel::first(array('id = ?' => (int) $id));

        if (null === $emailTemplate) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/email/');
        }
        
        if($emailTemplate->getType() == 2 && !$this->isSuperAdmin()){
            $view->warningMessage($this->lang('LOW_PERMISSIONS'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/email/');
        }

        $view->set('template', $emailTemplate);

        if (RequestMethods::post('submitEditEmailTemplate')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/email/');
            }

            $errors = array();
            $urlKey = $urlKeyCh = $this->_createUrlKey(RequestMethods::post('title'));

            if ($emailTemplate->urlKey != $urlKey && !$this->_checkUrlKey($urlKey)) {
                for ($i = 1; $i <= 100; $i+=1) {
                    if ($this->_checkUrlKey($urlKeyCh)) {
                        break;
                    } else {
                        $urlKeyCh = $urlKey . '-' . $i;
                    }

                    if ($i == 100) {
                        $errors['title'] = array($this->lang('ARTICLE_TITLE_IS_USED'));
                        break;
                    }
                }
            }

            $emailTemplate->title = RequestMethods::post('title');
            $emailTemplate->subject = RequestMethods::post('subject');
            $emailTemplate->urlKey = $urlKeyCh;
            $emailTemplate->body = stripslashes(RequestMethods::post('text'));
            $emailTemplate->bodyEn = stripslashes(RequestMethods::post('texten'));
            $emailTemplate->type = $this->isSuperAdmin() ? RequestMethods::post('type') : 1;
            $emailTemplate->active = RequestMethods::post('active');

            if (empty($errors) && $emailTemplate->validate()) {
                $emailTemplate->save();

                Event::fire('admin.log', array('success', 'Email template id: '.$id));
                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/admin/email/');
            } else {
                Event::fire('admin.log', array('fail', 'Email template id: '.$id,
                    'Errors: '.json_encode($errors + $emailTemplate->getErrors()), ));
                $view->set('errors', $errors + $emailTemplate->getErrors())
                    ->set('template', $emailTemplate);
            }
        }
    }

    /**
     * Delete existing email template.
     * 
     * @before _secured, _admin
     */
    public function delete($id)
    {
        $this->_disableView();

        $emailTemplate = \Admin\Model\EmailModel::first(array('id = ?' => (int) $id));

        if (null === $emailTemplate) {
            echo $this->lang('NOT_FOUND');
        } else {
            if($emailTemplate->getType() == 2 && !$this->isSuperAdmin()){
                echo $this->lang('LOW_PERMISSIONS');
                exit;
            }
            if ($emailTemplate->delete()) {
                Event::fire('admin.log', array('success', 'Email template id: '.$id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'Email template id: '.$id));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }
}
