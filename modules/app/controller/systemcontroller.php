<?php

namespace App\Controller;

use App\Etc\Controller;
use THCFrame\Profiler\Profiler;
use THCFrame\Core\Core;
use THCFrame\Request\RequestMethods;

/**
 * 
 */
class SystemController extends Controller
{

    /**
     * Method called by ajax shows profiler bar at the bottom of screen.
     */
    public function showProfiler()
    {
        $this->_disableView();

        echo Profiler::display();
    }

    /**
     * Screen resolution logging.
     */
    public function logresolution()
    {
        $this->_disableView();

        $width = RequestMethods::post('scwidth');
        $height = RequestMethods::post('scheight');
        $res = $width . ' x ' . $height;

        Core::getLogger()->info('resolution', array('resolution' => $res));
    }

}
