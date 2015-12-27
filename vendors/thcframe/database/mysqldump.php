<?php

namespace THCFrame\Database;

use THCFrame\Core\Base;
use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry;
use THCFrame\Database\Exception;
use THCFrame\Database\Connector as Connector;
use THCFrame\Filesystem\FileManager;

/**
 * Mysqldump
 */
class Mysqldump extends Base
{

    const MAXLINESIZE = 500;

    /**
     *
     * @var THCFrame\Database\ConnectionHandler
     */
    private $_connectionHandler;
    
    /**
     *
     * @var THCFrame\Filesystem\FileManager 
     */
    private $_filemanager;
    
    private $_backupFileName = null;
    private $_backupDir = null;
    private $_fileHandler = null;
    private $_settings = array();
    private $_database;
    private $_backupFiles = array();
    private $_defaultSettings = array(
        'include-tables' => array(),
        'exclude-tables' => array(),
        'exclude-tables-reqex' => array(),
        'no-data' => false,
        'only-data' => false,
        'add-drop-table' => true,
        'single-transaction' => true,
        'lock-tables' => false,
        'add-locks' => true,
        'disable-foreign-keys-check' => true,
        'extended-insert' => true,
        'write-comments' => true,
        'use-file-compression' => true
    );

    /**
     * Object constructor
     * 
     * @param type $settings
     */
    public function __construct($settings = array())
    {
        parent::__construct();

        $this->_connectionHandler = Registry::get('database');

        $this->_filemanager = new FileManager();
        $defaultDir = APP_PATH . '/temp/db/';

        if (!is_dir($defaultDir)) {
            $this->_filemanager->mkdir($defaultDir);
        }

        $this->_backupDir = $defaultDir;

        $this->_prepareSettings($settings);
    }

    /**
     * 
     */
    public function __destruct()
    {
        $this->_connectionHandler->disconnectAll();
    }

    /**
     * 
     * @param type $settings
     */
    private function _prepareSettings(array $settings)
    {
        $dbIdents = $this->_connectionHandler->getIdentifications();

        if (!empty($dbIdents)) {
            foreach ($dbIdents as $id) {
                if (!empty($settings[$id])) {
                    $this->_settings[$id] = array_replace_recursive($this->_defaultSettings, $settings[$id]);
                } else {
                    $this->_settings[$id] = $this->_defaultSettings;
                }
            }
        }
    }

    /**
     * 
     * @param Connector $dbc
     * @param type $dbid
     * @return array
     */
    private function _getTables(Connector $dbc, $dbid)
    {
        $sqlResult = $dbc->execute('SHOW TABLES');
        $tables = array();

        while ($row = $sqlResult->fetch_array(MYSQLI_ASSOC)) {
            if (empty($this->_settings[$dbid]['include-tables']) ||
                    (!empty($this->_settings[$dbid]['include-tables']) &&
                    in_array($row['Tables_in_' . $dbc->getSchema()], $this->_settings[$dbid]['include-tables'], true))) {
                array_push($tables, $row['Tables_in_' . $dbc->getSchema()]);
            }
        }

        return $tables;
    }

    /**
     * 
     * @param Connector $dbc
     * @param type $dbid
     * @param type $table
     * @return boolean
     */
    private function _getTableStructure(Connector $dbc, $dbid, $table)
    {
        $sqlResult = $dbc->execute("SHOW CREATE TABLE `{$table}`");

        while ($row = $sqlResult->fetch_array(MYSQLI_ASSOC)) {
            if (true === $this->_settings[$dbid]['only-data']) {
                return true;
            }

            if (isset($row['Create Table'])) {
                if (true === $this->_settings[$dbid]['write-comments']) {
                    $this->_write(
                            '-- -----------------------------------------------------' . PHP_EOL .
                            "-- Table structure for table `{$table}` --" . PHP_EOL);
                }

                if ($this->_settings[$dbid]['add-drop-table']) {
                    $this->_write("DROP TABLE IF EXISTS `{$table}`;" . PHP_EOL);
                }

                $this->_write($row['Create Table'] . ';' . PHP_EOL);
                return true;
            }
        }

        return false;
    }

