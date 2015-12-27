<?php

namespace App\Model;

use App\Model\Basic\BasicNewsModel;

/**
 * 
 */
class NewsModel extends BasicNewsModel
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
    protected $_alias = 'nw';

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
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('nw.*'))
                ->join('tb_user', 'nw.userId = us.id', 'us', array('us.firstname', 'us.lastname'));

        return self::initialize($query);
    }

    /**
     * Called from admin module.
     *
     * @return array
     */
    public static function fetchWithLimit($limit = 10, $page = 1)
    {
        $query = self::getQuery(array('nw.*'))
                ->join('tb_user', 'nw.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                ->order('nw.created', 'desc')
                ->limit((int) $limit, $page);

        return self::initialize($query);
    }

    /**
     * Called from app module.
     *
     * @param type $limit
     *
     * @return type
     */
    public static function fetchActiveWithLimit($limit = 10, $page = 1)
    {
        $news = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => false), array('urlKey', 'userAlias', 'title', 'shortBody', 'created'), array('rank' => 'desc', 'created' => 'desc'), $limit, $page
        );

        return $news;
    }

    /**
     * Called from app module.
     *
     * @param type $limit
     *
     * @return type
     */
    public static function fetchArchivatedWithLimit($limit = 10, $page = 1)
    {
        $news = self::all(array('active = ?' => true, 'approved = ?' => 1, 'archive = ?' => true), array('urlKey', 'userAlias', 'title', 'shortBody', 'created'), array('rank' => 'desc', 'created' => 'desc'), $limit, $page
        );

        return $news;
    }

    /**
     * Called from app module.
     *
     * @param type $urlKey
     *
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
