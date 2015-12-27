<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;

/**
 * Description of FhsHistoryModel
 *
 * @author Tomy
 */
class FhsHistoryModel extends Model
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
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     * @label timestamp
     */
    protected $_timestamp;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 15
     * 
     * @validate alphanumeric, max(15)
     * @label status
     */
    protected $_status;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * 
     * @validate path, max(250)
     * @label path
     */
    protected $_path;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     * 
     * @validate alphanumeric, max(200)
     * @label file hash orig
     */
    protected $_hashOrig;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     * 
     * @validate alphanumeric, max(200)
     * @label file hash new
     */
    protected $_hashNew;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     * @label last modification
     */
    protected $_lastMod;

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

}
