<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;
use THCFrame\Security\PasswordManager;
use THCFrame\Security\Exception;
use THCFrame\Request\RequestMethods;
use THCFrame\Security\Model\AuthtokenModel;
use THCFrame\Core\Rand;
use THCFrame\Date\Date;
use THCFrame\Registry\Registry;
use THCFrame\Core\StringMethods;
use THCFrame\Request\CookieBag;

/**
 * Basic user class
 */
class BasicUserModel extends Model
{

    const ADMIN_PASS_STRENGHT = 0.5;
    const MEMBER_PASS_STRENGHT = 0.3;

    /**
     * Maximum time after which the user must re-login
     * approx 1 month
     * 
     * @var int
     */
    protected static $_rememberMeExpiryTime = 2592000;

    /**
     * 
     * @var int 
     */
    protected static $_accountBlockTime = 300;

    /**
     * @readwrite
     */
    protected $_alias = 'us';

    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     * @unsigned
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 60
     * @index
     * @unique
     *
     * @validate required, email, max(60)
     * @label email address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     * @index
     *
     * @validate required, max(200)
     * @label password
     */
    protected $_password;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 1
     * @validate max(1)
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 0
     * @validate max(1)
     * @label acc blocked
     */
    protected $_blocked;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 0
     * @validate max(1)
     */
    protected $_deleted;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 1
     * 
     * @default 0
     * @validate max(1)
     */
    protected $_forcePassChange;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 40
     * @unique
     *
     * @validate required, max(40)
     */
    protected $_salt;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 25
     * 
     * @validate required, alpha, max(25)
     * @label user role
     */
    protected $_role;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 10
     * @unsigned
     * 
     * @validate numeric, max(10)
     * @label last login
     */
    protected $_lastLogin;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * @length 3
     * @unsigned
     *
     * @validate numeric, max(3)
     * @label login attempt counter
     */
    protected $_totalLoginAttempts;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 10
     * @unsigned
     * 
     * @validate numeric, max(10)
     * @label last login attempt
     */
    protected $_lastLoginAttempt;

    /**
     * @column
     * @readwrite
     * @type int
     * @length 10
     * @unsigned
     * 
     * @validate numeric, max(10)
     * @label first login attempt
     */
    protected $_firstLoginAttempt;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     * @label pass expiration time
     */
    protected $_accountExpire;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     * @label pass expiration time
     */
    protected $_passExpire;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 15
     *
     * @validate numeric, max(15)
     */
    protected $_lastLoginIp;

    /**
     * @column
     * @readwrite
     * @type text
     * @null
     *
     * @validate alphanumeric, max(1000)
     */
    protected $_lastLoginBrowser;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_lastForcePassChange;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_lastPassChange;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     *
     * @validate max(200)
     * @label prev password1
     */
    protected $_passwordHistory1;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 200
     *
     * @validate max(200)
     * @label prev password2
     */
    protected $_passwordHistory2;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_modified;

    /**
     * @readwrite
     * @var type 
     */
    protected $_newCleanPassword;

