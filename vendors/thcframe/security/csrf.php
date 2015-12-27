<?php

namespace THCFrame\Security;

use THCFrame\Registry\Registry;
use THCFrame\Request\RequestMethods;
use THCFrame\Core\Rand;

/**
 * Cross-site Request Forgery protection
 */
class CSRF
{

    /**
     * Token name
     * 
     * @var string 
     */
    protected static $_tokenname = 'csrf';

    /**
     * Session object
     * 
     * @var THCFrame\Session\Driver 
     */
    private $_session;

    /**
     * CSRF token
     * 
     * @var string
     */
    private $_token;

    /**
     * 
     * @param string $tokenname
     */
    public function __construct(\THCFrame\Session\Driver $sesion)
    {
        $this->_session = $sesion;

        $this->_token = Rand::randStr(32);
        $this->setToken($this->_token);
    }

    /**
     * Generates the HTML input field with the token
     */
    public function generateHiddenField()
    {
        return '<input type="hidden" name="' . self::$_tokenname . '" value="' . $this->getToken() . '" />';
    }

    /**
     * Verifies whether the post token was set, else dies with error
     * 
     * @return boolean
     */
    public function verifyRequest()
    {
        $checkPost = RequestMethods::issetpost(self::$_tokenname) && (RequestMethods::post(self::$_tokenname) === $this->getTokenFromSession());
        $checkGet = RequestMethods::issetget(self::$_tokenname) && (RequestMethods::get(self::$_tokenname) === $this->getTokenFromSession());

        $newToken = Rand::randStr(32);
        $this->eraseToken()
                ->setToken($newToken);

        if ($checkGet || $checkPost) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return token value
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Return token value from session
     * 
     * @return string
     */
    public function getTokenFromSession()
    {
        return $this->_session->get(self::$_tokenname);
    }

    /**
     * Set token value and store it in session
     * 
     * @param string $token
     * @return \THCFrame\Security\CSRF
     */
    public function setToken($token)
    {
        $this->_token = $token;
        $this->_session->set(self::$_tokenname, $token);

        return $this;
    }

    /**
     * Set token value to null and delete it from session
     * 
     * @return \THCFrame\Security\CSRF
     */
    public function eraseToken()
    {
        $this->_token = null;
        $this->_session->erase(self::$_tokenname);

        return $this;
    }

    /**
     * Return tokenname
     * 
     * @return string
     */
    public function getTokenname()
    {
        return self::$_tokenname;
    }

}