    /**
     * 
     * @param Connector $dbc
     * @param type $dbid
     * @param type $tablename
     * @return type
     */
    private function _getTableValues(Connector $dbc, $dbid, $tablename)
    {
        if (true === $this->_settings[$dbid]['write-comments']) {
            $this->_write('--' . PHP_EOL .
                    "-- Dumping data for table `{$tablename}` --" . PHP_EOL);
        }

        $dbSetting = $this->_settings[$dbid];

        if ($dbSetting['single-transaction']) {
            //$dbc->query('SET GLOBAL TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $dbc->beginTransaction();
        }

        if ($dbSetting['lock-tables']) {
            $dbc->execute("LOCK TABLES `{$tablename}` READ LOCAL");
        }

        if ($dbSetting['add-locks']) {
            $this->_write("LOCK TABLES `{$tablename}` WRITE;" . PHP_EOL);
        }

        $onlyOnce = true;
        $lineSize = 0;
        $sqlResult = $dbc->execute("SELECT * FROM `{$tablename}`");

        while ($row = $sqlResult->fetch_array(MYSQLI_ASSOC)) {
            $vals = array();
            foreach ($row as $val) {
                $vals[] = is_null($val) ? 'NULL' : addslashes($val);
            }

            if ($onlyOnce || !$dbSetting['extended-insert']) {
                $lineSize += $this->_write(html_entity_decode(
                                "INSERT INTO `{$tablename}` VALUES ('" . implode("', '", $vals) . "')", ENT_QUOTES, 'UTF-8'));
                $onlyOnce = false;
            } else {
                $lineSize += $this->_write(html_entity_decode(",('" . implode("', '", $vals) . "')", ENT_QUOTES, 'UTF-8'));
            }

            if (($lineSize > self::MAXLINESIZE) || !$dbSetting['extended-insert']) {
                $onlyOnce = true;
                $lineSize = $this->_write(';' . PHP_EOL);
            }
        }

        if (!$onlyOnce) {
            $this->_write(';' . PHP_EOL);
        }
        if ($dbSetting['add-locks']) {
            $this->_write('UNLOCK TABLES;' . PHP_EOL);
        }
        if ($dbSetting['single-transaction']) {
            $dbc->commitTransaction();
        }
        if ($dbSetting['lock-tables']) {
            $dbc->execute('UNLOCK TABLES');
        }

        unset($dbSetting);

        return;
    }

    /**
     * Returns header for dump file
     * 
     * @param Connector $dbc
     * @param type $dbid
     * @return string
     */
    private function _createHeader(Connector $dbc, $dbid)
    {
        $header = '';

        if (true === $this->_settings[$dbid]['write-comments']) {
            $header .= '-- mysqldump-php SQL Dump' . PHP_EOL .
                    '--' . PHP_EOL .
                    "-- Host: {$dbc->getHost()}" . PHP_EOL .
                    '-- Generation Time: ' . date('r') . PHP_EOL .
                    '--' . PHP_EOL .
                    "-- Database: `{$dbc->getSchema()}`" . PHP_EOL .
                    '--' . PHP_EOL;
        }

        if ($this->_settings[$dbid]['disable-foreign-keys-check']) {
            $header .= 'SET FOREIGN_KEY_CHECKS=0;' . PHP_EOL;
        }

        return $header;
    }

    /**
     * Returns footer for dump file
     * 
     * @param type $dbid
     * @return string
     */
    private function _createFooter($dbid)
    {
        $footer = '';
        if ($this->_settings[$dbid]['disable-foreign-keys-check']) {
            $footer .= 'SET FOREIGN_KEY_CHECKS=1;' . PHP_EOL;
        }

        return $footer;
    }

    /**
     * Open file
     * 
     * @param string $filename
     * @return boolean
     */
    private function _open($filename)
    {
        $this->_fileHandler = fopen($filename, 'wb');

        if (false === $this->_fileHandler) {
            return false;
        }
        return true;
    }

    /**
     * Write data into file
     * 
     * @param string $str
     * @return type
     * @throws \Exception
     */
    private function _write($str)
    {
        $bytesWritten = 0;
        if (false === ($bytesWritten = fwrite($this->_fileHandler, $str))) {
            throw new Exception\Mysqldump('Writting to file failed!', 4);
        }
        return $bytesWritten;
    }

