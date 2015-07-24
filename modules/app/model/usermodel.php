<?php

namespace App\Model;

use THCFrame\Security\Model\BasicUser;

/**
 * 
 */
class UserModel extends BasicUser
{

    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     *
     * @validate required, alphanumeric, min(3), max(40)
     * @label jméno
     */
    protected $_firstname;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     *
     * @validate required, alphanumeric, min(3), max(40)
     * @label prijmeni
     */
    protected $_lastname;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 15
     * 
     * @validate numeric, max(15)
     * @label telefon
     */
    protected $_phoneNumber;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     *
     * @validate alphanumeric, max(50)
     * @label activation token
     */
    protected $_emailActivationToken;
    
    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate max(3)
     */
    protected $_getNewActionNotification;
    
    /**
     * 
     */
    public function preSave()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary['raw'];

        if (empty($this->$raw)) {
            $this->setCreated(date('Y-m-d H:i:s'));
            $this->setBlocked(false);
            $this->setLastLogin(0);
            $this->setTotalLoginAttempts(0);
            $this->setLastLoginAttempt(0);
            $this->setFirstLoginAttempt(0);
        }
        $this->setModified(date('Y-m-d H:i:s'));
    }
    
    /**
     * 
     * @return type
     */
    public function getWholeName()
    {
        return $this->_firstname . ' ' . $this->_lastname;
    }

    /**
     * 
     * @return type
     */
    public function __toString()
    {
        $str = "Id: {$this->_id} <br/>Email: {$this->_email} <br/> Name: {$this->_firstname} {$this->_lastname}";
        return $str;
    }
    
    /**
     * 
     * @return type
     */
    public static function fetchAll()
    {
        return self::all(
                array('role <> ?' => 'role_superadmin'), 
                array('id', 'firstname', 'lastname', 'email', 'role', 'active', 'created', 'blocked'), 
                array('id' => 'asc')
        );
    }

    /**
     * 
     * @param type $limit
     * @return type
     */
    public static function fetchLates($limit = 10)
    {
        return self::all(
                array('role <> ?' => 'role_superadmin', 'active = ?' => true, 'blocked = ?' => false), 
                array('id', 'firstname', 'lastname', 'email', 'role', 'active', 'created', 'blocked'), 
                array('created' => 'desc'),
                (int)$limit
        );
    }
}
