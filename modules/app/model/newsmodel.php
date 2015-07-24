<?php

namespace App\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class NewsModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'nw';

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
     * @length 250
     * 
     * @validate alphanumeric, max(250)
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
     * @readwrite
     */
    protected $_fbLikeUrl;
    
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
        $query = self::getQuery(array('nw.*'))
                ->join('tb_user', 'nw.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'));
        
        return self::initialize($query);
    }

    /**
     * Called from admin module
     * @return array
     */
    public static function fetchWithLimit($limit = 10, $page = 1)
    {
        $query = self::getQuery(array('nw.*'))
                ->join('tb_user', 'nw.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->order('nw.created', 'desc')
                ->limit((int)$limit, $page);

        return self::initialize($query);
    }

    /**
     * Called from app module
     * @param type $limit
     * @return type
     */
    public static function fetchActiveWithLimit($limit = 10, $page = 1)
    {
        $news = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created'), 
                array('rank' => 'desc','created' => 'desc'), 
                $limit, $page
        );
        
        return $news;
    }
    
    /**
     * Called from app module
     * @param type $limit
     * @return type
     */
    public static function fetchArchivatedWithLimit($limit = 10, $page = 1)
    {
        $news = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => true), 
                array('urlKey', 'userAlias', 'title', 'shortBody', 'created'), 
                array('rank' => 'desc', 'created' => 'desc'), 
                $limit, $page
        );
        
        return $news;
    }
    
    /**
     * Called from app module
     * @param type $urlKey
     * @return type
     */
    public static function fetchByUrlKey($urlKey)
    {
        return self::first(array('active = ?' => true, 'approved' => 1, 'urlKey = ?' => $urlKey));
    }
}
