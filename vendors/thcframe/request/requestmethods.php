<?php

namespace THCFrame\Request;

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
     * Get value from $_COOKIE array
     * 
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function cookie($key, $default = '')
    {
        if (!empty($_COOKIE[$key])) {
            return $_COOKIE[$key];
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
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
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
        $ua = get_browser();

        return json_encode($ua);
    }

    /**
     * 
     * @return null
     */
    public static function getHttpReferer()
    {
        if ($_SERVER['HTTP_REFERER'] === false) {
            return null;
        } else {
            return $_SERVER['HTTP_REFERER'];
        }
    }
}
