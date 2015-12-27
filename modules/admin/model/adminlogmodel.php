<?php

namespace Admin\Model;

use Admin\Model\Basic\BasicAdminlogModel;

/**
 * Log ORM class.
 */
class AdminLogModel extends BasicAdminlogModel
{

    /**
     * 
     */
    public function preSave()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary['raw'];

        if (empty($this->$raw)) {
            $this->setCreated(date('Y-m-d H:i:s'));
        }
        $this->setModified(date('Y-m-d H:i:s'));
    }
    
    /**
     * Get errors from last week
     * @return array
     */
    public static function fetchErrorsFromLastWeek()
    {
        return self::all(array('result = ?' => 'fail', 'created between date_sub(now(),INTERVAL 1 WEEK) and now()' => ''), array('*'), array('created' => 'desc'));
    }
}
