<?php

namespace THCFrame\Database\Connector;

use THCFrame\Database as Database;
use THCFrame\Database\Exception as Exception;
use THCFrame\Profiler\Profiler;
use THCFrame\Model\Model;
use THCFrame\Core\Core;

/**
 * The Database\Connector\Mysql class defines a handful of adaptable 
 * properties and methods used to perform MySQLi class-specific functions, 
 * and return MySQLi class-specific properties. We want to isolate these from 
 * the outside so that our system is essentially plug-and-play
 */
class Mysql extends Database\Connector
{

    protected $_service;

    /**
     * @readwrite
     */
    protected $_host;

    /**
     * @readwrite
     */
    protected $_username;

    /**
     * @readwrite
     */
    protected $_password;

    /**
     * @readwrite
     */
    protected $_schema;

    /**
     * @readwrite
     */
    protected $_port = '3306';

    /**
     * @readwrite
     */
    protected $_charset = 'utf8';

    /**
     * @readwrite
     */
    protected $_engine = 'InnoDB';

    /**
     * @readwrite
     */
    protected $_isConnected = false;

    /**
     * @read
     */
    protected $_magicQuotesActive;

    /**
     * @read
     */
    protected $_realEscapeStringExists;

    /**
     * Class constructor
     * 
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_magicQuotesActive = get_magic_quotes_gpc();
        $this->_realEscapeStringExists = function_exists('mysqli_real_escape_string');
    }

    public function __destruct()
    {
        $this->disconnect();
        unset($this->_service);
    }

    /**
     * 
     * @param type $query
     * @param type $runQuery
     * @return type
     * @throws Exception\Sql
     */
    private function _runSyncQuery($query, $runQuery = true)
    {
        if ($runQuery === false) {
            return;
        }

        Core::getLogger()->debug($query);

        $result = $this->execute($query);
        if ($result === false) {
            $this->_logError($this->_service->error, $query);
            throw new Exception\Sql();
        }
        return $result;
    }

    /**
     * 
     * @param type $error
     * @param type $sql
     */
    protected function _logError($error, $sql)
    {
        $errMessage = 'There was an error in the query {error} - SQL: {query}';
        Core::getLogger()->error($errMessage, array('error' => $error, 'query' => $sql));
    }

    /**
     * Method is used to ensure that the value of the
     * $_service is a valid MySQLi instance
     * 
     * @return boolean
     */
    protected function _isValidService()
    {
        $isEmpty = empty($this->_service);
        $isInstance = $this->_service instanceof \MySQLi;

        if ($this->isConnected && $isInstance && !$isEmpty) {
            return true;
        }
        return false;
    }

    /**
     * Method attempts to connect to the MySQL server at the specified host/port
     * 
     * @return \THCFrame\Database\Connector\Mysql
     * @throws Exception\Service
     */
    public function connect()
    {
        if (!$this->_isValidService()) {
            $this->_service = new \MySQLi(
                    $this->_host, $this->_username, $this->_password, $this->_schema, $this->_port
            );

            if ($this->_service->connect_error) {
                throw new Exception\Service('Unable to connect to database service');
            }

            $this->_service->set_charset('utf8');

            $this->isConnected = true;
            unset($this->_password);
        }

        return $this;
    }

    /**
     * Method attempts to disconnect the $_service instance from the MySQL service
     * 
     * @return \THCFrame\Database\Connector\Mysql
     */
    public function disconnect()
    {
        if ($this->_isValidService()) {
            $this->isConnected = false;
            $this->_service->close();
        }

        return $this;
    }

    /**
     * Return query object for specific connector
     * 
     * @return \THCFrame\Database\Database\Query\Mysql
     */
    public function query()
    {
        return new Database\Query\Mysql(array(
            'connector' => $this
        ));
    }

