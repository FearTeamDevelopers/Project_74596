<?php

namespace App\Model;

use App\Model\Basic\BasicActionModel;

/**
 * 
 */
class ActionModel extends BasicActionModel
{

    const STATE_WAITING = 0;
    const STATE_APPROVED = 1;
    const STATE_REJECTED = 2;

    /**
     * @var type
     */
    private static $_statesConv = array(
        self::STATE_WAITING => 'Čeká na shválení',
        self::STATE_APPROVED => 'Schváleno',
        self::STATE_REJECTED => 'Zamítnuto',
    );

    /**
     * @readwrite
     */
    protected $_alias = 'ac';

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

        $shortText = preg_replace('/https:/i', 'http:', $this->getShortBody());
        $text = preg_replace('/https:/i', 'http:', $this->getBody());
        $this->setShortBody($shortText);
        $this->setBody($text);
        $this->setModified(date('Y-m-d H:i:s'));
    }

    /**
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('ac.*'))
                ->join('tb_user', 'ac.userId = us.id', 'us', array('us.firstname', 'us.lastname'));

        return self::initialize($query);
    }

    /**
     * Called from admin module.
     * 
     * @return array
     */
    public static function fetchWithLimit($limit = 10)
    {
        $query = self::getQuery(array('ac.*'))
                ->join('tb_user', 'ac.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                ->order('ac.created', 'desc')
                ->limit((int) $limit);

        return self::initialize($query);
    }

    /**
     * Called from app module.
     * 
     * @param type $limit
     * @param type $page
     * @return type
     */
    public static function fetchActiveWithLimit($limit = 10, $page = 1)
    {
        if ($limit === 0) {
            $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false, 'startDate >= ?' => date('Y-m-d', time())), 
                    array('id', 'urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                    array('rank' => 'desc', 'startDate' => 'asc')
            );
        } else {
            $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false, 'startDate >= ?' => date('Y-m-d', time())), 
                    array('id', 'urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                    array('rank' => 'desc', 'startDate' => 'asc'), $limit, $page
            );
        }

        return $actions;
    }

    /**
     * Called from app module.
     * 
     * @param type $limit
     * @param type $page
     * @return type
     */
    public static function fetchOldWithLimit($limit = 10, $page = 1)
    {
        $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false, 'startDate <= ?' => date('Y-m-d', time())), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                array('rank' => 'desc', 'startDate' => 'desc'), $limit, $page
        );

        return $actions;
    }

    /**
     * Called from app module.
     * 
     * @param type $limit
     * @param type $page
     * @return type
     */
    public static function fetchArchivatedWithLimit($limit = 10, $page = 1)
    {
        $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => true), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                array('rank' => 'desc', 'startDate' => 'desc'), $limit, $page
        );

        return $actions;
    }

    /**
     * Called from app module.
     * 
     * @param type $urlKey
     * @return type
     */
    public static function fetchByUrlKey($urlKey)
    {
        return self::first(array('active = ?' => true, 'approved' => 1, 'urlKey = ?' => $urlKey));
    }

    /**
     * Return action states.
     * 
     * @return array
     */
    public static function getStates()
    {
        return self::$_statesConv;
    }

}
