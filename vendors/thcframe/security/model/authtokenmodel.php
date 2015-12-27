<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;

/**
 * Authtoken used by "Remember me" function
 */
class AuthtokenModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'auth';

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
     * @index
     * @type int
     * @length 10
     * @unsigned
     * @null
     * 
     * @validate required,numeric, max(10)
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 130
     * @index
     * @unique
     *
     * @validate required, alphanumeric, max(130)
     * @label auth token
     */
    protected $_token;

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
        }
        $this->setModified(date("Y-m-d H:i:s"));
    }

}
