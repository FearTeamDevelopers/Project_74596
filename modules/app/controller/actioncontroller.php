<?php

namespace App\Controller;

use App\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Registry\Registry;
use THCFrame\Events\Events as Event;


/**
 * 
 */
class ActionController extends Controller
{

    /**
     * Check if are set specific metadata or leave their default values
     */
    private function _checkMetaData($layoutView, \App\Model\ActionModel $object)
    {
        $uri = RequestMethods::server('REQUEST_URI');

        if ($object->getMetaTitle() != '') {
            $layoutView->set('metatitle', 'Akce - '.$object->getMetaTitle());
        }

        if ($object->getMetaDescription() != '') {
            $layoutView->set('metadescription', $object->getMetaDescription());
        }

        $canonical = 'http://' . $this->getServerHost() . '/akce/r/' . $object->getUrlKey();

        $layoutView->set('canonical', $canonical)
                ->set('article', 1)
                ->set('articlecreated', $object->getCreated())
                ->set('articlemodified', $object->getModified())
                ->set('metaogurl', "http://{$this->getServerHost()}{$uri}")
                ->set('metaogtype', 'article');
    }

    /**
     * Get list of actions
     * 
     * @param int $page
     */
    public function index($page = 1)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $articlesPerPage = $this->getConfig()->actions_per_page;

        if ($page <= 0) {
            $page = 1;
        }

        if ($page == 1) {
            $canonical = 'http://' . $this->getServerHost() . '/akce';
        } else {
            $canonical = 'http://' . $this->getServerHost() . '/akce/p/' . $page;
        }

        $content = $this->getCache()->get('akce-' . $page);

        if (null !== $content) {
            $actions = $content;
        } else {
            $actions = \App\Model\ActionModel::fetchActiveWithLimit($articlesPerPage, $page);

            $this->getCache()->set('akce-' . $page, $actions);
        }

        $actionCount = \App\Model\ActionModel::count(
                        array('active = ?' => true,
                            'approved = ?' => 1,
                            'archive = ?' => false,
                            'startDate >= ?' => date('Y-m-d', time()))
        );

        $actionsPageCount = ceil($actionCount / $articlesPerPage);

        $this->_pagerMetaLinks($actionsPageCount, $page, '/akce/p/');

