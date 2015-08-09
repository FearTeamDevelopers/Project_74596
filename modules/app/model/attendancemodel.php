<?php

namespace App\Model;

use THCFrame\Model\Model;
use THCFrame\Date\Date;

/**
 * 
 */
class AttendanceModel extends Model
{

    const ACCEPT = 1;
    const REJECT = 2;
    const MAYBE = 3;
    
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
    public static function fetchActionsByUserId($userId, $future = false)
    {
        $query = self::getQuery(array('at.id', 'at.type', 'at.comment'))
                ->join('tb_action', 'at.actionId = ac.id', 'ac', 
                        array('ac.id' => 'acId','ac.title', 'ac.urlKey', 'ac.startDate', 'ac.endDate'))
                ->where('at.userId = ?', (int)$userId)
                ->order('ac.startDate', 'ASC');
        
        if($future === true){
            $query->where('ac.startDate >= ?', date('Y-m-d'));
        }
        
        return self::initialize($query);
    }

    /**
     * 
     * @param type $actionId
     * @return type
     */
    public static function fetchUsersByActionId($actionId)
    {
        $query = self::getQuery(array('at.id', 'at.type', 'at.comment'))
                ->join('tb_user', 'at.userId = us.id', 'us', 
                        array('us.id' => 'usId', 'us.firstname', 'us.lastname'))
                ->where('at.actionId = ?', (int)$actionId)
                ->order('us.lastname', 'ASC');
        
        return self::initialize($query);
    }
    
    /**
     * 
     * @return type
     */
    public static function fetchPercentAttendance($type)
    {
        if(!array_key_exists($type, ActionModel::getTypes())){
            return null;
        }
        
        $totalCount = ActionModel::count(array('active = ?' => true, 'startDate <= ?' => date('Y-m-d'), 'actionType' => $type));

        $query = self::getQuery(array('at.*', 'COUNT(at.id)' => 'cnt'))
                ->join('tb_user', 'us.id = at.userId', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->join('tb_action', 'at.actionId = ac.id', 'ac', 
                        array('ac.startDate'))
                ->where('at.type = ?', self::ACCEPT)
                ->where('ac.actionType = ?', $type)
                ->where('ac.startDate <= ?', date('Y-m-d'))
                ->groupby('at.userId')
                ->order('us.lastname', 'ASC');

        $attend = self::initialize($query);

        $ra = array();

        if ($attend !== null) {
            foreach ($attend as $value) {
                $ra[$value->firstname . ' ' . $value->lastname] = round(($value->cnt / $totalCount) * 100, 2);
            }

            return $ra;
        } else {
            return null;
        }
    }
    
    /**
     * 
     * @param type $month
     * @param type $year
     */
    public static function fetchMonthAttendance($month = null, $year = null)
    {
        $firstDay = Date::getInstance()->getFirstDayOfMonth($month, $year);
        $lastDay = Date::getInstance()->getLastDayOfMonth($month, $year);
        $returnArr = array();
        
        $usersQ = self::getQuery(array('distinct userId'))
                ->join('tb_user', 'us.id = at.userId', 'us', 
                            array('us.firstname', 'us.lastname'));
        
        $users = self::initialize($usersQ);

        if(!empty($users)){
            foreach($users as $user){
                $attQ = self::getQuery(array('at.actionId', 'at.type', 'at.comment'))
                        ->join('tb_action', 'at.actionId = ac.id', 'ac',
                                array('ac.actionType', 'ac.startDate', 'ac.endDate', 'ac.urlKey'))
                        ->where('at.userId = ?', $user->getUserId())
                        ->where('ac.startDate >= ?', $firstDay)
                        ->where('ac.startDate <= ?', $lastDay)
                        ->order('ac.startDate', 'asc');

                $attendance = self::initialize($attQ);
                
                if(!empty($attendance)){
                    foreach ($attendance as $attend){
                        $rec = array('type' => $attend->getType(), 
                                    'comment' => $attend->getComment());
                        $returnArr[$user->getUserId().'|'.$user->getFirstname().' '.$user->getLastname()][$attend->getActionType().'|'.$attend->getStartDate()] = $rec;
                    }
                }else{
                    $returnArr[$user->getUserId().'|'.$user->getFirstname().' '.$user->getLastname()] = array();
                }
            }
        }
        
        return $returnArr;
    }
}
