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
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate alphanumeric, max(150)
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * 
     * @validate alpha, max(50)
     */
    protected $_module;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * 
     * @validate alpha, max(50)
     */
    protected $_controller;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * 
     * @validate alpha, max(50)
     */
    protected $_action;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate alphanumeric
     */
    protected $_params;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate alphanumeric, max(500)
     */
    protected $_userAgent;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * 
     * @validate numeric, max(20)
     */
    protected $_userIp;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
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