    /**
     * Close file
     * 
     * @return type
     */
    private function _close()
    {
        return fclose($this->_fileHandler);
    }

    /**
     * Main private method
     * Creates file and write database dump into it
     * 
     * @param Connector $db     connector instance
     * @param string    $id     database identification
     * @throws Exception\Mysqldump
     */
    private function _writeData(Connector $db, $id)
    {

        if (null === $this->_backupFileName) {
            $filename = $this->_backupDir . $db->getSchema() . '_' . date('Y-m-d') . '.sql';
        } else {
            $filename = $this->_backupDir . $this->_backupFileName;
        }

        if (!$this->_open($filename)) {
            throw new Exception\Mysqldump(sprintf('Output file %s is not writable', $filename), 2);
        }

        Event::fire('framework.mysqldump.create.before', array($filename));

        $this->_write($this->_createHeader($db, $id));
        $tables = $this->_getTables($db, $id);

        if (!empty($tables)) {
            foreach ($tables as $table) {
                if (in_array($table, $this->_settings[$id]['exclude-tables'], true)) {
                    continue;
                }

                foreach ($this->_settings[$id]['exclude-tables-reqex'] as $regex) {
                    if (mb_ereg_match($regex, $table)) {
                        continue 2;
                    }
                }

                $is_table = $this->_getTableStructure($db, $id, $table);
                if (true === $is_table && false === $this->_settings[$id]['no-data']) {
                    $this->_getTableValues($db, $id, $table);
                }
            }
        }

        $this->_write($this->_createFooter($id));
        Event::fire('framework.mysqldump.create.after', array($filename));

        $this->_close();
        $this->_backupFiles[$id] = $filename;
    }

    /**
     * 
     * @param array $files
     */
    private function _compressBackupFiles(array $files = array())
    {
        if (!empty($files)) {
            foreach ($files as $dbid => $path) {
                if ($this->_settings[$dbid]['use-file-compression'] === true) {
                    if (file_exists($path)) {
                        $this->_filemanager->gzCompressFile($path);
                        @unlink($path);
                    }
                }
            }
        }
    }

    /**
     * Main public method
     * Create mysql database dump of all connected databases or one specific 
     * database based on parameter
     * 
     * @param string        $dbId       database identification
     * @return boolean
     * @throws Exception\Mysqldump
     */
    public function create($dbId = null)
    {
        $dbIdents = $this->_connectionHandler->getIdentifications();

        if (empty($dbIdents)) {
            throw new Exception\Mysqldump('No connected database found');
        }

        if (null !== $dbId) {
            if (in_array($dbId, $dbIdents)) {
                $db = $this->_connectionHandler->get($dbId);
                $this->_writeData($db, $dbId);

                if (!empty($this->_backupFiles)) {
                    $this->_compressBackupFiles($this->_backupFiles);
                    return true;
                } else {
                    return false;
                }
            } else {
                throw new Exception\Mysqldump(sprintf('Database with identification %s is not connected', $dbId));
            }
        } else {
            foreach ($dbIdents as $id) {
                $db = $this->_connectionHandler->get($id);
                $this->_writeData($db, $id);
                unset($db);
            }

            if (!empty($this->_backupFiles)) {
                $this->_compressBackupFiles($this->_backupFiles);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public function getDumpFile($id = null)
    {
        if (null === $id) {
            return $this->_backupFiles;
        } else {
            if (array_key_exists($id, $this->_backupFiles)) {
                return $this->_backupFiles[$id];
            } else {
                return null;
            }
        }
    }

    /**
     * 
     * @param type $name
     * @return \THCFrame\Database\Mysqldump
     */
    public function setBackupName($name)
    {
        $this->_backupFileName = $name;
        return $this;
    }

    /**
     * 
     * @param type $dir
     * @return \THCFrame\Database\Mysqldump
     */
    public function setBackupDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->_backupDir = $dir;
        return $this;
    }

    /**
     * Download database dump
     * 
     */
    public function downloadDump()
    {
        if (!empty($this->_backupFiles)) {
            foreach ($this->_backupFiles as $filename) {
                $mime = 'text/x-sql';
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
                header('Content-Length: ' . filesize($filename));
                ob_clean();
                readfile($filename);
            }
        }
        exit;
    }

}
