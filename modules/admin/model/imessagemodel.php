<?php

namespace Admin\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class ImessageModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'ims';

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
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label autor
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * 
     * @validate alpha, max(20)
     * @label typ
     */
    protected $_messageType;

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
     * @length 80
     * 
     * @validate alphanumeric, max(80)
     * @label alias autora
     */
    protected $_userAlias;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate required, alphanumeric, max(150)
     * @label nazev
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate required, html
     * @label text
     */
    protected $_body;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 12
     * 
     * @validate date, max(12)
     * @label zobrazovat od
     */
    protected $_displayFrom;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 12
     * 
     * @validate date, max(12)
     * @label zobrazovat do
     */
    protected $_displayTo;

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

    /**
     * 
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('ims.*'))
                ->join('tb_user', 'ims.userId = us.id', 'us', array('us.firstname', 'us.lastname'));

        return self::initialize($query);
    }
    
    /**
     * 
     * @return array
     */
    public static function fetchActive()
    {
        return self::all(array('displayFrom <= ?' => date('Y-m-d', time()), 'displayTo >= ?' => date('Y-m-d', time()), 'active = ?' => true));
    }
}
