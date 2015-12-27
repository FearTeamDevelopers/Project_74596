<?php

namespace Cron\Controller;

use Cron\Etc\Controller;
use THCFrame\Events\Events as Event;
use THCFrame\Filesystem\FileManager;
use THCFrame\Registry\Registry;

/**
 * 
 */
class ArchiveController extends Controller
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
     * 
     */
    public function archivateActions()
    {
        $this->_disableView();

        $articles = \App\Model\ActionModel::all(array('created <= ?' => date('Y-m-d H:i:s', strtotime('-2 year')), 'archive = ?' => false), array('id', 'archive'));

        if (!empty($articles)) {
            foreach ($articles as $article) {
                $article->archive = 1;

                if ($article->update() > 0) {
                    Event::fire('cron.log', array('success', 'Archivating action id: ' . $article->getId()));
                } else {
                    Event::fire('cron.log', array('fail', 'An error occured while archivating action id: ' . $article->getId()));
                }
            }
            $this->getCache()->erase('arch');
        }
    }

    /**
     * 
     */
    public function archivateNews()
    {
        $this->_disableView();

        $articles = \App\Model\NewsModel::all(array('created <= ?' => date('Y-m-d H:i:s', strtotime('-2 year')), 'archive = ?' => false), array('id', 'archive'));

        if (!empty($articles)) {
            foreach ($articles as $article) {
                $article->archive = 1;

                if ($article->update() > 0) {
                    Event::fire('cron.log', array('success', 'Archivating news id: ' . $article->getId()));
                } else {
                    Event::fire('cron.log', array('fail', 'An error occured while archivating news id: ' . $article->getId()));
                }
            }
            $this->getCache()->erase('arch');
        }
    }

    /**
     * 
     */
    public function archivateReports()
    {
        $this->_disableView();

        $articles = \App\Model\ReportModel::all(array('created <= ?' => date('Y-m-d H:i:s', strtotime('-2 year')), 'archive = ?' => false), array('id', 'archive'));

        if (!empty($articles)) {
            foreach ($articles as $article) {
                $article->archive = 1;

                if ($article->update() > 0) {
                    Event::fire('cron.log', array('success', 'Archivating report id: ' . $article->getId()));
                } else {
                    Event::fire('cron.log', array('fail', 'An error occured while archivating report id: ' . $article->getId()));
                }
            }
        }
        $this->getCache()->erase('arch');
    }

}
