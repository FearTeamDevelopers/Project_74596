<?php

namespace THCFrame\Configuration;

use THCFrame\Core\Base;
use THCFrame\Events\Events as Event;
use THCFrame\Configuration\Exception;

/**
 * Configuration factory class
 */
class Configuration extends Base
{

    /**
     * @readwrite
     */
    protected $_type;

    /**
     * @readwrite
     */
    protected $_options;

    /**
     * 
     * @param type $method
     * @return \THCFrame\Configuration\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf('%s method not implemented', $method));
    }
    
    /**
     * Factory method
     * It accepts initialization options and selects the type of returned object, 
     * based on the internal $_type property.
     * 
     * @return \THCFrame\Configuration\Configuration\Driver\Ini
     * @throws Exception\Argument
     */
    public function initialize()
    {
        Event::fire('framework.configuration.initialize.before', array($this->_type, $this->_options));

        if (!$this->_type) {
            throw new Exception\Argument('Invalid type');
        }

        Event::fire('framework.configuration.initialize.after', array($this->_type, $this->_options));

        switch ($this->_type) {
            case 'ini': {
                    return new Driver\Ini($this->_options);
                    break;
                }
            default: {
                    throw new Exception\Argument('Invalid type');
                    break;
                }
        }
    }

}
