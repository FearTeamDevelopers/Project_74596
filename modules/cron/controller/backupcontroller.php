<?php

namespace Cron\Controller;

use Cron\Etc\Controller;
use THCFrame\Database\Mysqldump;
use THCFrame\Events\Events as Event;
use THCFrame\Filesystem\FileManager;
use THCFrame\Registry\Registry;

/**
 * 
 */
class BackupController extends Controller
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
     * Reconnect to the test database.
     */
    private function _resertTestConnection()
    {
        $oldDb = new \THCFrame\Database\Database();
        $db = $oldDb->initializeDirectly(array(
            'type' => 'mysql',
            'host' => 'mysql4.ebola.cz',
            'username' => 'hastrmancz_ts',
            'password' => 'wAeol+B4V(W96H1Aot',
            'schema' => 'hastrman_004',
        ));

//        $db = $oldDb->initializeDirectly(array(
//            'type' => 'mysql',
//            'host' => 'localhost',
//            'username' => 'root',
//            'password' => '',
//            'schema' => 'hastrman_test'
//        ));

        return $db;
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
     * Create daily db backup by cron.
     * 
     * @before _cron
     */
    public function dailyDatabaseBackup()
    {
        $this->_disableView();

        $path = APP_PATH . '/temp/db/day/';
        $this->_removeOldFiles($path);

        $dump = new Mysqldump();
        $dump->setBackupDir($path);

        try {
            if ($dump->create()) {
                Event::fire('cron.log', array('success', 'Database backup'));
            } else {
                Event::fire('cron.log', array('fail', 'Database backup'));
                $this->_sendEmail('Error in mysqldump class while creating database backup', 'ERROR: Cron databaseBackup', null, 'cron@hastrman.cz');
            }
        } catch (\THCFrame\Database\Exception\Mysqldump $ex) {
            Event::fire('cron.log', array('fail', 'Database backup',
                'Error: ' . $ex->getMessage(),));

            $message = $ex->getMessage() . PHP_EOL . $ex->getTraceAsString();
            $this->_sendEmail('Error while creating database backup: ' . $message, 'ERROR: Cron databaseBackup', null, 'cron@hastrman.cz');
        }
    }

    /**
     * Create monthly db backup by cron.
     * 
     * @before _cron
     */
    public function monthlyDatabaseBackup()
    {
        $this->_disableView();

        $path = APP_PATH . '/temp/db/month/';
        $this->_removeOldFiles($path);

        $dump = new Mysqldump();
        $dump->setBackupDir($path);

        try {
            if ($dump->create()) {
                Event::fire('cron.log', array('success', 'Database backup'));
            } else {
                Event::fire('cron.log', array('fail', 'Database backup'));
                $this->_sendEmail('Error in mysqldump class while creating database backup', 'ERROR: Cron databaseBackup', null, 'cron@hastrman.cz');
            }
        } catch (\THCFrame\Database\Exception\Mysqldump $ex) {
            Event::fire('cron.log', array('fail', 'Database backup',
                'Error: ' . $ex->getMessage(),));

            $message = $ex->getMessage() . PHP_EOL . $ex->getTraceAsString();
            $this->_sendEmail('Error while creating database backup: ' . $message, 'ERROR: Cron databaseBackup', null, 'cron@hastrman.cz');
        }
    }

    /**
     * Clone production database to test.
     * 
     * @before _cron
     */
    public function databaseProdToTest()
    {
        $this->_disableView();

        $starttime = microtime(true);

        $dbDataPath = APP_PATH . '/temp/db/data/';
        $dbStructurePath = APP_PATH . '/temp/db/structure/';

        $this->_removeOldFiles($dbDataPath, 31);
        $this->_removeOldFiles($dbStructurePath, 31);

        $settingsNoData = array('main' => array(
                'no-data' => true,
                'write-comments' => false,
                'disable-foreign-keys-check' => false,
                'use-file-compression' => false,
        ));

        $settingsOnlyData = array('main' => array(
                'only-data' => true,
                'add-locks' => false,
                'disable-foreign-keys-check' => false,
                'extended-insert' => false,
                'write-comments' => false,
                'use-file-compression' => false,
        ));
        $dumpOnlyData = new Mysqldump($settingsOnlyData);
        $dumpNoData = new Mysqldump($settingsNoData);

        $dumpOnlyData->setBackupDir($dbDataPath);
        $dumpNoData->setBackupDir($dbStructurePath);

        try {
            if ($dumpNoData->create('main') && $dumpOnlyData->create('main')) {
                $db = $this->_resertTestConnection();

                $dbStructureSql = file_get_contents($dumpNoData->getDumpFile('main'));
                $sqls = explode(';', $dbStructureSql);

                $db->execute('SET FOREIGN_KEY_CHECKS=0');

                foreach ($sqls as $sql) {
                    $db->execute($sql);
                }

                $db = $this->_resertTestConnection();

                $dataSql = file_get_contents($dumpOnlyData->getDumpFile('main'));
                $dataSqlArr = explode('INSERT INTO', $dataSql);

                $db->execute('SET FOREIGN_KEY_CHECKS=0');

                if (!empty($dataSqlArr)) {
                    $i = 0;
                    foreach ($dataSqlArr as $query) {
                        if (empty($query)) {
                            continue;
                        }

                        $sql = 'INSERT INTO ' . trim($query);
                        $db->execute($sql);
                        $i += 1;

                        if ($i == 500) {
                            $db = $this->_resertTestConnection();
                            $db->execute('SET FOREIGN_KEY_CHECKS=0');
                            $i = 0;
                        }
                    }
                }

                $db->execute('SET FOREIGN_KEY_CHECKS=1');

                $time = round(microtime(true) - $starttime, 2);
                Event::fire('cron.log', array('success', sprintf('Database clone to test took %s sec', $time)));
            } else {
                Event::fire('cron.log', array('fail', 'Database clone to test'));
                $this->_sendEmail('Unknown error', 'ERROR: Cron clone production database', null, 'cron@hastrman.cz');
            }
        } catch (\THCFrame\Database\Exception\Mysqldump $ex) {
            Event::fire('cron.log', array('fail', 'Database clone to test',
                'Error: ' . $ex->getMessage(),));

            $message = $ex->getMessage() . PHP_EOL . $ex->getTraceAsString();
            $this->_sendEmail('Error: ' . $message, 'ERROR: Cron clone production database', null, 'cron@hastrman.cz');
        }
    }

}
