<?php

namespace App\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class ActionModel extends Model
{

    const TYPE_SELECT = 0;
    const TYPE_TRAINING = 1;
    const TYPE_MATCH = 2;
    const TYPE_PARTY = 3;
    
    const STATE_WAITING = 0;
    const STATE_APPROVED = 1;
    const STATE_REJECTED = 2;
    
    /**
     *
     * @var array 
     */
    private static $_typesConv = array(
        self::TYPE_SELECT => '-- Vybrat --',
        self::TYPE_TRAINING => 'Trénink',
        self::TYPE_MATCH => 'Zápas',
        self::TYPE_PARTY => 'Party Hard'
    );
    
    /**
     *
     * @var type 
     */
    private static $_statesConv = array(
        self::STATE_WAITING => 'Čeká na shválení',
        self::STATE_APPROVED => 'Schváleno',
        self::STATE_REJECTED => 'Zamítnuto'
    );
    
    /**
     * @readwrite
     */
    protected $_alias = 'ac';

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
     * @label id autora
     */
    protected $_userId;

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
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_approved;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_archive;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @index
     * 
     * @validate max(3)
     */
    protected $_actionType;
    
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
     * @label teaser
     */
    protected $_shortBody;

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
     * @type tinyint
     * 
     * @validate numeric, max(2)
     * @label pořadí
     */
    protected $_rank;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 12
     * 
     * @validate date, max(12)
     * @label datum začátek
     */
    protected $_startDate;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 12
     * 
     * @validate date, max(12)
     * @label datum konec
     */
    protected $_endDate;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     * 
     * @validate time, max(10)
     * @label čas začátek
     */
    protected $_startTime;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     * 
     * @validate time, max(10)
     * @label čas konec
     */
    protected $_endTime;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 350
     * 
     * @validate alphanumeric, max(350)
     * @label keywords
     */
    protected $_keywords;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate alphanumeric, max(150)
     * @label meta-název
     */
    protected $_metaTitle;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate alphanumeric
     * @label meta-popis
     */
    protected $_metaDescription;
    
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
        
        $shortText = preg_replace('/https:/i', 'http:', $this->getShortBody());
        $text = preg_replace('/https:/i', 'http:', $this->getBody());
        $this->setShortBody($shortText);
        $this->setBody($text);
        $this->setModified(date('Y-m-d H:i:s'));
    }

    /**
     * 
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('ac.*'))
                ->join('tb_user', 'ac.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'));
        
        return self::initialize($query);
    }

    /**
     * Called from admin module
     * 
     * @return array
     */
    public static function fetchWithLimit($limit = 10)
    {
        $query = self::getQuery(array('ac.*'))
                ->join('tb_user', 'ac.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->order('ac.created', 'desc')
                ->limit((int)$limit);

        return self::initialize($query);
    }
    
    /**
     * Called from app module
     * 
     * @param type $limit
     * @return type
     */
    public static function fetchActiveWithLimit($limit = 10, $page = 1)
    {
        $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false, 'startDate >= ?' => date('Y-m-d', time())), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                array('rank' => 'desc', 'startDate' => 'asc'), 
                $limit, $page
        );
        
        return $actions;
    }

    /**
     * Called from app module
     * 
     * @param type $limit
     * @return type
     */
    public static function fetchOldWithLimit($limit = 10, $page = 1)
    {
        $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false, 'startDate <= ?' => date('Y-m-d', time())), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                array('rank' => 'desc', 'startDate' => 'desc'), 
                $limit, $page
        );
        
        return $actions;
    }
    
    /**
     * Called from app module
     * 
     * @param type $limit
     * @return type
     */
    public static function fetchArchivatedWithLimit($limit = 10, $page = 1)
    {
        $actions = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => true), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created', 'startDate'), 
                array('rank' => 'desc', 'startDate' => 'desc'), 
                $limit, $page
        );
        
        return $actions;
    }
    
    /**
     * Called from app module
     * 
     * @param type $urlKey
     * @return type
     */
    public static function fetchByUrlKey($urlKey)
    {
        return self::first(array('active = ?' => true, 'approved' => 1, 'urlKey = ?' => $urlKey));
    }
    
    /**
     * Return action types
     * 
     * @return array
     */
    public static function getTypes()
    {
        return self::$_typesConv;
    }
    
    /**
     * Return action states
     * 
     * @return array
     */
    public static function getStates()
    {
        return self::$_statesConv;
    }
}
