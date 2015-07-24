<?php

namespace Admin\Model;

use THCFrame\Model\Model;

/**
 * Email template ORM class
 */
class EmailTemplateModel extends Model
{

    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate alphanumeric, max(150)
     * @label nazev
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 200
     * @unique
     * 
     * @validate required, alphanumeric, max(200)
     * @label url key
     */
    protected $_urlKey;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 200
     * 
     * @validate alphanumeric, max(200)
     * @label subject
     */
    protected $_subject;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate html
     * @label text
     */
    protected $_body;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate html
     * @label text
     */
    protected $_bodyEn;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * 
     * @validate numeric, max(2)
     * @label type
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
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
            $this->setActive(true);
        }
        $this->setModified(date('Y-m-d H:i:s'));
    }

    public static function fetchAll()
    {
        return self::all();
    }
    
    public static function fetchAllCommon()
    {
        return self::all(array('type = ?' => 1));
    }

    public static function fetchById($id)
    {
        return \Admin\Model\EmailTemplateModel::first(array('id = ?' => (int) $id));
    }
    
    public static function fetchAllActive()
    {
        return self::all(array('active = ?' => true));
    }
    
    public static function fetchAllCommonActive()
    {
        return self::all(array('active = ?' => true, 'type = ?' => 1));
    }

    public static function fetchCommonActiveByIdAndLang($id, $fieldName)
    {
        return \Admin\Model\EmailTemplateModel::first(
                        array('id = ?' => (int) $id, 'active = ?' => true, 'type = ?' => 1), 
                        array($fieldName, 'subject'));
    }
    
    public static function fetchActiveByIdAndLang($id, $fieldName)
    {
        return \Admin\Model\EmailTemplateModel::first(
                        array('id = ?' => (int) $id, 'active = ?' => true), 
                        array($fieldName, 'subject'));
    }

}
