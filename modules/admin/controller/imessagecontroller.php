<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;

/**
 * 
 */
class ImessageController extends Controller
{
    /**
     * @before _secured, _superadmin
     */
    public function index()
    {
        $view = $this->getActionView();

        $imessages = \Admin\Model\ImessageModel::fetchAll();

        $view->set('imessages', $imessages);
    }

    /**
     * @before _secured, _superadmin
     */
    public function add()
    {
        $view = $this->getActionView();

        $view->set('imessage', null);

        if (RequestMethods::post('submitAddImessage')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/imessage/');
            }

            $imessage = new \Admin\Model\ImessageModel(array(
                'userId' => $this->getUser()->getId(),
                'messageType' => RequestMethods::post('mtype'),
                'userAlias' => $this->getUser()->getWholeName(),
                'title' => RequestMethods::post('title'),
                'body' => RequestMethods::post('text'),
                'displayFrom' => RequestMethods::post('dfrom'),
                'displayTo' => RequestMethods::post('dto'),
            ));

            if ($imessage->validate()) {
                $id = $imessage->save();

                Event::fire('admin.log', array('success', 'Imessage id: '.$id));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/admin/imessage/');
            } else {
                Event::fire('admin.log', array('fail', 'Errors: '.json_encode($imessage->getErrors())));
                $view->set('errors', $imessage->getErrors())
                        ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                        ->set('imessage', $imessage);
            }
        }
    }

    /**
     * @before _secured, _superadmin
     *
     * @param type $id
     */
    public function edit($id)
    {
        $view = $this->getActionView();

        $imessage = \Admin\Model\ImessageModel::first(array('id = ?' => (int) $id));

        if (null === $imessage) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->willRenderActionView = false;
            self::redirect('/admin/imessage/');
        }

        $view->set('imessage', $imessage);

        if (RequestMethods::post('submitEditImessage')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/imessage/');
            }

            $imessage->messageType = RequestMethods::post('mtype');
            $imessage->title = RequestMethods::post('title');
            $imessage->body = RequestMethods::post('text');
            $imessage->displayFrom = RequestMethods::post('dfrom');
            $imessage->displayTo = RequestMethods::post('dto');
            $imessage->active = RequestMethods::post('active');

            if ($imessage->validate()) {
                $imessage->save();

                Event::fire('admin.log', array('success', 'Imessage id: '.$id));
                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/admin/imessage/');
            } else {
                Event::fire('admin.log', array('fail', 'Imessage id: '.$id,
                    'Errors: '.json_encode($imessage->getErrors()), ));
                $view->set('errors', $imessage->getErrors());
            }
        }
    }

    /**
     * @before _secured, _superadmin
     *
     * @param type $id
     */
    public function delete($id)
    {
        $this->_disableView();

        $imessage = \Admin\Model\ImessageModel::first(array('id = ?' => $id));

        if (null === $imessage) {
            echo $this->lang('NOT_FOUND');
        } else {
            if ($imessage->delete()) {
                Event::fire('admin.log', array('success', 'Imessage id: '.$id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'Imessage id: '.$id));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }
}
