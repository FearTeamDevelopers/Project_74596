<?php

namespace THCFrame\Security\FileHashScanner;

use THCFrame\Core\Base;
use THCFrame\Registry\Registry;
use THCFrame\Security\FileHashScanner\Reporter;
use THCFrame\Core\Core;
use THCFrame\Core\StringMethods;

/**
 * Scanner class compute file hashes and compare them with previously
 * stored results to check changes in file structure
 *
 * @author Tomy
 */
class Scanner extends Base
{

    /**
     * Array for the baseline table
     * @readwrite
     * @var array 
     */
    protected $_baseline = array();

    /**
     * Array for the current file scan
     * @readwrite
     * @var array 
     */
    protected $_current = array();

    /**
     * Differences arrays
     * @readwrite
     * @var array
     */
    protected $_added = array();

    /**
     * @readwrite
     * @var type 
     */
    protected $_altered = array();

    /**
     * @readwrite
     * @var type 
     */
    protected $_deleted = array();

    /**
     * @readwrite
     * @var bool
     */
    protected $_firstScan = false;

    /**
     *
     * @var string
     */
    private $_acct;
    private $_errors = array();

    /**
     *
     * @var THCFrame\Security\FileHashScanner\Reporter 
     */
    private $_reporter;

    /**
     *
     * @var THCFrame\Configuration\Configuration 
     */
    private $_configuration;

    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_configuration = Registry::get('configuration');
        $this->_acct = $this->_configuration->security->filescan->acct;
        $this->_reporter = new Reporter(array('scanner' => $this));
    }

    public function scan()
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '256M');

        $lastScan = \THCFrame\Security\Model\FhsScannedModel::getLastScann();

        if ($lastScan !== null) {
            $this->setFirstScan(false);
        } else {
            $this->setFirstScan(true);
        }

        $start = microtime(true);

        $this->_baseline = \THCFrame\Security\Model\FhsBaselineModel::fetchBasicArray($this->_acct);

        if (!empty($this->_baseline)) {
            // Output number of baseline files for testing
            $this->_reporter->addText(count($this->_baseline) . " baseline files extracted from database.");
        } else {
            if (!$this->getFirstScan()) {
                $this->_reporter->addText("Empty baseline table!\n\rPROBABLE HACK ATTACK\n\r(ALL files are missing/deleted)!");
            }
        }

        $dir = new \RecursiveDirectoryIterator(APP_PATH);
        $iter = new \RecursiveIteratorIterator($dir);

        while ($iter->valid()) {
            // Not in Dot AND avoid banned directories
            $skip = explode(',', $this->_configuration->security->filescan->skipDir);
            $ext = explode(',', $this->_configuration->security->filescan->ext);
            $exclExt = explode(',', $this->_configuration->security->filescan->excludeExt);
            
            if (!$iter->isDot() && StringMethods::striposArray($iter->getSubPath(), $skip) === false) {
                // Select file extensions OR $ext empty AND not excluded ext
                if ((!empty($ext)) || (empty($ext) && !in_array(pathinfo($iter->key(), PATHINFO_EXTENSION), $exclExt, true))) {
                    $filePath = $iter->key();
                    // Ensure $filePath without \'s
                    $filePath = str_replace(chr(92), chr(47), $filePath);

                    // Handle addition to $this->_current array
                    $this->_current[$filePath] = array('hash' => hash_file("sha256", $filePath), 'lastMod' => date("Y-m-d H:i:s", filemtime($filePath)));

                    // IF new, file was ADDED
                    if (!array_key_exists($filePath, $this->_baseline)) {
                        $this->_added[$filePath] = array('hash' => $this->_current[$filePath]['hash'], 'lastMod' => $this->_current[$filePath]['lastMod']);

                        // INSERT added record in baseline table
                        $baselineNewRec = new \THCFrame\Security\Model\FhsBaselineModel(array(
                            'path' => $filePath,
                            'hash' => $this->_added[$filePath]['hash'],
                            'lastMod' => $this->_added[$filePath]['lastMod'],
                            'acct' => $this->_acct
                        ));

                        if ($baselineNewRec->validate()) {
                            $baselineNewRec->save();
                        } else {
                            $this->_errors[] = $baselineNewRec->getErrors();
                        }

                        // INSERT added file record in history table
                        // EXCEPT if $this->getFirstScan() (to prevent unnecessary records)
                        if (!$this->getFirstScan()) {
                            $historyNewRec = new \THCFrame\Security\Model\FhsHistoryModel(array(
                                'timestamp' => date('Y-m-d h:i:s'),
                                'status' => 'Added',
                                'path' => $filePath,
                                'hashOrig' => 'Not Applicable',
                                'hashNew' => $this->_added[$filePath]['hash'],
                                'lastMod' => $this->_added[$filePath]['lastMod'],
                                'acct' => $this->_acct
                            ));

                            if ($historyNewRec->validate()) {
                                $historyNewRec->save();
                            } else {
                                $this->_errors[] = $historyNewRec->getErrors();
                            }
                        }
                    } else {
                        // IF file was ALTERED 
                        if ($this->_baseline[$filePath]['hash'] <> $this->_current[$filePath]['hash'] || $this->_baseline[$filePath]['lastMod'] <> $this->_current[$filePath]['lastMod']) {
                            $this->_altered[$filePath] = array('hashOrig' => $this->_baseline[$filePath]['hash'], 'hashNew' => $this->_current[$filePath]['hash'], 'lastMod' => $this->_current[$filePath]['lastMod']);

                            // UPDATE altered record in baseline
                            $baselineRec = \THCFrame\Security\Model\FhsBaselineModel::first(array('path = ?' => $filePath, 'acct = ?' => $this->_acct));
                            $baselineRec->hash = $this->_altered[$filePath]['hashNew'];
                            $baselineRec->lastMod = $this->_altered[$filePath]['lastMod'];
                            $baselineRec->save();

                            // INSERT altered file info in history table
                            $historyNewRec = new \THCFrame\Security\Model\FhsHistoryModel(array(
                                'timestamp' => date('Y-m-d h:i:s'),
                                'status' => 'Altered',
                                'path' => $filePath,
                                'hashOrig' => $this->_altered[$filePath]['hashOrig'],
                                'hashNew' => $this->_altered[$filePath]['hashNew'],
                                'lastMod' => $this->_altered[$filePath]['lastMod'],
                                'acct' => $this->_acct
                            ));

                            if ($historyNewRec->validate()) {
                                $historyNewRec->save();
                            } else {
                                $this->_errors[] = $historyNewRec->getErrors();
                            }
                        }
                    }
                } // End of handling $this->_altered
            } // End of handling $this->_current record entry
            $iter->next();
            unset($baselineNewRec, $historyNewRec, $baselineRec, $filePath);
        }

        // DELETED
        $this->_deleted = array_diff_key($this->_baseline, $this->_current);
        // Next line necessary for Windows
        $this->_deleted = str_replace(chr(92), chr(47), $this->_deleted);

        foreach ($this->_deleted as $key => $value) {
            // DELETE file from baseline table
            \THCFrame\Security\Model\FhsBaselineModel::deleteAll(array('path = ?' => $key));

            // Record deletion in history table
            $historyNewRec = new \THCFrame\Security\Model\FhsHistoryModel(array(
                'timestamp' => date('Y-m-d h:i:s'),
                'status' => 'Deleted',
                'path' => $key,
                'hashOrig' => $this->_deleted[$key]['file_hash'],
                'hashNew' => 'Not Applicable',
                'lastMod' => $this->_deleted[$key]['lastMod'],
                'acct' => $this->_acct
            ));

            if ($historyNewRec->validate()) {
                $historyNewRec->save();
            } else {
                $this->_errors[] = $historyNewRec->getErrors();
            }
        }

        $elapsed = round(microtime(true) - $start, 5);
        $this->_reporter->addText("Scan executed in {$elapsed} seconds");

        $this->deleteOldRecords();

        $totalChanges = count($this->_added) + count($this->_altered) + count($this->_deleted);

        $scanned = new \THCFrame\Security\Model\FhsScannedModel(array(
            'scanned' => date('Y-m-d h:i:s'),
            'changes' => $totalChanges,
            'acct' => $this->_acct
        ));

        if ($scanned->validate()) {
            $scanned->save();
        } else {
            $this->_errors[] = $scanned->getErrors();
        }

        if (!empty($this->_errors)) {
            Core::getLogger()->error('{fshr}', array('fshr', print_r($this->_errors, true)));
        }

        $this->_reporter->afterScanReport();
    }

    protected function deleteOldRecords()
    {
        // Clean-up history table and scanned table by deleting entries over 30 days old
        \THCFrame\Security\Model\FhsHistoryModel::deleteAll(array('timestamp < ?' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)'));
        \THCFrame\Security\Model\FhsScannedModel::deleteAll(array('scanned < ?' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)'));
    }

}
