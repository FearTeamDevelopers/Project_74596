<?php

namespace THCFrame\Request;

use THCFrame\Core\BagInterface;
use THCFrame\Registry\Registry;

/**
 * Description of cookie
 *
 * @author Tomy
 */
class CookieBag implements BagInterface
{

    private static $_instance = null;
    
    private $_prefix = 'THCF_';

    /**
     * 
     * @return type
     */
    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::$_instance = new static();
        }
        
        return self::$_instance;
    }

    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    public function clear()
    {
        if(!empty($_COOKIE)){
            foreach ($_COOKIE as $key => $cookie){
                unset($_COOKIE[$key]);
                setcookie($this->_prefix.$key, '', time()-1800, '/', null, false, true);
            }
        }
    }

    public function erase($key)
    {
        $key = $this->hashKey($key);
        
        if(isset($_COOKIE[$this->_prefix.$key])){
            unset($_COOKIE[$this->_prefix.$key]);
            setcookie($this->_prefix.$key, '', time()-1800, '/', null, false, true);
        }
    }

    public function get($key, $default = null)
    {
        $key = $this->hashKey($key);
        
        if(!empty($_COOKIE[$this->_prefix.$key])){
            return $_COOKIE[$this->_prefix.$key];
        }
        
        return $default;
    }

    public function hashKey($key)
    {
        $secret = Registry::get('configuration')->session->secret;
        return hash_hmac('sha512', $key, $secret);
    }

    public function set($key, $value, $exp = null, $path = '/', $domain = null, $secure = false, $httponly = true)
    {
        $key = $this->hashKey($key);
        
        if($exp === null){
            $exp = time() + 4*3600;
        }
        
        $_COOKIE[$this->_prefix.$key] = $value;
        setcookie($this->_prefix.$key, $value, $exp, $path, $domain, $secure, $httponly);
        
        return $this;
    }

}
