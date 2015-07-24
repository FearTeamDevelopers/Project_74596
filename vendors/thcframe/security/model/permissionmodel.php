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
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type text
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
     * @type boolean
     * 
     * @validate max(3)
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     *
     * @validate required, alpha, max(50)
     * @label module
     */
    protected $_module;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     *
     * @validate required, alpha, max(50)
     * @label controller
     */
    protected $_controller;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     *
     * @validate required, alpha, max(50)
     * @label action
     */
    protected $_action;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate max(3)
     */
    protected $_isAllowed;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate max(3)
     */
    protected $_isDenied;

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
