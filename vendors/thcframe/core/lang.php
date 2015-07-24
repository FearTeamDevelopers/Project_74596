<?php

namespace THCFrame\Core;

use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Core\StringMethods;

/**
 * Class controlling translates
 */
class Lang
{

    private $_customTranslates = array();
    private $_defaultMessage;
    public static $instance = null;

    public function __construct()
    {
        Event::fire('framework.lang.initialize.before', array());

        $defaultLang = Registry::get('configuration')->system->lang;

        if (file_exists(APP_PATH . '/lang/' . $defaultLang . '.php')) {
            $custom = include (APP_PATH . '/lang/' . $defaultLang . '.php');
        }

        if (!is_array($custom)) {
            throw new \Exception('Lang file content is not array');
        }

        $prepared = array();
        foreach ($custom as $key => $value) {
            $key = trim(StringMethods::removeDiacriticalMarks($key));
            $key = strtoupper(str_replace(' ', '_', $key));
            $prepared[$key] = $value;
        }
        
        unset($custom);

        if (isset($prepared['defaultMessage'])) {
            $this->_defaultMessage = $custom['defaultMessage'];
            unset($prepared['defaultMessage']);
        } else {
            $this->_defaultMessage = 'Translate not found';
        }

        $this->_customTranslates = $prepared;

        Event::fire('framework.lang.initialize.after', array($defaultLang));
    }

    /**
     * 
     * @return type
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 
     * @param type $key
     * @param type $args
     * @return type
     */
    public static function get($key, $args = array())
    {
        $lang = new static();
        return $lang->_get($key, $args);
    }

    /**
     * 
     */
    public function _get($key, $args = array())
    {
        $key = trim(StringMethods::removeDiacriticalMarks($key));
        $key = strtoupper(str_replace(' ', '_', $key));

        if (isset($this->_customTranslates[$key])) {
            if (!empty($args) && is_array($args)) {
                if (strpos($this->_customTranslates[$key], '%s') !== false) {
                    return vsprintf($this->_customTranslates[$key], $args);
                }
            }

            return $this->_customTranslates[$key];
        }

        return $this->_defaultMessage;
    }

}
