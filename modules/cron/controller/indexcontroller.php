<?php

namespace Cron\Controller;

use Cron\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;
use THCFrame\Router\Model\RedirectModel;
use THCFrame\Filesystem\FileManager;
use THCFrame\Registry\Registry;

/**
 * 
 */
class IndexController extends Controller
{

    /**
     * Remove old files from folder.
     * 
     * @param type $path
     * @param type $days
     */
    private function _removeOldFiles($path, $days = 7)
    {
        $fm = new FileManager();

        if (!is_dir($path)) {
            mkdir($path, 0755, true);

            return;
        }

        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (is_file($path . $file) && filectime($path . $file) < (time() - ($days * 24 * 60 * 60))) {
                    if (!preg_match('#.*\.gz$#i', $file)) {
                        $fm->gzCompressFile($path . $file);
                        unlink($path . $file);
                    } else {
                        unlink($path . $file);
                    }
                }
            }
        }
    }

    /**
     * @param type $dir
     *
     * @return type
     */
    private function _folderSize($dir)
    {
        $count_size = 0;
        $count = 0;
        $dir_array = scandir($dir);
        foreach ($dir_array as $key => $filename) {
            if ($filename != '..' && $filename != '.') {
                if (is_dir($dir . '/' . $filename)) {
                    $new_foldersize = $this->_folderSize($dir . '/' . $filename);
                    $count_size = $count_size + $new_foldersize;
                } elseif (is_file($dir . '/' . $filename)) {
                    $count_size = $count_size + filesize($dir . '/' . $filename);
                    $count+=1;
                }
            }
        }

        return $count_size;
    }

    /**
     * Reconnect to the database.
     */
    private function _resertConnections()
    {
        $config = Registry::get('configuration');
        Registry::get('database')->disconnectAll();

        $database = new \THCFrame\Database\Database();
        $connectors = $database->initialize($config);
        Registry::set('database', $connectors);

        unset($config);
        unset($database);
        unset($connectors);
    }
    
    /**
     * Generate sitemap.xml by cron.
     * 
     * @before _cron
     */
    public function generateSitemap()
    {
        $this->_disableView();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

        $xmlEnd = '</urlset>';

        $host = RequestMethods::server('HTTP_HOST');

        try {
            $pageContent = \App\Model\PageContentModel::all(array('active = ?' => true));
            $redirects = RedirectModel::all(array('module = ?' => 'app'));
            $news = \App\Model\NewsModel::all(array('active = ?' => true, 'approved = ?' => 1), array('urlKey'));
            $actions = \App\Model\ActionModel::all(array('active = ?' => true, 'approved = ?' => 1), array('urlKey'));
            $reports = \App\Model\ReportModel::all(array('active = ?' => true, 'approved = ?' => 1), array('urlKey'));

            $redirectArr = array();
            if (null !== $redirects) {
                foreach ($redirects as $redirect) {
                    $redirectArr[$redirect->getToPath()] = $redirect->getFromPath();
                }
            }

            $articlesXml = '';
            $pageContentXml = "<url><loc>http://{$host}</loc></url>" . PHP_EOL
                    . "<url><loc>http://{$host}/akce</loc></url>"
                    . "<url><loc>http://{$host}/probehle-akce</loc></url>"
                    . "<url><loc>http://{$host}/archiv-akci</loc></url>"
                    . "<url><loc>http://{$host}/archiv-novinek</loc></url>"
                    . "<url><loc>http://{$host}/archiv-reportazi</loc></url>"
                    . "<url><loc>http://{$host}/reportaze</loc></url>"
                    . "<url><loc>http://{$host}/novinky</loc></url>"
                    . "<url><loc>http://{$host}/galerie</loc></url>"
                    . "<url><loc>http://{$host}/bazar</loc></url>" . PHP_EOL;

            $linkCounter = 10;

            if (null !== $pageContent) {
                foreach ($pageContent as $content) {
                    $pageUrl = '/page/' . $content->getUrlKey();
                    if (array_key_exists($pageUrl, $redirectArr)) {
                        $pageUrl = $redirectArr[$pageUrl];
                    }
                    $pageContentXml .= "<url><loc>http://{$host}{$pageUrl}</loc></url>" . PHP_EOL;
                    $linkCounter+=1;
                }
            }

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

            if (null !== $reports) {
                foreach ($reports as $report) {
                    $articlesXml .= "<url><loc>http://{$host}/reportaze/r/{$report->getUrlKey()}</loc></url>" . PHP_EOL;
                    $linkCounter+=1;
                }
            }

            @file_put_contents('./sitemap.xml', $xml . $pageContentXml . $articlesXml . $xmlEnd);
            $this->_resertConnections();
            Event::fire('cron.log', array('success', 'Links count: ' . $linkCounter));
        } catch (\Exception $ex) {
            $this->_resertConnections();
            Event::fire('cron.log', array('fail', 'Error while creating sitemap file: ' . $ex->getMessage()));

            $message = $ex->getMessage() . PHP_EOL . $ex->getTraceAsString();
            $this->_sendEmail('Error while creating sitemap file: ' . $message, 'ERROR: Cron generateSitemap', null, 'cron@hastrman.cz');
        }
    }

    /**
     * Cron check database size and application disk space usage.
     * 
     * @before _cron
     */
    public function systemCheck()
    {
        $this->_disableView();

        $connHandler = Registry::get('database');
        $dbIdents = $connHandler->getIdentifications();
        foreach ($dbIdents as $id) {
            $db = $connHandler->get($id);
            $size = $db->getDatabaseSize();
            if ($size > 45) {
                $body = sprintf('Database %s is growing large. Current size is %s MB', $db->getSchema(), $size);
                $this->_sendEmail($body, 'WARNING: System chcek', null, 'cron@hastrman.cz');
            }
        }

        $applicationFolderSize = $this->_folderSize(APP_PATH);
        $applicationFolderSizeMb = $applicationFolderSize / (1024 * 1024);
        if ($applicationFolderSizeMb > 600) {
            $body = sprintf('Application folder is growing large. Current size is %s MB', $applicationFolderSizeMb);
            $this->_sendEmail($body, 'WARNING: System chcek', null, 'cron@hastrman.cz');
        }
    }

    /**
     * Run File hash scan
     * 
     * @before _cron
     */
    public function filehashscan()
    {
        $this->_disableView();
        $scanner = new \THCFrame\Security\FileHashScanner\Scanner();

        $scanner->scan();
        Event::fire('cron.log', array('success', 'File hash checked'));
    }

}
