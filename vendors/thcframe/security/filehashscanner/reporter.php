<?php

namespace THCFrame\Security\FileHashScanner;

use THCFrame\Core\Base;
use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry;

/**
 * Description of reporter
 *
 * @author Tomy
 */
class Reporter extends Base
{

    private $_acct;
    protected $_text = array();

    /**
     * @readwrite
     * @var type 
     */
    protected $_scanner;

    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_configuration = Registry::get('configuration');
        $this->_acct = $this->_configuration->security->filescan->acct;
    }

    public function addText($text)
    {
        $this->_text[] = $text;
    }

    public function dailyReport()
    {
        $report = "SuperScan Daily Report\r\n\r\n";
        $report .= implode('<br/>', $this->_text);

        $yesterday = date("Y-m-d H:i:s", mktime(date('H'), date('i'), date('s'), date('n'), date('j') - 1, date('Y')));

        $report .= "SuperScan log report for " . $this->_acct . " file changes since " . $yesterday . ":\r\n\r\n";

        $result = \THCFrame\Security\Model\FhsHistoryModel::all(array('timestamp > ?' => $yesterday, 'acct = ?' => $this->_acct));

        if (!empty($result)) {
            foreach ($result as $row) {
                $report .= $row->getTimestamp() . " =>  " . strtoupper($row->getStatus()) . " =>  " . $row->getPath() . "\r\n";
            }

            if ($this->_configuration->security->filescan->emailOutput) {
                mail($this->_configuration->system->adminemail, 'FileHash scan for ' . $this->_acct, $report);
            }
        }

        if ($this->_configuration->security->filescan->textOutput) {
            Core::getLogger()->info('{fshr}', array('fshr', print_r($report, true)));
        }
    }

    public function afterScanReport()
    {
        $report = "FileHash scan Check for {$this->_acct}\r\n\r\n";
        $report .= implode('<br/>', $this->_text);

        $countBaseline = count($this->_scanner->getBaseline());
        $countCurrent = count($this->_scanner->getCurrent());
        $report .= "$countCurrent files collected in scan.\n\r";
        if (0 == $countCurrent) {
            //	ALL files are gone!
            $report .= "\n\rThere are NO files in the specified location.\n\r";
            if (!$this->_scanner->getFirstScan()) {
                $report .= "This indicates a possible HACK ATTACK\n\rOR an incorrect path to the account's files\n\r\n\r";
            }
        }

        $countAdded = count($this->_scanner->getAdded());
        $report .= "$countAdded files ADDED to baseline.\n\r";

        $countAltered = count($this->_scanner->getAltered());
        $report .= "$countAltered ALTERED files updated.\n\r";

        $countDeleted = count($this->_scanner->getDeleted());
        $report .= "$countDeleted files DELETED from baseline.\n\r\n\r";

        $totalChanges = $countAdded + $countAltered + $countDeleted;

        if (0 == $totalChanges) {
            $report .= "File structure is unchanged since last scan.\n\rThe baseline contains $countCurrent files.";
        } else {
            $report .= "Summary:\n\rBaseline start: $countBaseline\n\rCurrent Baseline: $countCurrent\n\rChanges to baseline: $totalChanges\n\r\n\rAdded: $countAdded\n\rAltered: $countAltered\n\rDeleted: $countDeleted.";
            if (0 < $totalChanges) {
                $report .= "\n\r\n\rIf you did not makes these changes, examine your files closely\n\rfor evidence of embedded hacker code or added hacker files.\n\r(WinMerge provides excellent comparisons)";
            }

            if ($this->_configuration->security->filescan->emailOutput) {
                mail($this->_configuration->system->adminemail, 'FileHash scan for ' . $this->_acct, $report);
            }
        }

        if ($this->_configuration->security->filescan->textOutput) {
            Core::getLogger()->info('{fshr}', array('fshr', print_r($report, true)));
        }
    }

}