    /**
     * Check if new password doesnt math to the previous
     * 
     * @param string $newPasswordHash
     * @return boolean
     */
    private function _checkPasswordHistory($newPasswordHash)
    {
        if ($newPasswordHash == $this->_passwordHistory1 || $newPasswordHash == $this->_passwordHistory2) {
            return false;
        }

        return true;
    }

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
            $this->setBlocked(false);
            $this->setDeleted(false);
            $this->setPassExipre();
            $this->setAccountExpire();
            $this->setLastLogin(0);
            $this->setTotalLoginAttempts(0);
            $this->setLastLoginAttempt(0);
            $this->setFirstLoginAttempt(0);
        }

        $this->setModified(date('Y-m-d H:i:s'));
    }

    /**
     * 
     */
    public function preUpdate()
    {
        $this->setModified(date('Y-m-d H:i:s'));
    }

    /**
     * 
     * @param type $value
     * @throws \THCFrame\Security\Exception\Role
     */
    public function setRole($value)
    {
        $role = strtolower(substr($value, 0, 5));
        if ($role != 'role_') {
            throw new Exception\Role(sprintf('Role %s is not valid', $value));
        } else {
            $this->_role = $value;
        }

        return $this;
    }

    /**
     * Set user last login
     */
    public function setLastLogin($time = null)
    {
        if ($time === null) {
            $this->_lastLogin = time();
        } else {
            $this->_lastLogin = $time;
        }

        return $this;
    }

    /**
     * 
     * @return \THCFrame\Security\Model\BasicUserModel
     */
    public function setPassExipre()
    {
        $passExpiration = Registry::get('configuration')->security->passwordExpiration;

        if ((int) $passExpiration === 0) {
            $this->_passExpire = '3000-01-01 00:00:00';
        } else {
            $passExp = Date::getInstance()->dateAdd(date('Y-m-d'), 'Y-m-d H:i:s', 0, 0, $passExpiration);
            $this->_passExpire = $passExp;
        }

        return $this;
    }

    /**
     * 
     * @return \THCFrame\Security\Model\BasicUserModel
     */
    public function setAccountExpire()
    {
        $accExpiration = Registry::get('configuration')->security->accountExpiration;

        if ((int) $accExpiration === 0) {
            $this->_accountExpire = '3000-01-01 00:00:00';
        } else {
            $accExpirationDate = Date::getInstance()->dateAdd(date('Y-m-d'), 'Y-m-d H:i:s', 0, 0, $accExpiration);
            $this->_accountExpire = $accExpirationDate;
        }

        return $this;
    }

    /**
     * Function to activate the account
     */
    public function activateAccount()
    {
        $this->_active = true;

        if ($this->validate()) {
            $this->save();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to deactivate the account
     */
    public function deactivateAccount()
    {
        $this->_active = false;

        if ($this->validate()) {
            $this->save();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to check if the user's account is active or not
     * 
     * @return boolean
     */
    public function isActive()
    {
        return (boolean) $this->_active;
    }

    /**
     * Function to check if the user's account is blocked or not
     * 
     * @return boolean
     */
    public function isBlocked()
    {
        $currentTime = time();

        if ((boolean) $this->_blocked === true) {
            if (($currentTime - $this->getLastLoginAttempt()) >= self::$_accountBlockTime) {
                return false;
            } else {
                return (boolean) $this->_blocked;
            }
        } else {
            return (boolean) $this->_blocked;
        }
    }

    /**
     * To check if the password has aged. i.e. if the time has passed 
     * after which the password must be changed.
     * 
     * @return boolean
     */
    public function isPasswordExpired()
    {
        $passExp = Registry::get('configuration')->security->passwordExpiration;
        if ((int) $passExp === 0) {
            return false;
        }

        $currentTime = time();
        $passExpire = Date::getInstance()->getTimestamp($this->passExpire);

        if (($passExpire - $currentTime) < (24 * 3600)) {
            $this->setForcePassChange(true);
            $this->update();
            return false;
        } elseif (($passExpire - $currentTime) < 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @return boolean
     */
    public function getDaysToPassExpiration()
    {
        $passExp = Registry::get('configuration')->security->passwordExpiration;
        if ((int) $passExp === 0) {
            return false;
        }

        return Date::getInstance()->datediff(date('Y-m-d'), $this->passExpire);
    }

    /**
     * To check if the account has aged. i.e. if the time has passed 
     * after which the account will be blocked.
     * 
     * @return boolean
     */
    public function isAccountExpired()
    {
        $accExp = Registry::get('configuration')->security->accountExpiration;
        if ((int) $accExp === 0) {
            return false;
        }

        $currentTime = time();
        $accExpire = Date::getInstance()->getTimestamp($this->accountExpire);

        if (($accExpire - $currentTime) < 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $oldPassword
     * @param type $newPassword
     * @param type $passStrength
     * @return \THCFrame\Security\Model\BasicUserModel
     * @throws Exception\WrongPassword
     * @throws Exception\WeakPassword
     */
    public function changePassword($oldPassword, $newPassword, $passStrength = null)
    {
        if (!PasswordManager::validatePassword($oldPassword, $this->getPassword(), $this->getSalt())) {
            throw new Exception\WrongPassword('Wrong Password provided');
        }

        if (null === $passStrength) {
            if ($this->getRole() == 'role_member') {
                $passStrength = self::MEMBER_PASS_STRENGHT;
            } else {
                $passStrength = self::ADMIN_PASS_STRENGHT;
            }
        }

        if (PasswordManager::strength($newPassword) <= $passStrength) {
            throw new Exception\WeakPassword('Password is too weak');
        }

        $cleanHash = StringMethods::getHash($newPassword);
        $this->_passwordHistory2 = $this->_passwordHistory1;
        $this->_passwordHistory1 = $cleanHash;

        $this->forcePassChange = false;
        $this->salt = PasswordManager::createSalt();
        $this->password = PasswordManager::hashPassword($newPassword, $this->getSalt());
        $this->lastPassChange = Date::getInstance()->getFormatedCurDatetime('system');
        $this->setPassExipre();

        $checkPassHistory = Registry::get('configuration')->security->checkPasswordHistory;
        if ((int) $checkPassHistory === 0) {
            return $this;
        } else {
            if (!$this->_checkPasswordHistory($cleanHash)) {
                throw new Exception\PasswordInHistory('Password must be different than previous two');
            } else {
                return $this;
            }
        }
    }

    /**
     * Force password reset for user
     * 
     * @param type $newPassword
     * @return boolean
     */
    public function forceResetPassword($newPassword = null, $passStrength = null)
    {
        if (null === $passStrength) {
            if ($this->getRole() == 'role_member') {
                $passStrength = self::MEMBER_PASS_STRENGHT;
            } else {
                $passStrength = self::ADMIN_PASS_STRENGHT;
            }
        }
        if (null === $newPassword) {
            $newPassword = PasswordManager::generate($passStrength);
        }

        if (PasswordManager::strength($newPassword) <= $passStrength) {
            throw new Exception\WeakPassword('Password is too weak');
        }

        $this->_newCleanPassword = $newPassword;

        $cleanHash = StringMethods::getHash($newPassword);
        $this->_passwordHistory2 = $this->_passwordHistory1;
        $this->_passwordHistory1 = $cleanHash;

        $this->salt = PasswordManager::createSalt();
        $this->password = PasswordManager::hashPassword($newPassword, $this->getSalt());
        $this->forcePassChange = true;
        $this->lastForcePassChange = Date::getInstance()->getFormatedCurDatetime('system');
        $this->setPassExipre();

        return $this;
    }

    /**
     * Function to enable 'Remember Me' functionality
     * 
     * @param type $userID
     * @param type $secure
     * @param type $httpOnly
     * @return boolean
     */
    public static function enableRememberMe($userID, $secure = TRUE, $httpOnly = TRUE)
    {
        $authID = Rand::randStr(128);
        $cookieBag = CookieBag::getInstance();

        $token = new AuthtokenModel(array(
            'userId' => $userID,
            'token' => $authID
        ));

        if ($token->validate()) {
            $token->save();

            if ($secure && $httpOnly) {
                $cookieBag->set('AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, TRUE, TRUE);
            } elseif (!$secure && !$httpOnly) {
                $cookieBag->set('AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, FALSE, FALSE);
            } elseif ($secure && !$httpOnly) {
                $cookieBag->set('AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, TRUE, FALSE);
            } elseif (!$secure && $httpOnly) {
                $cookieBag->set('AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, FALSE, TRUE);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to check for AUTH token validity
     * 
     * @return boolean
     */
    public static function checkRememberMe()
    {
        $cookieBag = CookieBag::getInstance();

        if ($cookieBag->get('AUTHID') != '') {
            $token = AuthtokenModel::first(array('token = ?' => RequestMethods::cookie('THCF_AUTHID')));

            if ($token !== null) {
                $currentTime = time();

                //If cookie time has expired, then delete the cookie from the DB and the user's browser.
                if (($currentTime - $token->created) >= static::$_rememberMeExpiryTime) {
                    static::deleteAuthenticationToken();
                    return false;
                } else {
                    //The AUTH token is correct and valid. Hence, return the userID related to this AUTH token
                    return $token->userId;
                }
            } else {
                //If this AUTH token is not found in DB, then erase the cookie from the client's machine and return FALSE
                $cookieBag->erase('AUTHID');
                return false;
            }
        } else {
            //If the user is unable to provide a AUTH token, then return FALSE
            return false;
        }
    }

    /**
     * Function to delete the current user authentication token from the DB and user cookies
     */
    public static function deleteAuthenticationToken()
    {
        $cookieBag = CookieBag::getInstance();

        if ($cookieBag->get('AUTHID') != '') {
            AuthtokenModel::deleteAll(array('token = ?' => RequestMethods::cookie('THCF_AUTHID')));
            $cookieBag->erase('AUTHID');
        }
    }

}
