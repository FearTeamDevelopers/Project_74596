<?php

namespace THCFrame\Configuration\Model;

use THCFrame\Model\Model;

/**
 * ORM Config model 
 * 
 * SQL code:
 * CREATE TABLE `tb_config` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`title` varchar(200) NOT NULL DEFAULT '',
`xkey` varchar(200) NOT NULL DEFAULT '',
`value` varchar(500) NOT NULL DEFAULT '',
`created` varchar(22) DEFAULT NULL,
`modified` varchar(22) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
class ConfigModel extends Model
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
     * @length 200
     * 
     * @validate required, alphanumeric, max(200)
     * @label title
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     * 
     * @validate required, alphanumeric, max(200)
     * @label key
     */
    protected $_xkey;

    /**
     * @column
     * @readwrite
     * @type text
     * @null
     * 
     * @validate required, alphanumeric, max(2048)
     * @label value
     */
    protected $_value;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
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
     * @validate datetime, max(19)
     */
    protected $_modified;

    /**
     * 
     */
    public function preSave()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary['raw'];

        if (empty($this->$raw)) {
            $this->setCreated(date('Y-m-d H:i:s'));
        }
        $this->setModified(date('Y-m-d H:i:s'));
    }

}
