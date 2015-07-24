<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;
use THCFrame\Security\PasswordManager;
use THCFrame\Security\Exception;
use THCFrame\Request\RequestMethods;
use THCFrame\Security\Model\Authtoken;
use THCFrame\Core\Rand;
use THCFrame\Date\Date;
use THCFrame\Registry\Registry;

/**
 * Basic user class
 */
class BasicUser extends Model
{

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
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type text
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
     * @type text
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
     * @label acc blocked
     */
    protected $_blocked;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_deleted;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_forcePassChange;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     * @unique
     *
     * @validate required, max(40)
     */
    protected $_salt;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 25
     * 
     * @validate required, alpha, max(25)
     * @label user role
     */
    protected $_role;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric
     * @label last login
     */
    protected $_lastLogin;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate numeric
     * @label login attempt counter
     */
    protected $_totalLoginAttempts;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate numeric
     * @label last login attempt
     */
    protected $_lastLoginAttempt;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate numeric
     * @label first login attempt
     */
    protected $_firstLoginAttempt;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     * @label pass expiration time
     */
    protected $_accountExpire;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     * @label pass expiration time
     */
    protected $_passExpire;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     *
     * @validate numeric, max(20)
     */
    protected $_lastLoginIp;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     *
     * @validate alphanumeric, max(1000)
     */
    protected $_lastLoginBrowser;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     */
    protected $_lastForcePassChange;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     */
    protected $_lastPassChange;

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
            $this->setBlocked(false);
            $this->setDeleted(false);
            $this->setPassExipre();
            $this->setAccountExpire();
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
     * @return \THCFrame\Security\Model\BasicUser
     */
    public function setPassExipre()
    {
        $passExpiration = Registry::get('configuration')->security->passwordExpiration;

        if ((int) $passExpiration === 0) {
            $this->_passExpire = '3000-01-01 00:00:00';
        } else {
            $passExp = Date::getInstance()->dateAdd(time(), 'Y-m-d', 0, 0, $passExpiration);
            $this->_passExpire = $passExp;
        }
        
        return $this;
    }
    
    /**
     * 
     * @return \THCFrame\Security\Model\BasicUser
     */
    public function setAccountExpire()
    {
        $accExpiration = Registry::get('configuration')->security->accountExpiration;

        if ((int) $accExpiration === 0) {
            $this->_accountExpire = '3000-01-01 00:00:00';
        } else {
            $accExpirationDate = Date::getInstance()->dateAdd(time(), 'Y-m-d', 0, 0, $accExpiration);
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
        if ((int)$passExp === 0) {
            return false;
        }

        $currentTime = time();
        $passExpire = Date::getInstance()->getTimestamp($this->passExpire);

        if (($passExpire - $currentTime) < 0) {
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
        if ((int)$passExp === 0) {
            return false;
        }

        return Date::getInstance()->datediff($this->passExpire, time(), false);
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
        if ((int)$accExp === 0) {
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
     * @return \THCFrame\Security\Model\BasicUser
     * @throws Exception\WrongPassword
     * @throws Exception\WeakPassword
     */        
    public function changePassword($oldPassword, $newPassword, $passStrength = 0.5)
    {
        if (!PasswordManager::validatePassword($oldPassword, $this->getPassword(), $this->getSalt())) {
            throw new Exception\WrongPassword('Wrong Password provided');
        }

        if (PasswordManager::strength($newPassword) <= $passStrength) {
            throw new Exception\WeakPassword('Password is too weak');
        }
        
        $this->salt = PasswordManager::createSalt();
        $this->password = PasswordManager::hashPassword($newPassword, $this->getSalt());
        $this->lastPassChange = Date::getInstance()->getFormatedCurDatetime('system');
        $this->setPassExipre();

        return $this;
    }

    /**
     * Function to reset the password for the current user
     * 
     * @param type $oldPassword
     * @param type $newPassword
     * @return boolean
     * @throws Exception\WrongPassword
     */
    public function resetPassword($oldPassword, $newPassword)
    {
        if (!PasswordManager::validatePassword($oldPassword, $this->getPassword(), $this->getSalt())) {
            throw new Exception\WrongPassword('Wrong Password provided');
        }

        $this->salt = PasswordManager::createSalt();
        $this->password = PasswordManager::hashPassword($newPassword, $this->getSalt());
        $this->lastPassChange = Date::getInstance()->getFormatedCurDatetime('system');
        $this->setPassExipre();

        if ($this->validate()) {
            $this->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Force password reset for user
     * 
     * @param type $newPassword
     * @return boolean
     */
    public function forceResetPassword($newPassword = null, $passStrength = 0.5)
    {
        if (null === $newPassword) {
            $newPassword = PasswordManager::generate($passStrength);
        }
        
        if (PasswordManager::strength($newPassword) <= $passStrength) {
            throw new Exception\WeakPassword('Password is too weak');
        }

        $this->salt = PasswordManager::createSalt();
        $this->password = PasswordManager::hashPassword($newPassword, $this->getSalt());
        $this->forcePassChange = 1;
        $this->lastForcePassChange = Date::getInstance()->getFormatedCurDatetime('system');
        $this->setPassExipre();

        if ($this->validate()) {
            $this->save();
            return $newPassword;
        } else {
            return false;
        }
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

        $token = new Authtoken(array(
            'userId' => $userID,
            'token' => $authID
        ));

        if ($token->validate()) {
            $token->save();

            if ($secure && $httpOnly) {
                \setcookie('THCF_AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, TRUE, TRUE);
            } elseif (!$secure && !$httpOnly) {
                \setcookie('THCF_AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, FALSE, FALSE);
            } elseif ($secure && !$httpOnly) {
                \setcookie('THCF_AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, TRUE, FALSE);
            } elseif (!$secure && $httpOnly) {
                \setcookie('THCF_AUTHID', $authID, time() + static::$_rememberMeExpiryTime, null, null, FALSE, TRUE);
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
        if (RequestMethods::cookie('THCF_AUTHID') != '') {
            $token = Authtoken::first(array('token = ?' => RequestMethods::cookie('THCF_AUTHID')));

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
                \setcookie('THCF_AUTHID', '');
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
        if (RequestMethods::cookie('THCF_AUTHID') != '') {
            Authtoken::deleteAll(array('token = ?' => RequestMethods::cookie('THCF_AUTHID')));
            \setcookie('THCF_AUTHID', '', time() - 1800);
        }
    }

}
