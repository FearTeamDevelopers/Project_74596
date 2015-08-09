<?php

namespace App\Controller;

use App\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Registry\Registry;
use THCFrame\Events\Events as Event;

/**
 * 
 */
class NewsController extends Controller
{

    /**
     * Check if are set specific metadata or leave their default values
     */
    private function _checkMetaData($layoutView, \App\Model\NewsModel $object)
    {
        $uri = RequestMethods::server('REQUEST_URI');

        if ($object->getMetaTitle() != '') {
            $layoutView->set('metatitle', 'Novinky - '.$object->getMetaTitle());
        }

        if ($object->getMetaDescription() != '') {
            $layoutView->set('metadescription', $object->getMetaDescription());
        }

        $canonical = 'http://' . $this->getServerHost() . '/novinky/r/' . $object->getUrlKey();

        $layoutView->set('canonical', $canonical)
                ->set('article', 1)
                ->set('articlecreated', $object->getCreated())
                ->set('articlemodified', $object->getModified())
                ->set('metaogurl', "http://{$this->getServerHost()}{$uri}")
                ->set('metaogtype', 'article');
    }

    /**
     * Get list of news
     * 
     * @param int $page
     */
    public function index($page = 1)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $articlesPerPage = $this->getConfig()->news_per_page;

        if($page <= 0){
            $page = 1;
        }
        
        if ($page == 1) {
            $canonical = 'http://' . $this->getServerHost() . '/novinky';
        } else {
            $canonical = 'http://' . $this->getServerHost() . '/novinky/p/' . $page;
        }
        
        $content = $this->getCache()->get('news-' . $page);
        
        if (null !== $content) {
            $news = $content;
        } else {
            $news = \App\Model\NewsModel::fetchActiveWithLimit($articlesPerPage, $page);

            $this->getCache()->set('news-' . $page, $news);
        }

        $newsCount = \App\Model\NewsModel::count(
                        array('active = ?' => true,
                            'archive = ?' => false,
                            'approved = ?' => 1)
        );
        $newsPageCount = ceil($newsCount / $articlesPerPage);

        $this->_pagerMetaLinks($newsPageCount, $page, '/novinky/p/');

        $view->set('news', $news)
                ->set('currentpage', $page)
                ->set('pagerpathprefix', '/novinky')
                ->set('pagecount', $newsPageCount);

        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'TJ Sokol - Novinky');
    }

    /**
     * Get list of archivated news
     * 
     * @param int $page
     */
    public function archive($page = 1)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $articlesPerPage = $this->getConfig()->news_per_page;

        if($page <= 0){
            $page = 1;
        }
        
        if ($page == 1) {
            $canonical = 'http://' . $this->getServerHost() . '/archivnovinek';
        } else {
            $canonical = 'http://' . $this->getServerHost() . '/archivnovinek/p/' . $page;
        }
        
        $content = $this->getCache()->get('news-arch-' . $page);
        
        if (null !== $content) {
            $news = $content;
        } else {
            $news = \App\Model\NewsModel::fetchArchivatedWithLimit($articlesPerPage, $page);

            $this->getCache()->set('news-arch-' . $page, $news);
        }

        $newsCount = \App\Model\NewsModel::count(
                        array('active = ?' => true,
                            'archive = ?' => true,
                            'approved = ?' => 1)
        );
        $newsPageCount = ceil($newsCount / $articlesPerPage);

        $this->_pagerMetaLinks($newsPageCount, $page, '/archivnovinek/p/');

        $view->set('news', $news)
                ->set('currentpage', $page)
                ->set('pagerpathprefix', '/archivnovinek')
                ->set('pagecount', $newsPageCount);

        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'TJ Sokol - Novinky - Archiv');
    }
    
    /**
     * Show news detail
     * 
     * @param string $urlKey
     */
    public function detail($urlKey)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();
        
        $news = \App\Model\NewsModel::fetchByUrlKey($urlKey);
        
        if($news === null){
            self::redirect('/nenalezeno');
        }
        
        $comments = \App\Model\CommentModel::fetchCommentsByResourceAndType($news->getId(), \App\Model\CommentModel::RESOURCE_NEWS);
        
        $this->_checkMetaData($layoutView, $news);
        $view->set('news', $news)
                ->set('newcomment', null)
                ->set('comments', $comments);
        
        if (RequestMethods::post('submitAddComment')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/novinky/r/'.$news->getId());
            }
            
            $comment = new \App\Model\CommentModel(array(
                'userId' => $this->getUser()->getId(),
                'resourceId' => $news->getId(),
                'replyTo' => RequestMethods::post('replyTo', 0),
                'type' => \App\Model\CommentModel::RESOURCE_NEWS,
                'body' => RequestMethods::post('text')
            ));
            
            if ($comment->validate()) {
                $id = $comment->save();

                $this->getCache()->invalidate();
                
                Event::fire('app.log', array('success', 'Comment id: ' . $id. ' from user: '.$this->getUser()->getId()));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/novinky/r/'.$news->getId());
            } else {
                Event::fire('app.log', array('fail', 'Errors: '.  json_encode($comment->getErrors())));
                $view->set('errors', $comment->getErrors())
                    ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                    ->set('newcomment', $comment);
            }
        }
    }
    
    /**
     * Preview of news created in administration but not saved into db
     * 
     * @before _secured, _participant
     */
    public function preview()
    {
        $view = $this->getActionView();
        $session = Registry::get('session');
        
        $news = $session->get('newsPreview');
        
        if(null === $news){
            $this->_willRenderActionView = false;
            $view->warningMessage($this->lang('NOT_FOUND'));
            self::redirect('/admin/news/');
        }
        
        $act = RequestMethods::get('action');
        
        $view->set('news', $news)
            ->set('act', $act);
    }
}
