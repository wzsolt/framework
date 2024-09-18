<?php
namespace Framework\Models\Database;

use mysqli;
use Exception;

class Mysql extends Db
{
    private ?mysqli $connection = null;

    public function myConnect(string $hostName, string $userName, string $password, string $databaseName, string $encoding):void
    {
        if(empty($this->connection)) {
            $this->connection = new mysqli($hostName, $userName, $password, $databaseName);

            if ($this->connection->connect_error) {

                $this->connection = null;

                throw new Exception(__METHOD__ . ": Error connecting to database host.");
            }

            $this->connection->set_charset($encoding);
        }
    }

    /** Disconnects persistent database connection */
    public function myDisconnect():void
    {
        if($this->connection) $this->connection->close();
    }

    public function mySqlQuery($query):void
    {
        if (!$this->connection->query($query)) {
            throw new Exception( __METHOD__ . ": Error executing query: $query. Error message: " . $this->connection->error );
        }
    }

    /**
     * Opens a query and returns resultset
     * @param $query string Query to be opened
     * @return int
     * @throws
     */
    public function myOpenQuery($query)
    {
        if ( $this->connection->real_query($query) ) {
            return $this->connection->store_result();
        } else {
            throw new Exception( __METHOD__ . ": Error opening query: $query. Error message: " . $this->connection->error );
        }
    }

    /**
     * @param $result mysqli_result
     */
    public function closeQuery($result):void
    {
        $result->free();
    }

    /**
     * Returns the record count of a resultset
     * @param $result mysqli_result
     * @return int
     */
    public function getRecordCount($result):int
    {
        return $result->num_rows;
    }

    /**
     * Returns the next row of the query
     * in an associative array indexed by field names
     * @param $result mysqli_result
     * @return array
     */
    public function getNext($result):?array
    {
        return $result->fetch_assoc();
    }

    /**
     * Get last insert ID
     * @return int
     */
    public function getInsertRecordId():?int
    {
        return $this->connection->insert_id;
    }

    /**
     * Get number of affected rows after update/delete
     * @return int
     */
    public function getAffectedRows():int
    {
        return $this->connection->affected_rows;
    }

    /**
     * Get last MySQL error
     * @return string
     */
    public function getError():string
    {
        return $this->connection->error;
    }

    /**
     * Escape string
     * @param string $string
     * @return string
     */
    public function escapeString(mixed $string):string
    {
        if (is_array($string)) {
            $string = serialize($string);
        }

        return $this->connection->real_escape_string($string);
    }

    /**
     * Generate SQL SELECT query
     * @param string $tableName
     * @param array $fields
     * @param array $where array of fields or raw where statement
     * @param array $joins table name as key, possible values, string:as, array:keyFields
     * @param string|array $groupBy array of fields or string
     * @param string|array $orderBy array of fields or string
     * @param int $limit number of fetched rows
     * @return string
     */
    public function sqlSelect(string $tableName, array $fields = [], array|string $where = [], array $joins = [], string|array $groupBy = '', string|array $orderBy = '', int $limit = 0):string
    {
        if(Empty($fields)){
            $fields = "*";
        }else{
            $fields = implode(', ', $fields);
        }
        $result = "SELECT " . $fields . " FROM " . $this->prepareTableName($tableName);
        if($joins){
            foreach($joins AS $table => $join){
                $result .= " LEFT JOIN " . $this->prepareTableName($table) . (!Empty($join['as']) ? ' AS ' . $join['as'] : '');
                if(!Empty($join['on'])){
                    $j = [];
                    foreach ($join['on'] as $key => $val) {
                        $j[] = $key . '=' . $val;
                    }
                    $result .= " ON (" . implode(' AND ', $j) . ")";
                }
            }
        }

        if($where){
            $result .= $this->genSQLWhere($where);
        }

        if($groupBy){
            if(!is_array($groupBy)){
                $groupBy = [$groupBy];
            }
            $result .= " GROUP BY " . implode(', ', $groupBy);
        }

        if($orderBy){
            if(!is_array($orderBy)){
                $orderBy = [$orderBy];
            }
            $result .= " ORDER BY " . implode(', ', $orderBy);
        }

        if($limit){
            $result .= " LIMIT " . $limit;
        }

        return $result;
    }

