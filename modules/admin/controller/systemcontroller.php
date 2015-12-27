<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Database\Mysqldump;
use THCFrame\Events\Events as Event;
use THCFrame\Configuration\Model\ConfigModel;
use THCFrame\Profiler\Profiler;
use THCFrame\Router\Model\RedirectModel;
use THCFrame\Filesystem\LineCounter;
use THCFrame\Core\Core;

/**
 * 
 */
class SystemController extends Controller
{

    /**
     * @before _secured, _admin
     */
    public function index()
    {
        
    }

    /**
     * Ability to clear cache from administration.
     * 
     * @before _secured, _admin
     */
    public function clearCache()
    {
        $view = $this->getActionView();

        if (RequestMethods::post('clearCache')) {
            Event::fire('admin.log', array('success'));
            $this->getCache()->clearCache();

            $view->successMessage($this->lang('SYSTEM_DELETE_CACHE'));
            self::redirect('/admin/system/');
        }
    }

    /**
     * Create db bakcup.
     * 
     * @before _secured, _admin
     */
    public function createDatabaseBackup()
    {
        $view = $this->getActionView();
        $dump = new Mysqldump();

        try {
            if ($dump->create()) {
                $view->successMessage($this->lang('SYSTEM_DB_BACKUP'));
                Event::fire('admin.log', array('success', 'Database backup'));
            } else {
                $view->errorMessage($this->lang('SYSTEM_DB_BACKUP_FAIL'));
                Event::fire('admin.log', array('fail', 'Database backup'));
            }
        } catch (\THCFrame\Database\Exception\Mysqldump $ex) {
            $view->errorMessage($ex->getMessage());
            Event::fire('admin.log', array('fail', 'Database backup',
                'Error: ' . $ex->getMessage(),));
        }

        self::redirect('/admin/system/');
    }

    /**
     * Get admin log.
     * 
     * @before _secured, _admin
     */
    public function showLog()
    {
        $view = $this->getActionView();
        $log = \Admin\Model\AdminLogModel::all(array(), array('*'), array('created' => 'DESC'), 250);
        $view->set('adminlog', $log);
    }

    /**
     * 
     * @param type $id
     * @before _secured, _admin
     */
    public function showLogDetail($id)
    {
        $this->_disableView();

        $log = \Admin\Model\AdminLogModel::first(array('id = ?' => (int) $id));

        if (!empty($log)) {
            $params = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
            }, $log->getParams());

