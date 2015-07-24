<?php

namespace THCFrame\Filesystem;

use THCFrame\Core\Base as Base;

/**
 * 
 */
class LineCounter extends Base
{

    /**
     * @readwrite
     * @var type 
     */
    protected $_fileCounter = array('gen' => array('commentedLines' => 0,
                                                    'functions' => 0,
                                                    'classes' => 0,
                                                    'commentBlocks' => 0,
                                                    'blankLines' => 0,
                                                    'totalFiles' => 0)
                                );

    /**
     * @read
     */
    protected $_allowedExtensions = "(html|htm|phtml|php|js|css|ini|sql|styl|jade)";

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
    }

    /**
     * 
     * @return type
     */
    public function getFileCounter()
    {
        return $this->_fileCounter;
    }

    /**
     * 
     * @param type $dir
     * @return type
     */
    public function countLines($dir)
    {
        $lineCounter = 0;

        if (empty($dir)) {
            $dirHandle = opendir(APP_PATH);
            $path = realpath(APP_PATH);
        } else {
            $dirHandle = opendir($dir);
            $path = realpath($dir);
        }

        $nextLineIsComment = false;

        if ($dirHandle) {
            while (false !== ($file = readdir($dirHandle))) {
                if (is_dir($path . "/" . $file) && ($file !== '.' && $file !== '..')) {
                    $lineCounter += $this->countLines($path . "/" . $file);
                } elseif ($file !== '.' && $file !== '..') {
                    //Check if we have a valid file 
                    $ext = pathinfo($file, PATHINFO_EXTENSION);

                    if (preg_match("/" . $this->_allowedExtensions . "$/i", $ext)) {
                        $realFile = realpath($path) . "/" . $file;
                        $fileHandle = fopen($realFile, 'r');
                        $fileArray = file($realFile);

                        //Check content of file:
                        $fac = count($fileArray);
                        for ($i = 0; $i < $fac; $i++) {
                            if ($nextLineIsComment) {
                                $this->_fileCounter['gen']['commentedLines'] ++;
                                //Look for the end of the comment block
                                if (strpos($fileArray[$i], '*/')) {
                                    $nextLineIsComment = false;
                                }
                            } else {
                                //Look for a function
                                if (strpos($fileArray[$i], 'function')) {
                                    $this->_fileCounter['gen']['functions'] ++;
                                }
                                //Look for a commented line
                                if (strpos($fileArray[$i], '//')) {
                                    $this->_fileCounter['gen']['commentedLines'] ++;
                                }
                                //Look for a class
                                if (substr(trim($fileArray[$i]), 0, 5) == 'class') {
                                    $this->_fileCounter['gen']['classes'] ++;
                                }
                                //Look for a comment block
                                if (strpos($fileArray[$i], '/*')) {
                                    $nextLineIsComment = true;
                                    $this->_fileCounter['gen']['commentedLines'] ++;
                                    $this->_fileCounter['gen']['commentBlocks'] ++;
                                }
                                //Look for a blank line
                                if (trim($fileArray[$i]) == '') {
                                    $this->_fileCounter['gen']['blankLines'] ++;
                                }
                            }
                        }
                        $lineCounter += $fac;
                    }

                    //Add to the files counter
                    $this->_fileCounter['gen']['totalFiles']++;
                    
                    if(!isset($this->_fileCounter[strtolower($ext)])){
                        $this->_fileCounter[strtolower($ext)] = 0;
                    }
                    
                    $this->_fileCounter[strtolower($ext)]++;
                }
            }
        } else {
            echo 'Could not enter folder';
        }

        return $lineCounter;
    }

}
