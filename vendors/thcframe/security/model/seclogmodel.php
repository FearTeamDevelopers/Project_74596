<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class SecLogModel extends Model
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
     * @type varchar
     * @length 100
     * 
     * @validate alphanumeric, max(100)
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * 
     * @validate alpha, max(50)
     */
    protected $_module;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * 
     * @validate alpha, max(50)
     */
    protected $_controller;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * 
     * @validate alpha, max(50)
     */
    protected $_action;

    /**
     * @column
     * @readwrite
     * @type text
     * @null
     * 
     * @validate alphanumeric
     */
    protected $_params;

    /**
     * @column
     * @readwrite
     * @type text
     * @null
     * 
     * @validate alphanumeric, max(500)
     */
    protected $_userAgent;
    
    /**
     * @column
     * @readwrite
     * @type char
     * @length 15
     * 
     * @validate numeric, max(15)
     */
    protected $_userIp;
    
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
