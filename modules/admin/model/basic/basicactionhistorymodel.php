<?php

namespace Admin\Model\Basic;

use THCFrame\Model\Model;
                
class BasicActionhistoryModel extends Model 
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
     * @type int
     * @length 10
     * @validate numeric,max(10)
     * @label source id
     * @unsigned
     * @default 0
     */
    protected $_originId;

    /**
     * @column
     * @readwrite
     * @index
     * @type int
     * @length 10
     * @validate numeric,max(10)
     * @label editor id
     * @unsigned
     * @default 0
     */
    protected $_editedBy;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 30
     * @validate alphanumeric,max(30)
     * @label remote
     */
    protected $_remoteAddr;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 150
     * @validate url,max(150)
     * @label referrer
     */
    protected $_referer;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate alphanumeric
     * @label changes
     * @null
     */
    protected $_changedData;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @validate datetime,max(19)
     * @null
     */
    protected $_created;

}