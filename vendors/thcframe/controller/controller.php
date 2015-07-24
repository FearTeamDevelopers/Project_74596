<?php

namespace THCFrame\Controller;

use THCFrame\Core\Base;
use THCFrame\View\View;
use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry;
use THCFrame\Controller\Exception;
use THCFrame\View\Exception as ViewException;
use THCFrame\Request\RequestMethods;

/**
 * Parent controller class
 */
class Controller extends Base
{

    /**
     * Controller name
     * 
     * @read
     * @var string
     */
    protected $_name;

    /**
     * @readwrite
     */
    protected $_parameters;

    /**
     * @readwrite
     */
    protected $_layoutView;

    /**
     * @readwrite
     */
    protected $_actionView;

    /**
     * @readwrite
     */
    protected $_willRenderLayoutView = true;

    /**
     * @readwrite
     */
    protected $_willRenderActionView = true;

    /**
     * @readwrite
     */
    protected $_defaultPath = 'modules/%s/view';

    /**
     * @readwrite
     */
    protected $_defaultLayout = 'layouts/basic';

    /**
     * @readwrite
     */
    protected $_mobileLayout;

    /**
     * @readwrite
     */
    protected $_tabletLayout;

    /**
     * @readwrite
     */
    protected $_defaultExtension = array('phtml', 'html');

    /**
     * @readwrite
     */
    protected $_defaultContentType = 'text/html';

