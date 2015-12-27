<?php

namespace THCFrame\Logger\Driver;

use THCFrame\Logger;

/**
 * File logger class
 */
class File extends Logger\Driver
{

    const DIR_CHMOD = 0755;
    const FILE_CHMOD = 0644;
    const MAX_FILE_SIZE = 5000000;
    const ERROR_LOG = 1;
    const DEBUG_LOG = 2;
    const SQL_LOG = 3;
    const CRON_LOG = 4;

    /**
     * Object constructor
     * 
     * @param array $options
     */
    public function __construct($options = null)
    {
        $options = array(
            'path' => 'application' . DIRECTORY_SEPARATOR . 'logs',
            'debuglog' => '{date}-debug.log',
            'errorlog' => '{date}-error.log',
            'sqllog' => '{date}-sql.log',
            'cronlog' => '{date}-cron.log',
            'log' => '{date}.log',
        );

        parent::__construct($options);

        $this->path = APP_PATH . DIRECTORY_SEPARATOR . trim($this->path, DIRECTORY_SEPARATOR);
        $this->log = $this->path . DIRECTORY_SEPARATOR
                . str_replace('{date}', date('Y-m-d', time()), trim($this->log, DIRECTORY_SEPARATOR));
        $this->debuglog = $this->path . DIRECTORY_SEPARATOR
                . str_replace('{date}', date('Y-m-d', time()), trim($this->debuglog, DIRECTORY_SEPARATOR));
        $this->sqllog = $this->path . DIRECTORY_SEPARATOR
                . str_replace('{date}', date('Y-m-d', time()), trim($this->sqllog, DIRECTORY_SEPARATOR));
        $this->cronlog = $this->path . DIRECTORY_SEPARATOR
                . str_replace('{date}', date('Y-m-d', time()), trim($this->cronlog, DIRECTORY_SEPARATOR));
        $this->errorlog = $this->path . DIRECTORY_SEPARATOR
                . str_replace('{date}', date('Y-m-d', time()), trim($this->errorlog, DIRECTORY_SEPARATOR));

        if (!is_dir($this->path)) {
            mkdir($this->path, self::DIR_CHMOD);
        }

        $date = date('Y-m-d', strtotime('-90 days'));
        $this->deleteOldLogs($date);
    }

    private function interpolate($message, array $context = array())
    {
        // vytvoří nahrazovací pole se závorkami okolo kontextových klíčů
        $replace = array();
        if (!empty($context)) {
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }
            // interpoluje nahrazovací hodnoty do zprávy a vrátí je
            return strtr($message, $replace);
        }
        return $message;
    }

    private function putContents($file, $message)
    {
        if (!file_exists($file)) {
            file_put_contents($file, $message, FILE_APPEND);
        } elseif (file_exists($file) && filesize($file) < self::MAX_FILE_SIZE) {
            file_put_contents($file, $message, FILE_APPEND);
        } elseif (file_exists($file) && filesize($file) > self::MAX_FILE_SIZE) {
            for ($i = 1; $i < 100; $i+=1) {
                if (!file_exists($file . $i)) {
                    file_put_contents($file . $i, $message, FILE_APPEND);
                } elseif (file_exists($file . $i) && filesize($file . $i) < self::MAX_FILE_SIZE) {
                    file_put_contents($file . $i, $message, FILE_APPEND);
                } else {
                    continue;
                }
            }
        }
    }

    private function appendLine($level, $message, $context, $type = 0)
    {
        $message = '[' . date('Y-m-d H:i:s') . '][' . $level . ']' . $this->interpolate($message, $context) . PHP_EOL;

        if ($type === self::DEBUG_LOG) {
            $this->putContents($this->debuglog, $message);
        } elseif ($type === self::SQL_LOG) {
            $this->putContents($this->sqllog, $message);
        } elseif ($type === self::CRON_LOG) {
            $this->putContents($this->cronlog, $message);
        } elseif ($type === self::ERROR_LOG) {
            $this->putContents($this->errorlog, $message);
        } else {
            $this->putContents($this->log, $message);
        }
    }

    /**
     * Delete old log files
     * 
     * @param string $olderThan   date yyyy-mm-dd
     */
    private function deleteOldLogs($olderThan)
    {
        if (!is_dir($this->path)) {
            return;
        }

        $iterator = new \DirectoryIterator($this->path);
        $arr = array();

        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile()) {
                $date = substr($item->getFilename(), 0, 10);

                if (!preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $date)) {
                    continue;
                }

                if (time() - strtotime($date) > time() - strtotime($olderThan)) {
                    $arr[] = $this->path . DIRECTORY_SEPARATOR . $item->getFilename();
                }
            }
        }

        if (!empty($arr)) {
            foreach ($arr as $path) {
                unlink($path);
            }
        }
    }

    public function emergency($message, array $context = array())
    {
        $this->appendLine(self::EMERGENCY, $message, $context, self::ERROR_LOG);
        return $this;
    }

    public function alert($message, array $context = array())
    {
        $this->appendLine(self::ALERT, $message, $context, self::ERROR_LOG);
        return $this;
    }

    public function critical($message, array $context = array())
    {
        $this->appendLine(self::CRITICAL, $message, $context, self::ERROR_LOG);
        return $this;
    }

    public function error($message, array $context = array())
    {
        $this->appendLine(self::ERROR, $message, $context, self::ERROR_LOG);
        return $this;
    }

    public function warning($message, array $context = array())
    {
        $this->appendLine(self::WARNING, $message, $context, self::ERROR_LOG);
        return $this;
    }

    public function notice($message, array $context = array())
    {
        $this->appendLine(self::NOTICE, $message, $context, self::ERROR_LOG);
        return $this;
    }

    public function info($message, array $context = array())
    {
        $this->appendLine(self::INFO, $message, $context);
        return $this;
    }

    public function debug($message, array $context = array())
    {
        $this->appendLine(self::DEBUG, $message, $context, self::DEBUG_LOG);
        return $this;
    }

    public function sql($message, array $context = array())
    {
        $this->appendLine(self::SQL, $message, $context, self::SQL_LOG);
        return $this;
    }

    public function cron($message, array $context = array())
    {
        $this->appendLine(self::SQL, $message, $context, self::CRON_LOG);
        return $this;
    }

    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case self::EMERGENCY:
                $this->emergency($message, $context);
                break;
            case self::ALERT:
                $this->alert($message, $context);
                break;
            case self::CRITICAL:
                $this->critical($message, $context);
                break;
            case self::ERROR:
                $this->error($message, $context);
                break;
            case self::WARNING:
                $this->error($message, $context);
                break;
            case self::NOTICE:
                $this->notice($message, $context);
                break;
            case self::INFO:
                $this->info($message, $context);
                break;
            case self::DEBUG:
                $this->debug($message, $context);
                break;
            case self::SQL:
                $this->sql($message, $context);
                break;
            case self::CRON:
                $this->cron($message, $context);
                break;
        }
        
        return $this;
    }

}
