<?php

namespace App\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class AttendanceModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'at';

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
     * @label id uzivatele
     */
    protected $_userId;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label id akce
     */
    protected $_actionId;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @index
     * 
     * @validate max(3)
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 250
     * 
     * @validate alphanumeric, max(350)
     * @label comment
     */
    protected $_comment;

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
        }
        
        $this->setModified(date('Y-m-d H:i:s'));
    }

    /**
     * 
     * @param type $userId
     * @return type
     */
    public static function fetchActionsByUserId($userId)
    {
        $query = self::getQuery(array('at.type', 'at.comment'))
                ->join('tb_action', 'at.actionId = ac.id', 'ac', 
                        array('ac.*'))
                ->where('at.userId = ?', (int)$userId);
        
        print('<pre>'.print_r($query->assemble(), true).'</pre>');die;
        return self::initialize($query);
    }

    /**
     * 
     * @param type $actionId
     * @return type
     */
    public static function fetchUsersByActionId($actionId)
    {
        $query = self::getQuery(array('at.type', 'at.comment'))
                ->join('tb_user', 'at.userId = us.id', 'us', 
                        array('us.*'))
                ->where('at.actionId = ?', (int)$actionId);
        
        print('<pre>'.print_r($query->assemble(), true).'</pre>');die;
        return self::initialize($query);
    }
    
}