    /**
     * Generate SQL INSERT query
     * @param string $tableName
     * @param array $insertValues key/value array of fields/values
     * @param array $keyFields array of key fields
     * @param bool $insertIgnore
     * @param bool $replace
     * @return string
     */
    public function sqlInsert(string $tableName, array $insertValues, array $keyFields = [], bool $insertIgnore = false, bool $replace = false):string
    {
        //$insertValues = $this->markRecordToSync($tableName, $insertValues);

        $insertValues = $this->prepareValues($insertValues);

        $result = ($replace ? 'REPLACE' : 'INSERT') . ' ' . ($insertIgnore && !$replace ? 'IGNORE ' : '') . 'INTO ' . self::prepareTableName($tableName) . ' (' . implode(', ', array_keys($insertValues)) . ') VALUES (' . implode(', ', array_values($insertValues)) . ')';
        if (!empty($keyFields)) {
            $result .= " ON DUPLICATE KEY UPDATE ";
            $prefix = '';
            foreach ($insertValues as $key => $val) {
                if (!in_array($key, $keyFields)) {
                    $result .= $prefix.$key.'='.$val;
                    $prefix=', ';
                }
            }
        }
        return $result;
    }

    /**
     * Generate SQL UPDATE query
     * @param string $tableName
     * @param array $updateValues key/value array of fields/values
     * @param array $keyFields array of key fields
     * @param int $limit
     * @return string
     */
    public function sqlUpdate(string $tableName, array $updateValues, array $keyFields, int $limit = 0):string
    {
        //$updateValues = $this->markRecordToSync($tableName, $updateValues);

        $updateValues = $this->prepareValues($updateValues);
        $result = "UPDATE " . $this->prepareTableName($tableName) . " SET ";
        $set = [];
        foreach($updateValues as $key => $val) {
            $set[] = $key . '=' . $val;
        }
        $result .= implode(", ", $set);
        $result .= $this->genSQLWhere($keyFields);

        if($limit){
            $result .= " LIMIT " . $limit;
        }

        return $result;
    }

    /**
     * Generate SQL DELETE query
     * @param string $tableName
     * @param array $keyFields key/value array of key fields
     * @param int $limit
     * @return string
     */
    public function sqlDelete(string $tableName, array $keyFields, int $limit = 0):string
    {
        $result = "DELETE FROM " . self::prepareTableName($tableName);
        $result .= $this->genSQLWhere($keyFields);

        if($limit){
            $result .= " LIMIT " . $limit;
        }

        //$this->saveQuery($tableName, $keyFields);

        return $result;
    }

    /**
     * Generate SQL WHERE statement
     * @param string|array $fields key/value pairs with AND concatenation or raw where statement (as string)
     * @return string
     */
    private function genSQLWhere(mixed $fields):string
    {
        if(is_array($fields)) {
            $fields = $this->prepareValues($fields, false);

            $where = [];
            foreach ($fields as $key => $val) {
                if(!is_numeric($key)) {
                    if(is_array($val)){
                        foreach($val AS $operation => $value) {
                            if ($operation == 'in' && is_array($value) && !Empty($value)) {
                                $where[] = $key . ' IN (' . $this->implodeArray($value) . ')';
                            }
                            if ($operation == 'notin' && is_array($value) && !Empty($value)) {
                                $where[] = $key . ' NOT IN (' . $this->implodeArray($value) . ')';
                            }
                            if ($operation == 'not') {
                                $where[] = $key . '!="' . $value . '"';
                            }
                            if ($operation == 'is') {
                                $where[] = $key . ' IS ' . $this->prepareValue($value);
                            }
                            if ($operation == 'isnot') {
                                $where[] = $key . ' IS NOT ' . $this->prepareValue($value);
                            }
                            if ($operation == 'greater') {
                                $where[] = $key . '>' . $this->prepareValue($value);
                            }
                            if ($operation == 'greater=') {
                                $where[] = $key . '>=' . $this->prepareValue($value);
                            }
                            if ($operation == 'less') {
                                $where[] = $key . '<' . $this->prepareValue($value);
                            }
                            if ($operation == 'less=') {
                                $where[] = $key . '<=' . $this->prepareValue($value);
                            }
                            if ($operation == 'like') {
                                $where[] = $key . ' LIKE ' . $this->prepareValue($value);
                            }
                        }
                    }elseif($key == self::CUSTOM_QUERY) {
                        $where[] = $val;
                    }else {
                        $where[] = $key . '=' . $val;
                    }
                }else{
                    $where[] = $val;
                }
            }
            return ' WHERE ' . implode(" AND ", $where);
        }else{
            return " WHERE " . $fields;
        }
    }

