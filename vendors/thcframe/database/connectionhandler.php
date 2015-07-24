<?php

namespace THCFrame\Database;

use THCFrame\Core\Base;
use THCFrame\Database\Exception;

/**
 * 
 */
class ConnectionHandler extends Base
{

    private $_connectors = array();

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
    }

    /**
     * 
     * @param type $id
     * @param \THCFrame\Database\Connector $connector
     * @throws Exception\Argument
     */
    public function add($id, $connector)
    {
        if ($connector instanceof Connector) {
            $id = strtolower(trim($id));
            $this->_connectors[$id] = $connector;
        } else {
            throw new Exception\Argument(sprintf('%s is not valid connector', $id));
        }

        return $this;
    }

    /**
     * 
     * @param type $id
     */
    public function get($id)
    {
        if (array_key_exists($id, $this->_connectors)) {
            $id = strtolower(trim($id));
            return $this->_connectors[$id];
        } else {
            throw new Exception\Argument(sprintf('%s is not registred connector', $id));
        }
    }

    /**
     * 
     * @param type $id
     */
    public function erase($id)
    {
        $id = strtolower(trim($id));

        if (array_key_exists($id, $this->_connectors)) {
            unset($this->_connectors[$id]);
        }

        return $this;
    }

    /**
     * 
     * @return type
     */
    public function getIdentifications()
    {
        if (!empty($this->_connectors)) {
            return array_keys($this->_connectors);
        } else {
            return array();
        }
    }

    /**
     * 
     * @param type $id
     */
    public function disconnectById($id)
    {
        $id = strtolower(trim($id));

        if (array_key_exists($id, $this->_connectors)) {
            $this->_connectors[$id]->disconnect();
        }

        return $this;
    }

    /**
     * 
     */
    public function disconnectAll()
    {
        foreach ($this->_connectors as $connector) {
            $connector->disconnect();
        }
    }

}
