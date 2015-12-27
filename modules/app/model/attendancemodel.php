<?php

namespace App\Model;

use THCFrame\Date\Date;
use App\Model\Basic\BasicAttendanceModel;

/**
 * 
 */
class AttendanceModel extends BasicAttendanceModel
{

    const ACCEPT = 1;
    const REJECT = 2;
    const MAYBE = 3;

    /**
     * @readwrite
     */
    protected $_alias = 'at';

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
     * @return array
     */
    public static function getAttendanceReturnArray()
    {
        return array(self::ACCEPT => array(), self::REJECT => array(), self::MAYBE => array());
    }

    /**
     * 
     * @param integer $userId
     * @return array
     */
    public static function fetchActionsByUserId($userId, $future = false)
    {
        $query = self::getQuery(array('at.id', 'at.type', 'at.comment'))
                ->join('tb_action', 'at.actionId = ac.id', 'ac', array('ac.id' => 'acId', 'ac.title', 'ac.urlKey', 'ac.startDate', 'ac.endDate'))
                ->where('at.userId = ?', (int) $userId)
                ->order('ac.startDate', 'ASC');

        if ($future === true) {
            $query->where('ac.startDate >= ?', date('Y-m-d'));
        }

        return self::initialize($query);
    }

    /**
     * @param integer $actionId
     * @return array
     */
    public static function fetchUsersByActionId($actionId)
    {
        $query = self::getQuery(array('at.id', 'at.type', 'at.comment'))
                ->join('tb_user', 'at.userId = us.id', 'us', array('us.id' => 'usId', 'us.firstname', 'us.lastname', 'us.email'))
                ->where('at.actionId = ?', (int) $actionId)
                ->order('us.lastname', 'ASC');

        return self::initialize($query);
    }

    /**
     * 
     * @param integer $actionId
     * @return array
     */
    public static function fetchUsersByActionIdSimpleArr($actionId)
    {
        $query = self::getQuery(array('at.id', 'at.type', 'at.comment'))
                ->join('tb_user', 'at.userId = us.id', 'us', array('us.id' => 'usId', 'us.firstname', 'us.lastname', 'us.email'))
                ->where('at.actionId = ?', (int) $actionId)
                ->order('us.lastname', 'ASC');

        $result = self::initialize($query);

        $returnArr = array(self::ACCEPT => array(), self::REJECT => array(), self::MAYBE => array());
        if (!empty($result)) {
            foreach ($result as $att) {
                if ($att->type == self::ACCEPT) {
                    $returnArr[self::ACCEPT][] = $att->firstname . ' ' . $att->lastname;
                } elseif ($att->type == self::REJECT) {
                    $returnArr[self::REJECT][] = $att->firstname . ' ' . $att->lastname;
                } elseif ($att->type == self::MAYBE) {
                    $returnArr[self::MAYBE][] = $att->firstname . ' ' . $att->lastname;
                }
            }
        }
        return $returnArr;
    }

    /**
     * 
     * @param integer $userId
     * @param integer $actionId
     * @return array
     */
    public static function fetchTypeByUserAndAction($userId, $actionId)
    {
        $result = self::first(array('userId = ?' => (int) $userId, 'actionId = ?' => (int) $actionId), array('type'));
        
        if(!empty($result)){
            return $result->getType();
        }
        
        return 0;
    }

    /**
     * @return type
     */
    public static function fetchPercentAttendance($type)
    {
        if (!array_key_exists($type, ActionModel::getTypes())) {
            return;
        }

        $totalCount = ActionModel::count(array('active = ?' => true, 'startDate <= ?' => date('Y-m-d'), 'actionType' => $type));

        $query = self::getQuery(array('at.*', 'COUNT(at.id)' => 'cnt'))
                ->join('tb_user', 'us.id = at.userId', 'us', array('us.firstname', 'us.lastname'))
                ->join('tb_action', 'at.actionId = ac.id', 'ac', array('ac.startDate'))
                ->where('at.type = ?', self::ACCEPT)
                ->where('ac.actionType = ?', $type)
                ->where('ac.startDate <= ?', date('Y-m-d'))
                ->groupby('at.userId')
                ->order('us.lastname', 'ASC');

        $attend = self::initialize($query);

        $returnArr = array();

        if ($attend !== null) {
            foreach ($attend as $value) {
                $returnArr[$value->firstname . ' ' . $value->lastname] = round(($value->cnt / $totalCount) * 100, 2);
            }
        }

        return $returnArr;
    }

    /**
     * @param type $month
     * @param type $year
     */
    public static function fetchMonthAttendance($month = null, $year = null)
    {
        $firstDay = Date::getInstance()->getFirstDayOfMonth($month, $year);
        $lastDay = Date::getInstance()->getLastDayOfMonth($month, $year);
        $returnArr = array();

        $usersQ = self::getQuery(array('distinct userId'))
                ->join('tb_user', 'us.id = at.userId', 'us', array('us.firstname', 'us.lastname'));

        $users = self::initialize($usersQ);

        if (!empty($users)) {
            foreach ($users as $user) {
                $attQ = self::getQuery(array('at.actionId', 'at.type', 'at.comment'))
                        ->join('tb_action', 'at.actionId = ac.id', 'ac', array('ac.actionType', 'ac.startDate', 'ac.endDate'))
                        ->where('at.userId = ?', $user->getUserId())
                        ->where('ac.startDate >= ?', $firstDay)
                        ->where('ac.startDate <= ?', $lastDay)
                        ->order('ac.startDate', 'asc');

                $attendance = self::initialize($attQ);

                if (!empty($attendance)) {
                    foreach ($attendance as $attend) {
                        $rec = array('type' => $attend->getType(),
                            'comment' => $attend->getComment(),);
                        $returnArr[$user->getUserId() . '|' . $user->getFirstname() . ' ' . $user->getLastname()][$attend->getActionType() . '|' . $attend->getStartDate()] = $rec;
                    }
                } else {
                    $returnArr[$user->getUserId() . '|' . $user->getFirstname() . ' ' . $user->getLastname()] = array();
                }
            }
        }

        return $returnArr;
    }

}
