<?php

namespace THCFrame\Request;

use THCFrame\Registry\Registry;
use THCFrame\Request\CookieBag;

/**
 * Request methods wrapper class
 */
class RequestMethods
{

    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    /**
     * Get value from $_GET array
     * 
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = '')
    {
        if (isset($_GET[$key]) && (!empty($_GET[$key]) || is_numeric($_GET[$key]))) {
            return $_GET[$key];
        }
        return $default;
    }

    /**
     * Check if key is in $_GET array
     * 
     * @param mixed $key
     * @return boolean
     */
    public static function issetget($key)
    {
        if (isset($_GET[$key])) {
            return true;
        }
        return false;
    }

    /**
     * Get value from $_POST array
     * 
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function post($key, $default = '')
    {
        if (isset($_POST[$key]) && (!empty($_POST[$key]) || is_numeric($_POST[$key]))) {
            return $_POST[$key];
        }
        return $default;
    }

    /**
     * Check if key is in $_POST array
     * 
     * @param mixed $key
     * @return boolean
     */
    public static function issetpost($key)
    {
        if (isset($_POST[$key])) {
            return true;
        }
        return false;
    }

    /**
     * Get value from $_SERVER array
     * 
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function server($key, $default = '')
    {
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        return $default;
    }

    /**
     * Check if key is in $_POST array
     * 
     * @param mixed $key
     * @return boolean
     */
    public static function issetserver($key)
    {
        if (isset($_SERVER[$key])) {
            return true;
        }
        return false;
    }
    
    /**
     * Get value from $_COOKIE array
     * 
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function cookie($key, $default = '')
    {
        $cookieBag = CookieBag::getInstance();
        
        if($cookieBag->get($key) !== null){
           return $cookieBag->get($key);
        }
        
        return $default;
    }

    /**
     * Return client ip address
     * 
     * @return string
     */
    public static function getClientIpAddress()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    /**
     * Return client browser identification
     * 
     * @return string
     */
    public static function getBrowser()
    {
        $browser = Registry::get('browser');
        return $browser->getBrowser().' '.$browser->getVersion().' '.$browser->getPlatform().' '.$browser->getUserAgent();
    }

    /**
     * 
     * @return null
     */
    public static function getHttpReferer()
    {
        if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] === false || $_SERVER['HTTP_REFERER'] === '') {
            return '';
        } else {
            return $_SERVER['HTTP_REFERER'];
        }
    }
}
