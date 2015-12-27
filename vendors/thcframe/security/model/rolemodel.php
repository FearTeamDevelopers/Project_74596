<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class RoleModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'rl';

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
     * @type int
     * @length 10
     * @unsigned
     * @null
     * 
     * @validate numeric, max(10)
     */
    protected $_parentId;

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
     * @length 100
     * @index
     * @unique
     *
     * @validate required, alphanumeric, max(100)
     * @label role name
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @null
     * 
     * @validate alphanumeric, max(1024)
     * @label description
     */
    protected $_description;

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
    protected $_isLocked;

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
     * @readwrite
     * @var type 
     */
    protected $_permissions;
    
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
            $this->setIsLocked(false);
        }

        $this->setModified(date("Y-m-d H:i:s"));
    }
    
    public static function fetchPermissions()
    {
        
    }

}
