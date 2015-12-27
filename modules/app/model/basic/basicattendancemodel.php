<?php

namespace App\Model\Basic;

use THCFrame\Model\Model;
                
class BasicAttendanceModel extends Model 
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
     * @foreign fk_attendance_user REFERENCES tb_user (id) ON DELETE SET NULL ON UPDATE NO ACTION
     * @type int
     * @length 11
     * @validate numeric,max(11)
     * @label id uzivatele
     * @unsigned
     * @null
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @foreign fk_attendance_action REFERENCES tb_action (id) ON DELETE CASCADE ON UPDATE NO ACTION
     * @type int
     * @length 11
     * @validate numeric,max(11)
     * @label id akce
     * @unsigned
     */
    protected $_actionId;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * @validate max(1)
     * @default 0
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * @validate alphanumeric,max(350)
     * @label comment
     */
    protected $_comment;

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