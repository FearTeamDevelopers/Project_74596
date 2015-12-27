<?php

namespace App\Model\Basic;

use THCFrame\Model\Model;
                
class BasicCommentModel extends Model 
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
     * @foreign fk_comment_user REFERENCES tb_user (id) ON DELETE SET NULL ON UPDATE NO ACTION
     * @type int
     * @length 11
     * @validate numeric,max(11)
     * @label id autora
     * @unsigned
     * @null
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @index
     * @type int
     * @length 10
     * @validate numeric,max(10)
     * @label id objektu
     * @unsigned
     * @default 0
     */
    protected $_resourceId;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 10
     * @unsigned
     * @default 0
     */
    protected $_replyTo;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 0
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate required,html
     * @label text
     * @null
     */
    protected $_body;

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