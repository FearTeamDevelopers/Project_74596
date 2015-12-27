<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;
use THCFrame\Router\Model\RedirectModel;
use THCFrame\Core\Core;

/**
 * 
 */
class RedirectController extends Controller
{
    /**
     * Get list of all redirects.
     * 
     * @before _secured, _superadmin
     */
    public function index()
    {
        $view = $this->getActionView();
        $redirects = RedirectModel::all();
        $view->set('redirects', $redirects);
    }

    /**
     * Create new redirect.
     * 
     * @before _secured, _superadmin
     */
    public function add()
    {
        $view = $this->getActionView();
        $modules = Core::getModuleNames();

        $view->set('modules', $modules);

        if (RequestMethods::post('submitAddRedirect')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/redirect/');
            }

            $redirect = new RedirectModel(array(
                'module' => RequestMethods::post('module'),
                'fromPath' => RequestMethods::post('fromurl'),
                'toPath' => RequestMethods::post('tourl'),
            ));

            if ($redirect->validate()) {
                $id = $redirect->save();
                $this->getCache()->invalidate();

                Event::fire('admin.log', array('success', 'Redirect id: '.$id));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/admin/redirect/');
            } else {
                Event::fire('admin.log', array('fail', 'Errors: '.json_encode($redirect->getErrors())));
                $view->set('errors', $redirect->getErrors())
                        ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                        ->set('redirect', $redirect);
            }
        }
    }

    /**
     * Edit existing redirect.
     * 
     * @before _secured, _superadmin
     *
     * @param int $id redirect id
     */
    public function edit($id)
    {
        $view = $this->getActionView();

        $redirect = RedirectModel::first(array('id = ?' => (int) $id));

        if (null === $redirect) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/redirect/');
        }

        $modules = Core::getModuleNames();
        $view->set('redirect', $redirect)
                ->set('modules', $modules);

        if (RequestMethods::post('submitEditRedirect')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/redirect/');
            }

            $redirect->module = RequestMethods::post('module');
            $redirect->fromPath = RequestMethods::post('fromurl');
            $redirect->toPath = RequestMethods::post('tourl');

            if ($redirect->validate()) {
                $redirect->save();
                $this->getCache()->invalidate();

                Event::fire('admin.log', array('success', 'Redirect id: '.$id));
                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/admin/redirect/');
            } else {
                Event::fire('admin.log', array('fail', 'Redirect id: '.$id,
                    'Errors: '.json_encode($redirect->getErrors()), ));
                $view->set('errors', $redirect->getErrors());
            }
        }
    }

    /**
     * Delete existing redirect.
     * 
     * @before _secured, _superadmin
     *
     * @param int $id redirect id
     */
    public function delete($id)
    {
        $this->_disableView();

        $redirect = RedirectModel::first(
                        array('id = ?' => (int) $id), array('id')
        );

        if (null === $redirect) {
            echo $this->lang('NOT_FOUND');
        } else {
            if ($redirect->delete()) {
                $this->getCache()->invalidate();
                Event::fire('admin.log', array('success', 'Redirect id: '.$id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'Redirect id: '.$id));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }
}
