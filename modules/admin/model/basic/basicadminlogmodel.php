<?php

namespace Admin\Model\Basic;

use THCFrame\Model\Model;
                
class BasicAdminlogModel extends Model 
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
     * @length 80
     * @validate alphanumeric,max(80)
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * @validate alpha,max(50)
     */
    protected $_module;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * @validate alpha,max(50)
     */
    protected $_controller;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * @validate alpha,max(50)
     */
    protected $_action;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 15
     * @validate alpha,max(15)
     */
    protected $_result;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * @validate alphanumeric,max(250)
     */
    protected $_httpreferer;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate alphanumeric
     * @null
     */
    protected $_params;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @validate datetime,max(19)
     * @null
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @validate datetime,max(19)
     * @null
     */
    protected $_modified;

}