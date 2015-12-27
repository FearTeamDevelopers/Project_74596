<?php

namespace App\Model\Basic;

use THCFrame\Model\Model;
                
class BasicPhotoModel extends Model 
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
     * @foreign fk_photo_gallery REFERENCES tb_gallery (id) ON DELETE CASCADE ON UPDATE NO ACTION
     * @type int
     * @length 11
     * @validate numeric,max(11)
     * @unsigned
     */
    protected $_galleryId;

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
     * @type varchar
     * @length 60
     * @validate alphanumeric,max(60)
     * @label název fotky
     */
    protected $_photoName;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * @validate required,path,max(250)
     * @label thumb path
     */
    protected $_imgThumb;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * @validate required,path,max(250)
     * @label photo path
     */
    protected $_imgMain;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate alphanumeric,max(500)
     * @label popis
     * @null
     */
    protected $_description;

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
     * @type varchar
     * @length 32
     * @validate required,max(32)
     * @label mime type
     */
    protected $_mime;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 10
     * @validate required,alpha,max(10)
     * @label format
     */
    protected $_format;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 10
     * @validate required,numeric,max(10)
     * @label size
     * @default 0
     */
    protected $_size;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 5
     * @validate required,numeric,max(5)
     * @label width
     * @default 0
     */
    protected $_width;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 5
     * @validate required,numeric,max(5)
     * @label height
     * @default 0
     */
    protected $_height;

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