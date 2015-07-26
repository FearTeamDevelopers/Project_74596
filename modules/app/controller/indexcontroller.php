<?php

namespace App\Controller;

use App\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Model\Model;
use THCFrame\Request\Request;

/**
 * 
 */
class IndexController extends Controller
{

    /**
     * Check if are set specific metadata or leave their default values
     */
    private function _checkMetaData($layoutView, Model $object)
    {
        $uri = RequestMethods::server('REQUEST_URI');

        if ($object->getMetaTitle() != '') {
            $layoutView->set('metatitle', $object->getMetaTitle());
        }

        if ($object->getMetaDescription() != '') {
            $layoutView->set('metadescription', $object->getMetaDescription());
        }

        $canonical = "http://{$this->getServerHost()}{$uri}";
        
        $layoutView->set('canonical', $canonical)
                ->set('metaogurl', "http://{$this->getServerHost()}{$uri}")
                ->set('metaogtype', 'website');
    }

    /**
     * Landing page
     */
    public function index()
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();
        $canonical = 'http://' . $this->getServerHost();

        $cachedNews = $this->getCache()->get('index-news');
        
        if(null !== $cachedNews){
            $news = $cachedNews;
            unset($cachedNews);
        }else{
            $news = \App\Model\NewsModel::fetchActiveWithLimit(4);
            $this->getCache()->set('index-news', $news);
        }
        
        $cachedActions = $this->getCache()->get('index-actions');
        
        if(null !== $cachedActions){
            $actions = $cachedActions;
            unset($cachedActions);
        }else{
            $actions = \App\Model\ActionModel::fetchActiveWithLimit(6);
            $this->getCache()->set('index-actions', $actions);
        }

        $view->set('news', $news)
                ->set('actions', $actions);
        
        $layoutView->set('includecarousel', 1)
                ->set('canonical', $canonical);
    }

    /**
     * Default method for content loading
     */
    public function loadContent($urlKey)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $content = $this->getCache()->get('content_' . $urlKey);

        if (null !== $content) {
            $content = $content;
        } else {
            $content = \App\Model\PageContentModel::fetchByUrlKey($urlKey);

            if ($content === null) {
                self::redirect('/nenalezeno');
            }
            
            $this->getCache()->set('content_' . $urlKey, $content);
        }
        
        $this->_checkMetaData($layoutView, $content);
        
        $view->set('content', $content);
    }

    /**
     * Custom 404 page
     */
    public function notFound()
    {
        $canonical = 'http://' . $this->getServerHost().'/nenalezeno';
        
        $this->getLayoutView()
                ->set('canonical', $canonical)
                ->set('metatitle', 'TJ Sokol - Stránka nenalezena');
    }

    /**
     * Search in application
     */
    public function search($page = 1)
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();
        $articlesPerPage = $this->getConfig()->search_results_per_page;
        
        if($page <= 0){
            $page = 1;
        }
        
        $canonical = 'http://' . $this->getServerHost() . '/hledat';
        $requestUrl = 'http://'.$this->getServerHost().'/dosearch/'.$page;
        $parameters = array('str' => RequestMethods::get('str'));
        
        $request = new Request();
        $response = $request->request('post', $requestUrl, $parameters);
        $urls = json_decode($response, true);
        
        if (null !== $urls) {
            $articleCount = array_shift($urls);

            $searchPageCount = ceil($articleCount['totalCount'] / $articlesPerPage);

            $this->_pagerMetaLinks($searchPageCount, $page, '/hledat/p/');

            $view->set('urls', $urls)
                    ->set('currentpage', $page)
                    ->set('pagecount', $searchPageCount)
                    ->set('pagerpathprefix', '/hledat')
                    ->set('pagerpathpostfix', '?' . http_build_query($parameters));
        }
        
        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'TJ Sokol - Hledat');
    }
}