        $view->set('actions', $actions)
                ->set('pagerpathprefix', '/akce')
                ->set('currentpage', $page)
                ->set('pagecount', $actionsPageCount);

        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'Hastrman - Akce');
    }

    /**
     * Show action detail
     * 
     * @param string $urlKey
     */
    public function detail($urlKey)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $action = \App\Model\ActionModel::fetchByUrlKey($urlKey);

        if ($action === null) {
            self::redirect('/nenalezeno');
        }

        $attendance = \App\Model\AttendanceModel::fetchUsersByActionId($action->getId());
        
        $this->_checkMetaData($layoutView, $action);
        $view->set('action', $action)
                ->set('comment', null)
                ->set('attendance', $attendance);
        
        if (RequestMethods::post('submitAddComment')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/akce/r/'.$action->getId());
            }
            
            $comment = new \App\Model\CommentModel(array(
                'userId' => $this->getUser()->getId(),
                'resourceId' => $action->getId(),
                'replyTo' => RequestMethods::post('replyTo', 0),
                'type' => \App\Model\CommentModel::RESOURCE_ACTION,
                'body' => RequestMethods::post('text')
            ));
            
            if ($comment->validate()) {
                $id = $comment->save();

                $this->getCache()->invalidate();
                
                Event::fire('app.log', array('success', 'Comment id: ' . $id. ' from user: '.$this->getUser()->getId()));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/akce/r/'.$action->getId());
            } else {
                Event::fire('app.log', array('fail', 'Errors: '.  json_encode($comment->getErrors())));
                $view->set('errors', $comment->getErrors())
                    ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                    ->set('comment', $comment);
            }
        }
    }

    /**
     * Show archivated actions
     * 
     * @param type $page
     */
    public function archive($page = 1)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $articlesPerPage = $this->getConfig()->actions_per_page;

        if ($page <= 0) {
            $page = 1;
        }

        if ($page == 1) {
            $canonical = 'http://' . $this->getServerHost() . '/archivakci';
        } else {
            $canonical = 'http://' . $this->getServerHost() . '/archivakci/p/' . $page;
        }

        $actions = \App\Model\ActionModel::fetchArchivatedWithLimit($articlesPerPage, $page);

        $actionCount = \App\Model\ActionModel::count(
                        array('active = ?' => true,
                            'approved = ?' => 1,
                            'archive = ?' => true)
        );

        $actionsPageCount = ceil($actionCount / $articlesPerPage);

        $this->_pagerMetaLinks($actionsPageCount, $page, '/archivakci/p/');

        $view->set('actions', $actions)
                ->set('pagerpathprefix', '/archivakci')
                ->set('currentpage', $page)
                ->set('pagecount', $actionsPageCount);

        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'Hastrman - Akce - Archiv');
    }

    /**
     * Show old but not archivated actions
     * 
     * @param type $page
     */
    public function oldActions($page = 1)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $articlesPerPage = $this->getConfig()->actions_per_page;

        if ($page <= 0) {
            $page = 1;
        }

        if ($page == 1) {
            $canonical = 'http://' . $this->getServerHost() . '/probehleakce';
        } else {
            $canonical = 'http://' . $this->getServerHost() . '/probehleakce/p/' . $page;
        }

        $actions = \App\Model\ActionModel::fetchOldWithLimit($articlesPerPage, $page);

        $actionCount = \App\Model\ActionModel::count(
                        array('active = ?' => true,
                            'approved = ?' => 1,
                            'archive = ?' => false,
                            'startDate <= ?' => date('Y-m-d', time()))
        );

        $actionsPageCount = ceil($actionCount / $articlesPerPage);

        $this->_pagerMetaLinks($actionsPageCount, $page, '/probehleakce/p/');

        $view->set('actions', $actions)
                ->set('pagerpathprefix', '/probehleakce')
                ->set('currentpage', $page)
                ->set('pagecount', $actionsPageCount);

        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'Hastrman - Akce - Proběhlé');
    }

    /**
     * Preview of action created in administration but not saved into db
     * 
     * @before _secured, _participant
     */
    public function preview()
    {
        $view = $this->getActionView();
        $session = Registry::get('session');

        $action = $session->get('actionPreview');

        if (null === $action) {
            $this->_willRenderActionView = false;
            $view->warningMessage($this->lang('NOT_FOUND'));
            self::redirect('/admin/action/');
        }

        $act = RequestMethods::get('action');

        $view->set('action', $action)
                ->set('act', $act);
    }

    /**
     * 
     * @param type $actionId
     * @param type $type
     * @before _secured, _member
     */
    public function attendance($actionId, $type)
    {
        $this->_disableView();
        
        if($type != \App\Model\AttendanceModel::ACCEPT ||
                $type != \App\Model\AttendanceModel::REJECT ||
                $type != \App\Model\AttendanceModel::MAYBE){
            echo $this->lang('COMMON_FAIL');
            exit;
        }
        
        $action = \App\Model\ActionModel::first(array('id = ?' => (int)$actionId));
        
        if (NULL === $action) {
            echo $this->lang('NOT_FOUND');
        }else{
            $attendance = new \App\Model\AttendanceModel(array(
                'userId' => $this->getUser()->getId(),
                'resourceId' => $action->getId(),
                'type' => (int)$type,
                'comment' => RequestMethods::post('attcomment')
            ));
            
            if($attendance->validate()){
                $attendance->save();
                $this->getCache()->invalidate();

                Event::fire('app.log', array('success', 'Attendance - '.$type.' - action '.$action->getId().' by user: '.$this->getUser()->getId()));
                echo 'success';
            } else {
                Event::fire('app.log', array('fail', 'Errors: '.  json_encode($attendance->getErrors())));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }
    
}
