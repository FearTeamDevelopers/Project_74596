<?php

namespace App\Model\Basic;

use THCFrame\Model\Model;
                
class BasicGalleryModel extends Model 
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
     * @foreign fk_gallery_user REFERENCES tb_user (id) ON DELETE SET NULL ON UPDATE NO ACTION
     * @type int
     * @length 11
     * @validate numeric,max(11)
     * @unsigned
     * @null
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 10
     * @validate numeric,max(10)
     * @unsigned
     * @default 0
     */
    protected $_avatarPhotoId;

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
     * @label název
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate required,html,max(1024)
     * @label popis
     * @null
     */
    protected $_description;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 1
     */
    protected $_isPublic;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 3
     * @validate numeric,max(3)
     * @label rank
     * @default 1
     */
    protected $_rank;

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