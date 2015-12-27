<?php

namespace Admin\Model;

use THCFrame\Registry\Registry;
use THCFrame\Events\Events as Event;
use THCFrame\Request\RequestMethods;
use Admin\Model\Basic\BasicActionhistoryModel;

/**
 * 
 */
class ActionHistoryModel extends BasicActionhistoryModel
{
    /**
     * @readwrite
     */
    protected $_alias = 'ach';

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
    }

    /**
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('ach.*'))
                ->join('tb_user', 'ach.editedBy = us.id', 'us',
                        array('us.firstname', 'us.lastname'));

        return self::initialize($query);
    }

    /**
     * Called from admin module.
     * 
     * @return array
     */
    public static function fetchWithLimit($limit = 10)
    {
        $query = self::getQuery(array('ach.*'))
                ->join('tb_user', 'ach.editedBy = us.id', 'us',
                        array('us.firstname', 'us.lastname'))
                ->order('ach.created', 'desc')
                ->limit((int) $limit);

        return self::initialize($query);
    }

    /**
     * Check differences between two objects.
     * 
     * @param \App\Model\ActionModel $original
     * @param \App\Model\ActionModel $edited
     */
    public static function logChanges(\App\Model\ActionModel $original, \App\Model\ActionModel $edited)
    {
        $sec = Registry::get('security');
        $user = $sec->getUser();

        $remoteAddr = RequestMethods::getClientIpAddress();
        $referer = RequestMethods::server('HTTP_REFERER');
        $changes = array();

        $reflect = new \ReflectionClass($original);
        $properties = $reflect->getProperties();
        $className = get_class($original);

        if (empty($properties)) {
            return;
        }

        foreach ($properties as $key => $value) {
            if(!preg_match('#.*@column.*#s', $value->getDocComment())){
                continue;
            }
            if ($value->class == $className) {
                $propertyName = $value->getName();
                $getProperty = 'get'.ucfirst(str_replace('_', '', $value->getName()));

                if (trim((string) $original->$getProperty()) !== trim((string) $edited->$getProperty())) {
                    $changes[$propertyName] = $original->$getProperty();
                }
            }
        }

        $historyRecord = new self(array(
            'originId' => $original->getId(),
            'editedBy' => $user->getId(),
            'remoteAddr' => $remoteAddr,
            'referer' => $referer,
            'changedData' => json_encode($changes),
        ));

        if ($historyRecord->validate()) {
            $historyRecord->save();
            Event::fire('admin.log', array('success', 'Action '.$original->getId().' changes saved'));
        } else {
            Event::fire('admin.log', array('fail', 'Action history errors: '.json_encode($historyRecord->getErrors())));
        }
    }
}
