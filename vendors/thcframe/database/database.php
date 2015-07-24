<?php

namespace THCFrame\Database;

use THCFrame\Core\Base;
use THCFrame\Events\Events as Event;
use THCFrame\Database\Exception;
use THCFrame\Database\ConnectionHandler;

/**
 * Factory class returns a Database\Connector subclass.
 * Connectors are the classes that do the actual interfacing with the 
 * specific database engine. They execute queries and return data
 */
class Database extends Base
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
     * @param \THCFrame\Configuration\Driver $configuration
     * @return \THCFrame\Database\Database\Connector
     * @throws Exception\Argument
     */
    public function initialize($configuration)
    {
        Event::fire('framework.database.initialize.before', array());

        $databases = $configuration->database;
        $conHandler = new ConnectionHandler();

        if (!empty($databases)) {
            foreach ($databases as $dbIdent) {
                if (!empty($dbIdent) && !empty($dbIdent->type)) {
                    $type = $dbIdent->type;
                    $options = (array) $dbIdent;
                } else {
                    throw new Exception\Argument('Error in configuration file');
                }

                try {
                    $connector = $this->createConnector($type, $options);
                    $conHandler->add($dbIdent->id, $connector);
                    $connector->connect();
                } catch (Exception $exc) {
                    throw new Exception\Connector($exc->getMessage());
                }

                Event::fire('framework.database.initialize.after', array($type, $options));
            }
        }

        return $conHandler;
    }

    /**
     * 
     * @param array     $options
     * @return \THCFrame\Database\Database\Connector
     * @throws Exception\Argument
     */
    public function initializeDirectly($options)
    {
        if (!empty($options['type'])) {
            $type = $options['type'];
            $options = (array) $options;
        } else {
            throw new Exception\Argument('Error in configuration');
        }

        $connector = $this->createConnector($type, $options);
        $connector->connect();

        return $connector;
    }

    /**
     * 
     * @param type $type
     * @param type $options
     * @return \THCFrame\Database\Connector\Mysql
     * @throws Exception\Argument
     */
    private function createConnector($type = 'mysql', $options = array())
    {
        if (empty($options)) {
            throw new Exception\Argument('Invalid database options');
        }

        switch ($type) {
            case 'mysql': {
                    return new Connector\Mysql($options);
                    break;
                }
            default: {
                    throw new Exception\Argument('Invalid database type');
                    break;
                }
        }
    }

}
