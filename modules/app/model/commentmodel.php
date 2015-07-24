<?php

namespace App\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class CommentModel extends Model
{

    const RESOURCE_ACTION = 1;
    const RESOURCE_NEWS = 2;

    private static $_resourceConv = array(
        'action' => self::RESOURCE_ACTION,
        'news' => self::RESOURCE_NEWS
    );

    /**
     * @readwrite
     */
    protected $_alias = 'cm';

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
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label id objektu
     */
    protected $_resourceId;

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
     * @var array
     */
    public $_replies;

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
    public static function fetchAll()
    {
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', 
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
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->order('cm.created', 'desc')
                ->limit((int) $limit);

        return self::initialize($query);
    }

    /**
     * 
     * @param type $userId
     * @return type
     */
    public static function fetchByUserId($userId)
    {
        return self::all(array('userId = ?' => (int) $userId), 
                array('*'),
                array('created' => 'desc'));
    }

    /**
     * 
     * @param type $resourceId
     * @param type $type
     * @return type
     */
    public static function fetchCommentsByResourceAndType($resourceId, $type)
    {
        if (!array_key_exists(strtolower($type), self::$_resourceConv)) {
            return null;
        }

        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->where('cm.resourceId = ?', (int) $resourceId)
                ->where('cm.type = ?', self::$_resourceConv[$type])
                ->where('cm.replyTo = ?', 0)
                ->order('cm.created', 'desc');

        $comments = self::initialize($query);

        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $comment->_replies = self::fetchReplies($comment->getId());
            }
        }

        return $comments;
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public static function fetchReplies($id)
    {
        $comment = new self(array('id' => $id));

        $comment->_replies = $comment->getReplies();

        if ($comment->_replies !== null) {
            foreach ($comment->_replies as $cm) {
                $cm->_replies = self::fetchReplies($cm->getId());
            }
        }

        return $comment->_replies;
    }

    /**
     * 
     * @return type
     */
    public function getReplies()
    {
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->where('cm.replyTo = ?', $this->getId())
                ->order('cm.created', 'desc');

        return self::initialize($query);
    }

}
