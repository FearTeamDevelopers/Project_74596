<?php

namespace Admin\Controller;

use Admin\Etc\Controller;

/**
 * 
 */
class FeedbackController extends Controller
{

    /**
     * @before _secured, _admin
     */
    public function index()
    {
        $view = $this->getActionView();
        
        $feedbacks = \App\Model\FeedbackModel::all(array(), array('*'), array('created' => 'desc'), 150);
        
        $view->set('feedbacks', $feedbacks);
    }

}