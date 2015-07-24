<?php

namespace THCFrame\Session;

use THCFrame\Core\Base;
use THCFrame\Events\Events as Event;
use THCFrame\Session\Exception;

/**
 * Session factory class
 */
class Session extends Base
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
     * @param string $method
     * @return \THCFrame\Session\Exception\Implementation
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
     * @return \THCFrame\Session\Session\Driver\Server
     * @throws Exception\Argument
     */
    public function initialize($configuration)
    {
        Event::fire('framework.session.initialize.before', array($this->type, $this->options));

        if (!$this->type) {
            if (!empty($configuration->session) && !empty($configuration->session->type)) {
                $this->type = $configuration->session->type;
                $this->options = (array) $configuration->session;
            } else {
                throw new \Exception('Error in configuration file');
            }
        }

        if (!$this->type) {
            throw new Exception\Argument('Invalid session type');
        }

        Event::fire('framework.session.initialize.after', array($this->type, $this->options));

        switch ($this->type) {
            case 'server': {
                    return new Driver\Server($this->options);
                    break;
                }
            case 'database': {
                    return new Driver\Database($this->options);
                    break;
                }
            default: {
                    throw new Exception\Argument('Invalid session type');
                    break;
                }
        }
    }

}
