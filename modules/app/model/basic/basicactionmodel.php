<?php

namespace App\Model\Basic;

use THCFrame\Model\Model;
                
class BasicActionModel extends Model 
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
     * @foreign fk_action_user REFERENCES tb_user (id) ON DELETE SET NULL ON UPDATE NO ACTION
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
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 1
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 0
     */
    protected $_approved;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 0
     */
    protected $_archive;

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
     * @label teaser
     * @null
     */
    protected $_shortBody;

    /**
     * @column
     * @readwrite
     * @type mediumtext
     * @validate required,html
     * @label text
     * @null
     */
    protected $_body;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 3
     * @validate numeric,max(3)
     * @label pořadí
     * @default 1
     */
    protected $_rank;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 10
     * @validate required,date,max(10)
     * @label datum začátek
     * @null
     */
    protected $_startDate;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 10
     * @validate required,date,max(10)
     * @label datum konec
     * @null
     */
    protected $_endDate;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 8
     * @validate time,max(8)
     * @label čas začátek
     * @null
     */
    protected $_startTime;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 8
     * @validate time,max(8)
     * @label čas konec
     * @null
     */
    protected $_endTime;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 350
     * @validate alphanumeric,max(350)
     * @label keywords
     */
    protected $_keywords;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 150
     * @validate alphanumeric,max(150)
     * @label metatitle
     */
    protected $_metaTitle;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate alphanumeric
     * @label metadescription
     * @null
     */
    protected $_metaDescription;

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