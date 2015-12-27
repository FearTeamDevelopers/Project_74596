<?php

namespace App\Controller;

use App\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Model\Model;

/**
 * 
 */
class IndexController extends Controller
{

    /**
     * Check if are set specific metadata or leave their default values.
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
     * Landing page.
     */
    public function index()
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();
        $canonical = 'http://' . $this->getServerHost();

        $cachedNews = $this->getCache()->get('index-news');

        if (null !== $cachedNews) {
            $news = $cachedNews;
            unset($cachedNews);
        } else {
            $news = \App\Model\NewsModel::fetchActiveWithLimit(4);
            $this->getCache()->set('index-news', $news);
        }

        $cachedActions = $this->getCache()->get('index-actions');

        if (null !== $cachedActions) {
            $actions = $cachedActions;
            unset($cachedActions);
        } else {
            $actions = \App\Model\ActionModel::fetchActiveWithLimit(6);
            $this->getCache()->set('index-actions', $actions);
        }

        $view->set('news', $news)
                ->set('actions', $actions);

        $layoutView->set('canonical', $canonical);
    }

}
