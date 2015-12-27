<?php

namespace Admin\Model\Basic;

use THCFrame\Model\Model;
                
class BasicImessageModel extends Model 
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
     * @foreign fk_imessage_user REFERENCES tb_user (id) ON DELETE SET NULL ON UPDATE NO ACTION
     * @type int
     * @length 11
     * @validate numeric,max(11)
     * @label autor
     * @unsigned
     * @null
     */
    protected $_userId;

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
     * @index
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @label typ
     * @default 1
     */
    protected $_messageType;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 80
     * @validate alphanumeric,max(80)
     * @label alias autora
     */
    protected $_userAlias;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 150
     * @validate required,alphanumeric,max(150)
     * @label nazev
     */
    protected $_title;

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
     * @length 10
     * @validate date,max(10)
     * @label zobrazovat od
     */
    protected $_displayFrom;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 10
     * @validate date,max(10)
     * @label zobrazovat do
     */
    protected $_displayTo;

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