    /**
     * 
     * @return type
     */
    protected function getName()
    {
        if (empty($this->_name)) {
            $this->_name = get_class($this);
        }
        return $this->_name;
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\Session\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf('%s method not implemented', $method));
    }

    /**
     * 
     */
    protected function _mutliSubmissionProtectionToken()
    {
        $session = Registry::get('session');
        $token = $session->get('submissionprotection');

        if ($token === null) {
            $token = md5(microtime());
            $session->set('submissionprotection', $token);
        }

        return $token;
    }

    /**
     * 
     * @return type
     */
    protected function _revalidateMutliSubmissionProtectionToken()
    {
        $session = Registry::get('session');
        $session->erase('submissionprotection');
        $token = md5(microtime());
        $session->set('submissionprotection', $token);

        return $token;
    }

    /**
     * 
     * @param type $token
     */
    protected function _checkMutliSubmissionProtectionToken()
    {
        $session = Registry::get('session');
        $sessionToken = $session->get('submissionprotection');

        $token = RequestMethods::post('submstoken');

        if ($token == $sessionToken) {
            $session->erase('submissionprotection');
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     */
    protected function _checkCSRFToken()
    {
        $security = Registry::get('security');
        
        if ($security->getCSRF()->verifyRequest()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Static function for redirects
     * 
     * @param string $url
     */
    public static function redirect($url = null)
    {
        $schema = 'http';
        $host = RequestMethods::server('HTTP_HOST');

        if (NULL === $url) {
            header("Location: {$schema}://{$host}");
            exit;
        } else {
            header("Location: {$schema}://{$host}{$url}");
            exit;
        }
    }

    /**
     * Object constructor
     * 
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        Event::fire('framework.controller.construct.before', array($this->name));

        //get resources
        $configuration = Registry::get('configuration');
        $session = Registry::get('session');
        $router = Registry::get('router');

        if (!empty($configuration->view)) {
            $this->_defaultExtension = explode(',', $configuration->view->extension);
            $this->_defaultLayout = $configuration->view->layout;
            $this->_mobileLayout = $configuration->view->mobileLayout;
            $this->_tabletLayout = $configuration->view->tabletLayout;
            $this->_defaultPath = $configuration->view->path;
        } else {
            throw new \Exception('Error in configuration file');
        }

        //collect main variables
        $module = $router->getLastRoute()->getModule();
        $controller = $router->getLastRoute()->getController();
        $action = $router->getLastRoute()->getAction();

        $deviceType = $session->get('devicetype');

        if ($deviceType == 'phone' && $this->_mobileLayout != '') {
            $defaultLayout = $this->_mobileLayout;
        } elseif ($deviceType == 'tablet' && $this->_tabletLayout != '') {
            $defaultLayout = $this->_tabletLayout;
        } else {
            $defaultLayout = $this->_defaultLayout;
        }

        $defaultPath = sprintf($this->_defaultPath, $module);

        //create view instances
        if ($this->_willRenderLayoutView) {
            foreach ($this->_defaultExtension as $ext) {
                if (file_exists(APP_PATH . "/{$defaultPath}/{$defaultLayout}.{$ext}")) {
                    $viewFile = APP_PATH . "/{$defaultPath}/{$defaultLayout}.{$ext}";
                    break;
                }
            }

            $view = new View(array(
                'file' => $viewFile
            ));

            $this->_layoutView = $view;
        }

        if ($this->_willRenderActionView) {
            foreach ($this->_defaultExtension as $ext) {
                if (file_exists(APP_PATH . "/{$defaultPath}/{$controller}/{$action}.{$ext}")) {
                    $viewFile = APP_PATH . "/{$defaultPath}/{$controller}/{$action}.{$ext}";
                    break;
                }
            }

            $view = new View(array(
                'file' => $viewFile
            ));

            $this->_actionView = $view;
        }

        Event::fire('framework.controller.construct.after', array($this->name));
    }

    /**
     * Object destruct
     */
    public function __destruct()
    {
        Event::fire('framework.controller.destruct.before', array($this->_name));

        $this->render();

        Event::fire('framework.controller.destruct.after', array($this->_name));
    }

    /**
     * Return action view
     * 
     * @return View
     */
    public function getActionView()
    {
        return $this->_actionView;
    }

    /**
     * Return layout view
     * 
     * @return View
     */
    public function getLayoutView()
    {
        return $this->_layoutView;
    }

    /**
     * Return model instance
     * 
     * @param string $model Format: module/model_name
     * @param null|array $options
     */
    public function getModel($model, $options = NULL)
    {
        list($module, $modelName) = explode('/', $model);

        if ($module == '' || $modelName == '') {
            throw new Exception\Model(sprintf('%s is not valid model name', $model));
        } else {
            $fileName = APP_PATH . strtolower("/modules/{$module}/model/{$modelName}.php");
            $className = ucfirst($module) . '_Model_' . ucfirst($modelName);

            if (file_exists($fileName)) {
                if (NULL !== $options) {
                    return new $className($options);
                } else {
                    return new $className();
                }
            }
        }
    }

    /**
     * Main render method
     * 
     * @throws View\Exception\Renderer
     */
    public function render()
    {
        Event::fire('framework.controller.render.before', array($this->_name));

        $defaultContentType = $this->_defaultContentType;
        $results = null;

        $doAction = $this->_willRenderActionView && $this->_actionView;
        $doLayout = $this->_willRenderLayoutView && $this->_layoutView;
        $profiler = \THCFrame\Profiler\Profiler::getInstance();

        try {
            if ($doAction) {
                $results = $this->_actionView->render();

                $this->_actionView
                        ->template
                        ->implementation
                        ->set('action', $results);
            }

            if ($doLayout) {
                $results = $this->_layoutView->render();
                $profiler->stop();

                //protection against clickjacking
                header('X-Frame-Options: deny');
                header("Content-type: {$defaultContentType}");
                echo $results;
            } elseif ($doAction) {
                $profiler->stop();

                //protection against clickjacking
                header('X-Frame-Options: deny');
                header("Content-type: {$defaultContentType}");
                echo $results;
            }

            $this->_willRenderLayoutView = false;
            $this->_willRenderActionView = false;
        } catch (\Exception $e) {
            throw new ViewException\Renderer('Invalid layout/template syntax');
        }

        Event::fire('framework.controller.render.after', array($this->_name));
    }

}
