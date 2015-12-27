<?php

namespace THCFrame\Model;

use THCFrame\Core\Base;
use THCFrame\Registry\Registry;
use THCFrame\Model\Modelwriter;
use THCFrame\Core\Core;

/**
 * Class generate new class files based on database structure
 *
 * @author Tomy
 */
class Generator extends Base
{

    /**
     * @readwrite
     * @var type 
     */
    protected $_dbIdent;

    /**
     * @readwrite
     * @var type 
     */
    protected $_dbSchema;
    
    /**
     *
     * @var THCFrame\Database\Connector 
     */
    private $_db;

    /**
     *
     * @var THCFrame\Database\ConnectionHandler
     */
    private $_connectionHandler;

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        $this->_connectionHandler = Registry::get('database');

        parent::__construct($options);
        $ident = $this->_dbIdent;

        $this->_db = $this->_connectionHandler->get($ident);
        $this->_dbSchema = Registry::get('configuration')->database->$ident->schema;
    }

    /**
     * Get table prefix from system configuration
     * 
     * @return string
     */
    private function _getTablePrefix()
    {
        $ident = $this->_dbIdent;
        $tbPrefix = Registry::get('configuration')->database->$ident->tablePrefix;

        return $tbPrefix;
    }

    /**
     * Get tables from database with system specific prefix
     * 
     * @return array
     */
    private function _getTables()
    {
        $sqlResult = $this->_db->execute('SHOW TABLE STATUS IN ' . $this->getDbSchema() . " LIKE '" . $this->_getTablePrefix() . "%'");
        $tables = array();

        while ($row = $sqlResult->fetch_array(MYSQLI_ASSOC)) {
            if ($row['Comment'] == 'system') {
                continue;
            }

            $tables[$row['Name']] = $row['Comment'];
        }

        return $tables;
    }

    /**
     * Get columns of table
     * 
     * @param string $tableName
     * @return array
     */
    private function _getTableColumns($tableName)
    {
        $sqlResult = $this->_db->execute('SHOW FULL COLUMNS FROM ' . $tableName);
        $columns = array();

        while ($row = $sqlResult->fetch_array(MYSQLI_ASSOC)) {
            if (strtolower($row['Key']) == 'mul') {
                $fkResult = $this->_db->execute(
                        "select i.TABLE_NAME,i.COLUMN_NAME,i.CONSTRAINT_NAME,i.REFERENCED_TABLE_NAME,i.REFERENCED_COLUMN_NAME,r.UPDATE_RULE,r.DELETE_RULE 
                        from INFORMATION_SCHEMA.KEY_COLUMN_USAGE as i
                        LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS r
                        ON r.CONSTRAINT_SCHEMA=i.TABLE_SCHEMA
                            AND r.CONSTRAINT_NAME=i.CONSTRAINT_NAME
                        where i.TABLE_SCHEMA = '{$this->getDbSchema()}' and i.TABLE_NAME = '{$tableName}' and i.COLUMN_NAME = '{$row['Field']}'
                        and i.REFERENCED_COLUMN_NAME is not NULL;");

                if (!empty($fkResult)) {
                    while ($fkrow = $fkResult->fetch_array(MYSQLI_ASSOC)) {
                        $deleteRule = $updateRule = '';

                        if (!empty($fkrow['DELETE_RULE'])) {
                            $deleteRule = 'ON DELETE ' . $fkrow['DELETE_RULE'];
                        }

                        if (!empty($fkrow['UPDATE_RULE'])) {
                            $updateRule = 'ON UPDATE ' . $fkrow['UPDATE_RULE'];
                        }

                        $foreignStr = preg_replace('/\s+/', ' ', "{$fkrow['CONSTRAINT_NAME']} REFERENCES {$fkrow['REFERENCED_TABLE_NAME']} ({$fkrow['REFERENCED_COLUMN_NAME']}) {$deleteRule} {$updateRule}");

                        $row += array('Foreign' => $foreignStr);
                    }
                }
            }
            $columns[] = $row;
        }

        return $columns;
    }

    /**
     * Create model file name from table name and module name
     * 
     * @param string $tableName
     * @param string $module
     * @return string
     * @throws \Exception
     */
    private function _createModelFileName($tableName, $module)
    {
        $tbPrefix = $this->_getTablePrefix();
        if (in_array(ucfirst($module), \THCFrame\Core\Core::getModuleNames())) {

            $path = MODULES_PATH . '/' . $module . '/model/basic/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            return $path . 'basic' . strtolower(str_replace($tbPrefix, '', $tableName)) . 'model.php';
        } else {
            throw new \Exception($module . ' is not one of the registered application modules');
        }
    }

    /**
     * Create model class name
     * 
     * @param string $tableName
     * @return string
     */
    private function _createModelClassName($tableName)
    {
        $tbPrefix = $this->_getTablePrefix();

        return 'Basic' . ucfirst(str_replace($tbPrefix, '', $tableName)) . 'Model';
    }

    /**
     * Create annotation comment for property from column definition
     * 
     * @param array $column
     * @return string
     */
    private function _createColumnAnnotations($column)
    {
        if (!empty($column)) {
            preg_match('#^([a-z]*)\(?([0-9]*)\)?#i', $column['Type'], $matches);

            $lines = array();
            switch (strtolower($column['Key'])) {
                case 'pri': {
                        $lines[] = '* @primary';
                        break;
                    }
                case 'uni': {
                        $lines[] = '* @unique';
                        break;
                    }
                case 'mul': {
                        if(isset($column['Foreign']) && !empty($column['Foreign'])){
                            $lines[] = '* @foreign '.$column['Foreign'];
                        }else{
                            $lines[] = '* @index';
                        }
                        break;
                    }
            }

            if (strtolower($column['Key']) == 'pri') {
                $lines[] = '* @type auto_increment';
            } else {
                $lines[] = '* @type ' . strtolower($matches[1]);
                if (!empty($matches[2])) {
                    $lines[] = '* @length ' . $matches[2];
                }
            }

            if (!empty($column['Comment'])) {
                $parts = explode(';', $column['Comment']);
                foreach ($parts as $part) {
                    if(empty($part)){
                        continue;
                    }
                    $lines[] = '* ' . $part;
                }
            }

            stripos($column['Type'], 'unsigned') !== false ? $lines[] = '* @unsigned' : '';
            strtolower($column['Null']) == 'no' ? '' : $lines[] = '* @null';
            
            if ((int) $column['Default'] === 0 
                    && strtolower($column['Null']) == 'no' 
                    && strtolower($column['Key']) != 'pri'
                    && empty($column['Foreign'])
                    && in_array($matches[1], array('int', 'integer', 'tinyint', 'smallint', 'mediumint'))) {
                $lines[] = '* @default 0';
            } elseif ((int) $column['Default'] === 0 
                    && strtolower($column['Null']) == 'no' 
                    && in_array($matches[1], array('float', 'double', 'decimal'))) {
                $lines[] = '* @default 0.0';
            } elseif (!empty($column['Default'])) {
                $lines[] = '* @default '.$column['Default'];
            }
                
            $definition = implode(PHP_EOL . '     ', $lines);
            $annotation = <<<ANNOTATION
    /**
     * @column
     * @readwrite
     {$definition}
     */
ANNOTATION;

            return $annotation;
        }
    }

    /**
     * Create model classes based on table definitions
     */
    public function createModels()
    {
        $tables = $this->_getTables();

        if (!empty($tables)) {
            foreach ($tables as $table => $module) {
                Core::getLogger()->debug('-------- Creating model class for '.$table.' --------');
                $columns = $this->_getTableColumns($table);

                if (!empty($columns)) {
                    $modelWriter = new Modelwriter(array(
                        'filename' => $this->_createModelFileName($table, $module),
                        'classname' => $this->_createModelClassName($table),
                        'extends' => 'Model',
                        'namespace' => ucfirst($module) . '\Model\Basic')
                    );

                    $modelWriter->addUse('THCFrame\Model\Model');

                    foreach ($columns as $column) {
                        $an = $this->_createColumnAnnotations($column);
                        $modelWriter->addProperty($column['Field'], $an);
                    }

                    $modelWriter->writeModel();
                    unset($modelWriter, $columns);
                }
                
                Core::getLogger()->debug('-------- Model class was successfully created for table '.$table.' --------', 'system');
            }
        }
    }

}
