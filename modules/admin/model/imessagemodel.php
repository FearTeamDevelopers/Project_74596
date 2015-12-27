<?php

namespace Admin\Model;

use Admin\Model\Basic\BasicImessageModel;

/**
 * 
 */
class ImessageModel extends BasicImessageModel
{
    const TYPE_INFO = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;

    /**
     * @var array
     */
    private static $_typesConv = array(
        self::TYPE_INFO => 'Info',
        self::TYPE_WARNING => 'Warning',
        self::TYPE_ERROR => 'Error',
    );

    /**
     * @readwrite
     */
    protected $_alias = 'ims';

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
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('ims.*'))
                ->join('tb_user', 'ims.userId = us.id', 'us', array('us.firstname', 'us.lastname'));

        return self::initialize($query);
    }

    /**
     * @return array
     */
    public static function fetchActive()
    {
        return self::all(array('displayFrom <= ?' => date('Y-m-d', time()), 'displayTo >= ?' => date('Y-m-d', time()), 'active = ?' => true));
    }

    /**
     * Get imessage types.
     * 
     * @return array
     */
    public static function getTypes()
    {
        return self::$_typesConv;
    }
}
