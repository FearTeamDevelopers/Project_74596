<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class PermissionModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'perm';

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
     * @index
     * @unique
     *
     * @validate required, alphanumeric, max(100)
     * @label permission name
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 1
     * @validate max(1)
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     *
     * @validate required, alpha, max(50)
     * @label module
     */
    protected $_module;
    
    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     *
     * @validate required, alpha, max(50)
     * @label controller
     */
    protected $_controller;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     *
     * @validate required, alpha, max(50)
     * @label action
     */
    protected $_action;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 1
     * @validate max(1)
     */
    protected $_isAllowed;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 1
     * @validate max(1)
     */
    protected $_isDenied;

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
        $raw = $primary["raw"];

        if (empty($this->$raw)) {
            $this->setCreated(date("Y-m-d H:i:s"));
            $this->setActive(true);
            $this->setIsAllowed(true);
            $this->setIsDenied(false);
        }

        $this->setModified(date("Y-m-d H:i:s"));
    }

}