    /**
     * Method execute sql query by using prepared statements
     * 
     * @param string $sql
     * @return mysqli_stmt
     * @throws Exception\Service
     */
    public function execute($sql)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service('Not connected to a valid database service');
        }

        $profiler = Profiler::getInstance();

        $args = func_get_args();

        if (count($args) == 1) {
            $profiler->dbQueryStart($sql);
            $result = $this->_service->query($sql);
            $profiler->dbQueryStop($this->getAffectedRows());

            return $result;
        }

        //$profiler->dbQueryStart($sql);
        if (!$stmt = $this->_service->prepare($sql)) {
            $this->_logError($this->_service->error, $sql);

            if (ENV == 'dev') {
                throw new Exception\Sql(sprintf('There was an error in the query %s', $this->_service->error));
            } else {
                throw new Exception\Sql('There was an error in the query');
            }
        }

        array_shift($args); //remove sql from args

        $bindParamsReferences = array();

        foreach ($args as $key => $value) {
            $bindParamsReferences[$key] = &$args[$key];
        }

        $types = str_repeat('s', count($args)); //all params are strings, works well on MySQL and SQLite
        array_unshift($bindParamsReferences, $types);

        $bindParamsMethod = new \ReflectionMethod('mysqli_stmt', 'bind_param');
        $bindParamsMethod->invokeArgs($stmt, $bindParamsReferences);

        $stmt->execute();
        //$profiler->dbQueryStop($stmt->affected_rows);
        $meta = $stmt->result_metadata();

        unset($bindParamsMethod);

        if ($meta) {
            $stmtRow = array();
            $rowReferences = array();

            while ($field = $meta->fetch_field()) {
                $rowReferences[] = &$stmtRow[$field->name];
            }

            $bindResultMethod = new \ReflectionMethod('mysqli_stmt', 'bind_result');
            $bindResultMethod->invokeArgs($stmt, $rowReferences);

            $result = array();
            while ($stmt->fetch()) {
                foreach ($stmtRow as $key => $value) {
                    $row[$key] = $value;
                }
                $result[] = $row;
            }

            $stmt->free_result();
            $stmt->close();

            unset($stmt);
            unset($bindResultMethod);

            return $result;
        } else {
            return null;
        }
    }

    /**
     * Escapes values
     * 
     * @param mixed $value
     * @return mixed
     * @throws Exception\Service
     */
    public function escape($value)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service('Not connected to a valid database service');
        }

        if ($this->realEscapeStringExists) {
            if ($this->magicQuotesActive) {
                $value = stripslashes($value);
            }
            $value = $this->_service->real_escape_string($value);
        } else {
            if (!$this->magicQuotesActive) {
                $value = addslashes($value);
            }
        }

        return $value;
    }

    /**
     * Returns last inserted id
     * 
     * @return integer
     * @throws Exception\Service
     */
    public function getLastInsertId()
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service('Not connected to a valid database service');
        }

        return $this->_service->insert_id;
    }

    /**
     * Returns count of affected rows by last query
     * 
     * @return integer
     * @throws Exception\Service
     */
    public function getAffectedRows()
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service('Not connected to a valid database service');
        }

        return $this->_service->affected_rows;
    }

    /**
     * Return last error
     * 
     * @return string
     * @throws Exception\Service
     */
    public function getLastError()
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service('Not connected to a valid database service');
        }

        return $this->_service->error;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->_service->autocommit(FALSE);
    }

    /**
     * Commit transaction
     */
    public function commitTransaction()
    {
        $this->_service->commit();
        $this->_service->autocommit(TRUE);
    }

    /**
     * Rollback transaction
     */
    public function rollbackTransaction()
    {
        $this->_service->rollback();
        $this->_service->autocommit(TRUE);
    }

    /**
     * Retrun columns in table with field name as key
     * 
     * @param string $tableName
     * @return array
     */
    public function getColumns($tableName)
    {
        $sqlResult = $this->execute('SHOW FULL COLUMNS FROM ' . $tableName);
        $columns = array();

        while ($row = $sqlResult->fetch_array(MYSQLI_ASSOC)) {
            $field = $row['Field'];
            unset($row['Field']);
            $columns[$field] = $row;
        }

        return $columns;
    }

    /**
     * 
     * @param type $modelColumns
     * @param type $databaseColumns
     */
    protected function _dropColumns($modelColumns, $databaseColumns, $table)
    {
        $dropColumns = array_diff(array_keys($databaseColumns), array_keys($modelColumns));
        $queries = array();

        if (!empty($dropColumns)) {
            foreach ($dropColumns as $value) {
                $queries[] = "ALTER TABLE {$table} DROP COLUMN {$value};";
            }
        }

        return $queries;
    }

    /**
     * 
     * @param type $table
     */
    protected function _dropForeignKeys($table)
    {
        $fkResult = $this->execute(
                "select i.TABLE_NAME,i.COLUMN_NAME,i.CONSTRAINT_NAME,i.REFERENCED_TABLE_NAME,i.REFERENCED_COLUMN_NAME 
                        from INFORMATION_SCHEMA.KEY_COLUMN_USAGE i
                        where i.TABLE_SCHEMA = '{$this->getSchema()}' and i.TABLE_NAME = '{$table}' 
                        and i.referenced_column_name is not NULL;");

        $queries = array();
        while ($row = $fkResult->fetch_array(MYSQLI_ASSOC)) {
            $queries[] = "ALTER TABLE {$table} DROP FOREIGN KEY {$row['CONSTRAINT_NAME']}";
        }
        
        return $queries;
    }

    /**
     * Prepare queries to execute
     * 
     * @param Model $model
     * @param string $queryType
     * @param bool $dropIfExists
     * @return array
     */
    protected function _prepareQueries(Model $model, $queryType = 'alter', $dropIfExists = true)
    {
        $lines = $queries = array();
        $createConstraints = array();

        $columns = $model->getColumns();
        $table = $model->getTable();
        $databaseColumnList = $this->getColumns($table);

        $queries += $this->_dropColumns($columns, $databaseColumnList, $table);
        $queries += $this->_dropForeignKeys($table);

        preg_match('/^([a-zA-Z]*).*/i', get_class($model), $matches);
        $tableComment = strtolower($matches[1]);
        unset($matches);

        $templateCreate = "CREATE TABLE `%s` (\n%s,\n%s\n) ENGINE=%s DEFAULT CHARSET=%s COMMENT='%s';";
        $templateAlter = "ALTER TABLE `%s` %s;";

        foreach ($columns as $column) {
            $raw = $column['raw'];
            $name = $column['name'];
            $type = $column['type'];
            $length = $column['length'];

            if ($queryType == 'alter') {
                $alterType = $this->_getTypeOfAlter($name, $databaseColumnList);
            } else {
                $alterType = '';
            }

            if ($column['default'] !== false) {
                if ($column['default'] == 'null') {
                    $default = "DEFAULT NULL";
                } elseif ((int) $column['default'] === 0 && in_array($type, array('int', 'integer', 'tinyint', 'smallint', 'mediumint'))) {
                    $default = 'DEFAULT 0';
                } elseif ((int) $column['default'] === 0 && in_array($type, array('float', 'double', 'decimal'))) {
                    $default = 'DEFAULT 0.0';
                } elseif (is_numeric($column['default'])) {
                    $default = "DEFAULT {$column['default']}";
                } else {
                    $default = "DEFAULT '{$column['default']}'";
                }
            } else {
                $default = '';
            }

            $null = empty($column['null']) && strpos($type, 'text') === false ? 'NOT NULL' : '';
            $unsigned = $column['unsigned'] === true ? 'UNSIGNED' : '';

            $cmStr = $column['validate'] !== false ? '@validate ' . implode(',', $column['validate']) . ';' : '';
            $cmStr .=!empty($column['label']) ? '@label ' . $column['label'] . ';' : '';
            $comment = $cmStr === '' ? '' : "COMMENT '{$cmStr}'";

            switch ($type) {
                case 'auto_increment': {
                        $lines[] = "{$alterType} `{$name}` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";
                        break;
                    }
                default: {
                        if ($length !== null) {
                            $lines[] = preg_replace('/\s+/', ' ', "{$alterType} `{$name}` {$type}({$length}) {$unsigned} {$null} {$default} {$comment}");
                        } else {
                            $lines[] = preg_replace('/\s+/', ' ', "{$alterType} `{$name}` {$type} {$unsigned} {$null} {$default} {$comment}");
                        }
                        break;
                    }
            }

            if ($column['primary']) {
                $createConstraints[] = "PRIMARY KEY (`{$name}`)";
            }
            if ($column['index']) {
                $createConstraints[] = "KEY `ix_{$name}` (`{$name}`)";
            }
            if ($column['unique']) {
                $createConstraints[] = "UNIQUE KEY (`{$name}`)";
            }
            if (!empty($column['foreign'])) {
                preg_match('/^([a-zA-Z_-]*)\s?REFERENCES ([a-zA-Z_-]*) \(([a-zA-Z_,-]*)\) (.*)$/i', $column['foreign'], $fkParts);

                $fkName = !empty($fkParts[1]) ? "`{$fkParts[1]}`" : '';
                $referencedTable = $fkParts[2];
                $referencedColumn = $fkParts[3];
                $referenceDefinition = $fkParts[4];

                $createConstraints[] = preg_replace('/\s+/', ' ', "FOREIGN KEY {$fkName} (`{$name}`) REFERENCES `{$referencedTable}` (`{$referencedColumn}`) {$referenceDefinition}");
                unset($fkParts);
            }
            if (!empty($column['foreign']) && $queryType == 'alter') {
                $lines[] = preg_replace('/\s+/', ' ', "ADD FOREIGN KEY {$fkName} (`{$name}`) REFERENCES `{$referencedTable}` (`{$referencedColumn}`) {$referenceDefinition}");
            }
        }

        if ($queryType == 'create') {
            if ($dropIfExists === true) {
                $queries[] = "DROP TABLE IF EXISTS {$table};";
            }
            $queries[] = sprintf(
                    $templateCreate, $table, implode(",\n", $lines), implode(",\n", $createConstraints), $this->_engine, $this->_charset, $tableComment
            );
        } elseif ($queryType == 'alter') {
            if (!empty($lines)) {
                foreach ($lines as $columnDef) {
                    $queries[] = sprintf($templateAlter, $table, $columnDef);
                }
            }
        }

        unset($lines, $createConstraints, $model, $databaseColumnList);

        return $queries;
    }

    /**
     * Get type of alter. If column exists in database retunr modify.
     * If column does not exists in database return add.
     * 
     * @param string $columnName
     * @param array $databaseColumns
     * @return string
     */
    protected function _getTypeOfAlter($columnName, $databaseColumns)
    {
        if (array_key_exists($columnName, $databaseColumns)) {
            return 'MODIFY COLUMN';
        } elseif (!array_key_exists($columnName, $databaseColumns)) {
            return 'ADD COLUMN';
        }
    }

    /**
     * Method converts the class/properties into a valid SQL query, and 
     * ultimately into a physical database table. It does this by first 
     * getting a list of the columns, by calling the model’s getColumns() method. 
     * Looping over the columns, it creates arrays of indices and field strings.
     * After all the field strings have been created, they are joined (along with the indices), 
     * and applied to the CREATE TABLE or ALTER TABLE $template string.
     * 
     * @param Model $model
     * @return \THCFrame\Database\Connector\Mysql
     * @throws Exception\Sql
     */
    public function sync(Model $model, $runQuery = true, $queryType = 'alter', $dropIfExists = true)
    {
        Core::getLogger()->debug('---------- Sync Start ----------');

        $queries = $this->_prepareQueries($model, $queryType, $dropIfExists);

        try {
            $this->beginTransaction();

            $this->execute('SET foreign_key_checks = 0;');

            if (!empty($queries)) {
                foreach ($queries as $sql) {
                    $this->_runSyncQuery($sql, $runQuery);
                }
            }

            $this->execute('SET foreign_key_checks = 1;');
        } catch (\Exception $ex) {
            Core::getLogger()->debug('{exception}', array('exception' => $ex));
            Core::getLogger()->debug('---------- Sync was finished with errors ----------');
            $this->rollbackTransaction();
            return false;
        }

        $this->commitTransaction();
        Core::getLogger()->debug('---------- Sync was finished without errors ----------');

        return true;
    }

    /**
     * 
     */
    public function getDatabaseSize()
    {
        $sql = "SHOW TABLE STATUS FROM `" . $this->_schema . "`";
        $result = $this->execute($sql);

        if ($result !== false) {
            $size = 0;

            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $size += $row["Data_length"] + $row["Index_length"];
            }

            $megabytes = $size / (1024 * 1024);
            return number_format(round($megabytes, 3), 2);
        }

        return 0;
    }

    /**
     * 
     * @return boolean
     */
    public function ping()
    {
        if ($this->_isValidService()) {
            return $this->_service->ping();
        }

        return false;
    }

}
