<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;

/**
 * Description of FhsScannedModel
 *
 * @author Tomy
 */
class FhsScannedModel extends Model
{

    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     * @unsigned
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @length 10
     * @unsigned
     * 
     * @validate numeric, max(10)
     * @label changes
     */
    protected $_changes;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     * @label scanned
     */
    protected $_scanned;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 20
     * 
     * @validate alphanumeric, max(20)
     * @label account
     */
    protected $_acct;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_modified;

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
    
    public static function getLastScann()
    {
        return self::first(array(),array('*'), array('created' => 'desc'));
    }

}
