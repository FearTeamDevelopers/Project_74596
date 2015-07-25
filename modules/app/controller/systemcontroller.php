<?php

namespace App\Controller;

use App\Etc\Controller;
use THCFrame\Profiler\Profiler;
use THCFrame\Core\Core;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;

/**
 * 
 */
class SystemController extends Controller
{

    /**
     * Method called by ajax shows profiler bar at the bottom of screen
     */
    public function showProfiler()
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = false;

        echo Profiler::display();
    }

    /**
     * Screen resolution logging
     */
    public function logresolution()
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = false;

        $width = RequestMethods::post('scwidth');
        $height = RequestMethods::post('scheight');
        $res = $width . ' x ' . $height;

        Core::getLogger()->log($res, FILE_APPEND, true, 'scres.log');
    }

    /**
     * Form for visitors feedback
     */
    public function feedback()
    {
        $view = $this->getActionView();
        $layoutView = $this->getLayoutView();

        $canonical = 'http://' . $this->getServerHost() . '/feedback';

        $view->set('feedback', null);
        $layoutView->set('canonical', $canonical)
                ->set('metatitle', 'Hastrman - Feedback');

        if (RequestMethods::post('submitFeedback')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/feedback');
            }
            
            $userAlias = $this->getUser() !== null ? $this->getUser()->getWholeName() : '';
            $feedback = new \App\Model\FeedbackModel(array(
                'userAlias' => $userAlias,
                'message' => RequestMethods::post('message')
            ));

            if ($feedback->validate()) {
                $id = $feedback->save();

                Event::fire('app.log', array('success', 'Feedback id: ' . $id));
                $view->successMessage('Děkujeme za Vaše nápady a návrhy');
                self::redirect('/');
            } else {
                Event::fire('app.log', array('fail', 'Errors: '.  json_encode($feedback->getErrors())));
                $view->set('feedback', $feedback)
                        ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                        ->set('errors', $feedback->getErrors());
            }
        }
    }

}