    private function implodeArray(array $array, string $separator = ','):string
    {
        $tmp = [];

        if(!Empty($array)) {
            foreach ($array AS $value) {
                if(is_string($value)){
                    $value = '"' . $value . '"';
                }

                $tmp[] = $value;
            }
        }

        return implode($separator, $tmp);
    }

    /**
     * Set SQL variable
     * @param array $vars
     * @return  void
     */
    public function setVariables(array $vars):void
    {
        $sql = [];
        foreach($vars as $key => $val) {
            $sql[] = '@' . $key . '="' . $this->escapeString($val) . '"';
        }
        if (!empty($sql)) {
            $this->sqlQuery("SET " . implode(', ', $sql));
        }
    }

    /**
     * Prepare/escape insert values from the array
     * @param array $values key/value pairs
     * @param bool $serializeValues serialize values in case of array
     * @return array
     */
    private function prepareValues(array $values, bool $serializeValues = true):array
    {
        $inc = 0;
        foreach($values as $key => $val) {
            if (is_array($val) && $serializeValues) {
                if(!Empty($val)) {
                    $val = serialize($val);
                }else{
                    $val = '';
                }
            }

            if(!is_array($val)) {
                if ($val === NULL) {
                    $val = 'NULL';
                }else if (is_numeric(str_replace([' ', ','], ['', '.'], $val)) && substr($val, 0, 1) != 0
                    && stripos($val, 'e') === false && stripos($val, 'f') === false && strlen($val) < 20) {
                    $val = str_replace([' ', ','], ['', '.'], $val);
                } else if ($val == 'NOW()') {
                } else if (substr($val, 0, 4) == 'MD5(') {
                } else if (substr($val, 0, 4) == 'AND(') {
                    preg_match('#\((.*?)\)#', $val, $match);
                    $val = $key . " & " . $match[1];
                } else if (substr($val, 0, 3) == 'OR(') {
                    preg_match('#\((.*?)\)#', $val, $match);
                    $val = $key . " | " . $match[1];
                } else if (substr($val, 0, 3) == 'IN(') {
                    preg_match('#\((.*?)\)#', $val, $match);
                    $val = $key . " IN (" . $match[1] . ")";
                    unset($values[$key]);
                    $key = $inc++;
                } else if ($val == 'INCREMENT') {
                    $val = $key . " + 1";
                } else if ($val == 'DECREMENT') {
                    $val = $key . " - 1";
                } else {
                    if($key != self::CUSTOM_QUERY) {
                        $val = "'" . $this->escapeString($val) . "'";
                    }
                }
            }
            $values[$key] = $val;
        }

        return $values;
    }

    private function prepareValue($value):string
    {
        if(!is_array($value)) {
            if (is_numeric(str_replace([' ', ','], ['', '.'], $value)) && substr($value, 0, 1) != 0
                && stripos($value, 'e') === false && stripos($value, 'f') === false && strlen($value) < 20) {
                $value = str_replace([' ', ','], ['', '.'], $value);
            } else if ($value === NULL || $value === 'NULL') {
                $value = 'NULL';
            } else if ($value === 'NOW()') {
                $value = 'NOW()';
            } else {
                $value = "'" . $this->escapeString($value) . "'";
            }
        }
        return $value;
    }

    /**
     * Prepend database name before the table name
     * @param string $tableName
     * @param bool $prependDatabaseName
     * @return string
     */
    public function prepareTableName(string $tableName, bool $prependDatabaseName = true):string
    {
        if(self::$prependDatabaseName && $prependDatabaseName){
            if(!str_contains($tableName, '.')){
                $tableName = self::$databaseName . '.' . $tableName;
            }
        }

        return $tableName;
    }
}
