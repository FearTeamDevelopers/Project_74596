<?php

namespace Admin\Controller;

use Admin\Etc\Controller;

/**
 * 
 */
class IndexController extends Controller
{

    /**
     * Get some basic info for dashboard
     * 
     * @before _secured, _participant
     */
    public function index()
    {
        $view = $this->getActionView();

        $imessages = \Admin\Model\ImessageModel::fetchActive();
        $latestNews = \App\Model\NewsModel::fetchWithLimit(10);
        $latestActions = \App\Model\ActionModel::fetchWithLimit(10);
        $latestComments = \App\Model\CommentModel::fetchWithLimit(10);
        $latestUsers = \App\Model\UserModel::fetchLates();
        
        $view->set('latestnews', $latestNews)
                ->set('latestusers', $latestUsers)
                ->set('latestcomments', $latestComments)
                ->set('latestactions', $latestActions)
                ->set('imessages', $imessages);
    }

}
