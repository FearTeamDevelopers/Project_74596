<?php

namespace Admin\Model\Basic;

use THCFrame\Model\Model;
                
class BasicConceptModel extends Model 
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
     * @foreign fk_concept_user REFERENCES tb_user (id) ON DELETE SET NULL ON UPDATE NO ACTION
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
     * @label typ
     * @default 0
     */
    protected $_type;

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
     * @type text
     * @validate html
     * @label teaser
     * @null
     */
    protected $_shortBody;

    /**
     * @column
     * @readwrite
     * @type mediumtext
     * @validate html
     * @label text
     * @null
     */
    protected $_body;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * @validate alphanumeric,max(250)
     * @label keywords
     */
    protected $_keywords;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 150
     * @validate alphanumeric,max(150)
     * @label meta
     */
    protected $_metaTitle;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate alphanumeric
     * @label meta
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