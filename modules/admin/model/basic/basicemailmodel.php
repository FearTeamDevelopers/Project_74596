<?php

namespace Admin\Model\Basic;

use THCFrame\Model\Model;
                
class BasicEmailModel extends Model 
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
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 1
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 150
     * @validate alphanumeric,max(150)
     * @label nazev
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @unique
     * @type varchar
     * @length 200
     * @validate required,alphanumeric,max(200)
     * @label url key
     */
    protected $_urlKey;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     * @validate alphanumeric,max(200)
     * @label subject
     */
    protected $_subject;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate html
     * @label text
     * @null
     */
    protected $_body;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate html
     * @label text
     * @null
     */
    protected $_bodyEn;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @label type
     * @default 1
     */
    protected $_type;

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