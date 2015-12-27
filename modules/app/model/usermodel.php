<?php

namespace App\Model;

use THCFrame\Security\Model\BasicUserModel;

/**
 * 
 */
class UserModel extends BasicUserModel
{

    /**
     * Pole uživatelských rolí
     * @var array
     */
    private static $_avRoles = array(
        'role_superadmin' => 'Super Admin',
        'role_admin' => 'Admin',
        'role_participant' => 'Člen s přístupem do administrace',
        'role_member' => 'Člen',
    );

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 40
     *
     * @validate required, alphanumeric, min(3), max(40)
     * @label jméno
     */
    protected $_firstname;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 40
     *
     * @validate required, alphanumeric, min(3), max(40)
     * @label prijmeni
     */
    protected $_lastname;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 15
     * @validate numeric, max(15)
     * @label telefon
     */
    protected $_phoneNumber;

    /**
     * @column
     * @readwrite
     * @type text
     * @validate alphanumeric
     * @label personal info
     */
    protected $_personalData;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 50
     * @unique
     * @validate alphanumeric, max(50)
     * @label activation token
     */
    protected $_emailActivationToken;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * @default 0
     * @validate max(1)
     */
    protected $_getNewActionNotification;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * @default 0
     * @validate max(1)
     */
    protected $_team;

    /**
     * 
     */
    public function preSave()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary['raw'];

        if (empty($this->$raw)) {
            $this->setGetNewActionNotification(0);
            $this->setTeam(0);
        }

        parent::preSave();
    }

    /**
     * 
     * @return type
     */
    public static function getAllRoles()
    {
        return self::$_avRoles;
    }

    /**
     * @return type
     */
    public function getWholeName()
    {
        return $this->_firstname . ' ' . $this->_lastname;
    }

    /**
     * @return type
     */
    public function __toString()
    {
        $str = "Email: {$this->_email}";

        return $str;
    }

    /**
     * @return type
     */
    public static function fetchAll()
    {
        return self::all(
                array('role <> ?' => 'role_superadmin'), 
                array('id', 'firstname', 'lastname', 'email', 'role', 'active', 'created', 'blocked', 'deleted'), 
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
                array('role <> ?' => 'role_superadmin'), 
                array('id', 'firstname', 'lastname', 'email', 'role', 'active', 'created', 'blocked', 'deleted'), 
                array('created' => 'desc'), 
                (int) $limit
        );
    }

    /**
     * 
     * @return type
     */
    public static function fetchAdminsEmail()
    {
        $admins = self::all(array('role = ?' => 'role_admin', 'active = ?' => true, 'deleted = ?' => false, 'blocked = ?' => false), array('email'));

        $returnArr = array();
        if (!empty($admins)) {
            foreach ($admins as $admin) {
                $returnArr[] = $admin->getEmail();
            }
        }

        return $returnArr;
    }

}