            $str = 'Akce: <br/><strong>' . $log->getModule() . '/' . $log->getController() . '/' . $log->getAction() . '</strong><br/><br/>';
            $str .= 'Referer: <br/><strong>' . $log->getHttpreferer() . '</strong><br/><br/>';
            $str .= 'Parametry: <br/><strong>' . $params . '</strong>';
            echo $str;
            exit;
        } else {
            echo $this->lang('NOT_FOUND');
            exit;
        }
    }

    /**
     * Edit application settings.
     * 
     * @before _secured, _admin
     */
    public function settings()
    {
        $view = $this->getActionView();
        $config = ConfigModel::all(array(), array('*'), array('title' => 'ASC'));
        $view->set('config', $config);

        if (RequestMethods::post('submitEditSet')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/');
            }
            $errors = array();

            foreach ($config as $conf) {
                $oldVal = $conf->getValue();
                $conf->value = RequestMethods::post($conf->getXkey());

                if ($conf->validate()) {
                    Event::fire('admin.log', array('success', $conf->getXkey() . ': ' . $oldVal . ' - ' . $conf->getValue()));
                    $conf->save();
                } else {
                    Event::fire('admin.log', array('fail', $conf->getXkey() . ': ' . json_encode($conf->getErrors())));
                    $error = $conf->getErrors();
                    $errors[$conf->xkey] = array_shift($error);
                }
            }

            if (empty($errors)) {
                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/admin/system/');
            } else {
                $view->set('errors', $errors);
            }
        }
    }

    /**
     * Get profiler result.
     * 
     * @before _secured
     */
    public function showProfiler()
    {
        $this->_disableView();

        echo Profiler::display();
    }

    /**
     * Generate sitemap.xml.
     * 
     * @before _secured, _admin
     */
    public function generateSitemap()
    {
        $view = $this->getActionView();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

        $xmlEnd = '</urlset>';

        $host = RequestMethods::server('HTTP_HOST');

        $pageContent = \App\Model\PageContentModel::all(array('active = ?' => true));
        $redirects = RedirectModel::all(array('module = ?' => 'app'));
        $news = \App\Model\NewsModel::all(array('active = ?' => true, 'approved = ?' => 1), array('urlKey'));
        $reports = \App\Model\ReportModel::all(array('active = ?' => true, 'approved = ?' => 1), array('urlKey'));
        $actions = \App\Model\ActionModel::all(array('active = ?' => true, 'approved = ?' => 1), array('urlKey'));

        $redirectArr = array();
        if (null !== $redirects) {
            foreach ($redirects as $redirect) {
                $redirectArr[$redirect->getToPath()] = $redirect->getFromPath();
            }
        }

        $articlesXml = '';
        $pageContentXml = "<url><loc>http://{$host}</loc></url>" . PHP_EOL
                . "<url><loc>http://{$host}/akce</loc></url>"
                . "<url><loc>http://{$host}/novinky</loc></url>"
                . "<url><loc>http://{$host}/galerie</loc></url>" . PHP_EOL;

        $linkCounter = 10;

        if (null !== $news) {
            foreach ($news as $_news) {
                $articlesXml .= "<url><loc>http://{$host}/novinky/r/{$_news->getUrlKey()}</loc></url>" . PHP_EOL;
                $linkCounter+=1;
            }
        }

        if (null !== $actions) {
            foreach ($actions as $action) {
                $articlesXml .= "<url><loc>http://{$host}/akce/r/{$action->getUrlKey()}</loc></url>" . PHP_EOL;
                $linkCounter+=1;
            }
        }

        file_put_contents('./sitemap.xml', $xml . $pageContentXml . $articlesXml . $xmlEnd);

        Event::fire('admin.log', array('success', 'Links count: ' . $linkCounter));
        $view->successMessage('Soubor sitemap.xml byl aktualizován');
        self::redirect('/admin/system/');
    }

    /**
     * @before _secured, _superadmin
     */
    public function linecounter()
    {
        if (ENV !== 'dev') {
            exit;
        }

        $view = $this->getActionView();

        $counter = new LineCounter();
        $totalLines = $counter->countLines(APP_PATH);
        $fileCounter = $counter->getFileCounter();

        $view->set('totallines', $totalLines)
                ->set('filecounter', $fileCounter);
    }

    /**
     * Generate basic model classes
     * 
     * @before _secured, _superadmin
     */
    public function generator($dbIdent = 'main')
    {
        $this->_disableView();
        $view = $this->getActionView();

        try {
            $generator = new \THCFrame\Model\Generator(array('dbIdent' => $dbIdent));
            $generator->createModels();

            Event::fire('admin.log', array('success', 'Generate model classes'));
            $view->successMessage('New models were generated');
            self::redirect('/admin/system/');
        } catch (\Exception $ex) {
            Event::fire('admin.log', array('fail', 'An error occured while creating model classes: ' . $ex->getMessage()));
            $view->errorMessage('An error occured while creating model classes: ' . $ex->getMessage());
            self::redirect('/admin/system/');
        }
    }

    /**
     * Update database schema based on basic model classes
     * 
     * @before _secured, _superadmin
     */
    public function sync($type = 1)
    {
        //set_time_limit(0);

        $this->_disableView();
        $view = $this->getActionView();

        $models = array(
            '\App\Model\Basic\BasicActionModel',
            '\App\Model\Basic\BasicAttendanceModel',
            '\App\Model\Basic\BasicCommentModel',
            '\App\Model\Basic\BasicGalleryModel',
            '\App\Model\Basic\BasicNewsModel',
            '\App\Model\Basic\BasicPhotoModel',
            '\Admin\Model\Basic\BasicActionhistoryModel',
            '\Admin\Model\Basic\BasicAdminlogModel',
            '\Admin\Model\Basic\BasicConceptModel',
            '\Admin\Model\Basic\BasicEmailModel',
            '\Admin\Model\Basic\BasicImessageModel',
            '\Admin\Model\Basic\BasicNewshistoryModel',
            '\THCFrame\Configuration\Model\ConfigModel',
            '\THCFrame\Router\Model\RedirectModel',
            '\THCFrame\Security\Model\AuthtokenModel',
            '\THCFrame\Security\Model\FhsBaselineModel',
            '\THCFrame\Security\Model\FhsHistoryModel',
            '\THCFrame\Security\Model\FhsScannedModel',
        );

        $db = \THCFrame\Registry\Registry::get('database')->get();
        $error = false;

        $dump = new Mysqldump();
        $dump->create();

        foreach ($models as $model) {
            $m = new $model();

            if ($type == 1) {
                if (!$db->sync($m, false, 'alter', false)) {
                    $errMsg = 'An error occured while executing db sync for model: ' . $model . '. Check error log';
                    $error = true;

                    Event::fire('admin.log', array('fail', $errMsg));
                    Core::getLogger()->critical('{error}', array('error' => $errMsg));
                }
            } elseif ($type == 2) {
                if (!$db->sync($m, true, 'alter', false)) {
                    $errMsg = 'An error occured while executing db sync for model: ' . $model . '. Check error log';
                    $error = true;

                    Event::fire('admin.log', array('fail', $errMsg));
                    Core::getLogger()->critical('{error}', array('error' => $errMsg));
                }
            }

            unset($m);
        }

        if ($error === true) {
            $view->errorMessage('An error occured while executing db sync. Check error log');
            self::redirect('/admin/system/');
        } else {
            Event::fire('admin.log', array('success', 'Model -> DB sync'));
            $view->successMessage('Model -> DB sync done');
            self::redirect('/admin/system/');
        }
    }

}
