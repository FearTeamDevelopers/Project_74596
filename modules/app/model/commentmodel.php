<?php

namespace App\Model;

use App\Model\Basic\BasicCommentModel;

/**
 * 
 */
class CommentModel extends BasicCommentModel
{

    const RESOURCE_ACTION = 1;
    const RESOURCE_NEWS = 2;
    const RESOURCE_REPORT = 3;

    private static $_resourceConv = array(
        'action' => self::RESOURCE_ACTION,
        'news' => self::RESOURCE_NEWS,
        'report' => self::RESOURCE_REPORT,
    );

    /**
     * @readwrite
     */
    protected $_alias = 'cm';

    /**
     * @readwrite
     *
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
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', array('us.firstname', 'us.lastname'));

        return self::initialize($query);
    }

    /**
     * Called from admin module.
     * 
     * @return array
     */
    public static function fetchWithLimit($limit = 10)
    {
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                ->order('cm.created', 'desc')
                ->limit((int) $limit);

        return self::initialize($query);
    }

    /**
     * @param type $userId
     *
     * @return type
     */
    public static function fetchByUserId($userId)
    {
        return self::all(array('userId = ?' => (int) $userId), array('*'), array('created' => 'desc'));
    }

    /**
     * @param type $resourceId
     * @param type $type
     *
     * @return type
     */
    public static function fetchCommentsByResourceAndType($resourceId, $type, $limit = 15)
    {
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                ->where('cm.resourceId = ?', (int) $resourceId)
                ->where('cm.type = ?', (int) $type)
                ->where('cm.replyTo = ?', 0)
                ->order('cm.created', 'desc')
                ->limit((int) $limit);

        $comments = self::initialize($query);

        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $comment->_replies = self::fetchReplies($comment->getId());
            }
        }

        return $comments;
    }

    /**
     * @param type $actionId
     * @param type $created
     */
    public static function fetchByTypeAndCreated($type, $resourceId, $created, $limit = 15)
    {
        $types = array_values(self::$_resourceConv);

        if (!in_array($type, $types)) {
            return;
        }

        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                ->where('cm.resourceId = ?', (int) $resourceId)
                ->where('cm.created >= ?', (int) $created)
                ->where('cm.type = ?', (int) $type)
                ->order('cm.created', 'desc')
                ->limit((int) $limit);

        $comments = self::initialize($query);

        return $comments;
    }

    /**
     * @param type $id
     *
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
     * @return type
     */
    public function getReplies()
    {
        $query = self::getQuery(array('cm.*'))
                ->join('tb_user', 'cm.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                ->where('cm.replyTo = ?', $this->getId())
                ->order('cm.created', 'desc');

        return self::initialize($query);
    }

}
