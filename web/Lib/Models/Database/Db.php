<?php
namespace Framework\Models\Database;

use Exception;
use Framework\Models\Memcache\MemcacheInterface;

abstract class Db
{
    const SYNCHRONIZED_COLUMN_NAME = 'synchronized';

    const CLIENT_ID_COLUMN_NAME = 'clientId';

    const CUSTOM_QUERY = '__custom__';

    private static array $instances = [];

    private array $syncedTables = [];

    protected static string $hostName = DB_HOST;

    protected static string $userName = DB_USER;

    protected static string $password = DB_PASSWORD;

    protected static string $databaseName = DB_NAME;

    protected static string $encoding = DB_ENCODING;

    protected static string $type = DB_TYPE;

    protected static bool $prependDatabaseName = true;

    abstract public function escapeString(mixed $string):string;

    abstract public function sqlInsert(string $tableName, array $insertValues, array $keyFields = [], bool $insertIgnore  = false, bool $replace = false):string;

    abstract public function sqlUpdate(string $tableName, array $updateValues, array $keyFields, int $limit = 0):string;

    abstract public function sqlSelect(string $tableName, array $fields = [], array $where = [], array $joins = [], string|array $groupBy = '', string|array $orderBy = 'ASC', int $limit = 0):string;

    abstract public function sqlDelete(string $tableName, array $keyFields, int $limit = 0):string;

    abstract public function getInsertRecordId():?int;

    abstract public function getError():string;

    abstract protected function myDisconnect():void;

    abstract protected function mySqlQuery(string $query);

    abstract protected function myConnect(string $hostName, string $userName, string $password, string $databaseName, string $encoding):void;

    abstract protected function myOpenQuery(string $query);

    abstract public function closeQuery($result):void;

    abstract public function getRecordCount($result):int;

    abstract public function getNext($result):?array;

    /**
     * @throws Exception
     */
    public static function factory(string $type, string $hostName, string $userName, string $password, string $database, string $encoding)
    {
        if ($type == 'mysql') {
            $result = new Mysql();
        } else {
            throw new Exception (__METHOD__.": Unknown database type. Type=$type");
        }

        self::$hostName = $hostName;
        self::$userName = $userName;
        self::$password = $password;
        self::$databaseName = $database;
        self::$encoding = $encoding;

        return $result;
    }

    public static function create(string|false $databaseName = false):Db
    {
        $cls = static::class;

        if(!$databaseName){
            $databaseName = self::$databaseName;
        }

        if (!isset(self::$instances[$cls])) {
            if (self::$type == 'mysql') {
                self::$instances[$cls] = new Mysql();

                self::$instances[$cls]->myConnect(self::$hostName, self::$userName, self::$password, $databaseName, self::$encoding);
            }
        }

        return self::$instances[$cls];
    }

    public static function select(string $tableName, array $fields = [], array|string $where = [], array $joins = [], string|array $groupBy = '', string|array $orderBy = '', int $limit = 0):string
    {
        return self::create()->sqlSelect($tableName, $fields, $where, $joins, $groupBy, $orderBy, $limit);
    }

    public static function insert(string $tableName, array $insertValues, array $keyFields = [], bool $insertIgnore = false, bool $replace = false):string
    {
        return self::create()->sqlInsert($tableName, $insertValues, $keyFields, $insertIgnore, $replace);
    }

    public static function update(string $tableName, array $updateValues, array $keyFields, int $limit = 0):string
    {
        return self::create()->sqlUpdate($tableName, $updateValues, $keyFields, $limit);
    }

    public static function delete(string $tableName, array $keyFields, int $limit = 0):string
    {
        return self::create()->sqlDelete($tableName, $keyFields, $limit);
    }

    public function setSynchronizedTables(MemcacheInterface $memcached): void
    {
        $this->syncedTables = ($memcached->get(CACHE_SYNCED_TABLES) ?: []);

        if(Empty($this->syncedTables)){
            $this->syncedTables = $this->getTablesToSynchronize(self::$databaseName, self::SYNCHRONIZED_COLUMN_NAME);

            $memcached->set(CACHE_SYNCED_TABLES, $this->syncedTables);
        }
    }

    public function getSynchronizedTables():array
    {
        return $this->syncedTables;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function disconnect():void
    {
        $cls = static::class;

        if(!empty(isset(self::$instances[$cls]))) {
            $this->myDisconnect();

            self::$instances[$cls] = null;
        }
    }

    /**
     * Executes a query without resultset
     *
     * @param $query string Query to be executed
     * @param $sqlDebug false
     */
    public function sqlQuery(string $query, bool $sqlDebug = false):void
    {
        if($sqlDebug){
            dd($query);
        }

        try {
            $this->mySqlQuery($query);
        } catch (Exception $e) {
            $this->saveLog($query);
        }
    }

    /**
     * Opens a query and returns with a resultset
     *
     * @param $query string Query to be opened
     * @param $sqlDebug bool
     * @return mysqli_result|bool
     */
    public function openQuery(string $query, bool $sqlDebug = false)
    {
        if($sqlDebug){
            dd($query);
        }

        try {
            $ret = $this->myOpenQuery($query);
        } catch (Exception $e) {
            $this->saveLog($query);

            $ret = false;
        }

        return $ret;
    }

    /**
     * @param $query string
     * @param $sqlDebug bool
     * @return array
     */
    public function getRows(string $query, bool $sqlDebug = false):array
    {
        $result = $this->openQuery($query, $sqlDebug);
        $res = [];

        if ($result) {
            while ( $row = $this->getNext($result) ) {
                $res[] = $row;
            }
            $this->closeQuery($result);
        }

        return $res;
    }

    /**
     * @param $query string
     * @param $sqlDebug bool
     * @return array|false
     */
    public function getFirstRow(string $query, bool $sqlDebug = false):array|false
    {
        $result = $this->openQuery($query, $sqlDebug);

        if ($result) {
            $row = $this->getNext($result);

            $this->closeQuery($result);
        }

        return ($row ?? false);
    }

    public function prependDatabaseName(bool $value):void
    {
        self::$prependDatabaseName = $value;
    }

    public function showTables():array
    {
        $tables = [];

        $result = $this->getRows(
            'SHOW TABLES'
        );
        if($result){
            foreach($result AS $row){
                $tables[] = reset($row);
            }
        }

        return $tables;
    }

    public function showColumns(string $tableName):array
    {
        $columns = [];

        $result = $this->getRows(
            'SHOW COLUMNS FROM `' . $tableName . '`'
        );
        if($result){
            foreach($result AS $row){
                $columns[] = $row;
            }
        }

        return $columns;
    }

    public function showTriggers():array
    {
        $triggers = [];

        $result = $this->getRows(
            'SHOW TRIGGERS'
        );
        if($result){
            foreach($result AS $row){
                $triggers[$row['Table']][strtolower($row['Event'])] = 1;
            }
        }

        return $triggers;
    }

    public function getTablesToSynchronize(string $database, string $columnName):array
    {
        $tables = [];

        $result = $this->getRows(
            "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = '" . $this->escapeString($columnName) . "' AND TABLE_SCHEMA = '" . $this->escapeString($database) . "' ORDER BY TABLE_NAME"
        );
        if($result){
            foreach($result AS $row){
                $tables[] = $row['TABLE_NAME'];
            }
        }

        return $tables;
    }

    protected function markRecordToSync(string $tableName, array $fields):array
    {
        if(in_array($tableName, $this->syncedTables) && SYNC_DB_ENABLED){
            $fields[self::SYNCHRONIZED_COLUMN_NAME] = 0;
        }

        return $fields;
    }

    protected function saveQuery(string $tableName, array $keyFields):void
    {
        if(in_array($tableName, $this->syncedTables) && SYNC_DB_ENABLED) {
            $this->sqlQuery(
                $this->sqlInsert(
                    'sync_queries',
                    [
                        'sq_table' => $tableName,
                        'sq_keyfields' => json_encode($keyFields),
                        'sq_timestamp' => 'NOW()'
                    ]
                )
            );
        }
    }

    protected function saveLog(string $query):void
    {
        $error = $this->getError();
        if ( !empty($error) && defined('DIR_LOG') ) {
            $serverId = 'x';
            if(defined('SERVER_ID')){
                $serverId = SERVER_ID;
            }

            $className = (new \ReflectionClass($this))->getShortName();

            $fileName   = $className . '_errors_'. $serverId . '.txt';
            $folderName = DIR_LOG . strtolower( $className ) . '/' . date( 'Ym' ) . '/' . date( 'd' );

            if(!is_dir($folderName)){
                @mkdir($folderName, 0777, true);
                @chmod($folderName, 0777);
            }

            $data  = ' ' . date("H:i:s") . ': ' . $error . "\n";
            $data .= '      SQL: ' . $query . "\n";

            $callstack = [];
            $trace = debug_backtrace();
            foreach($trace as $i => $val) {
                if ($i == 0) continue;
                $func = (!empty($val['class'])) ? $val['class'] . $val['type'] . $val['function'] : $val['function'];

                $callstack[] = str_pad(basename(($val['file'] ?? '')), 30, ' ') . ' (' . ($val['line'] ?? '') . ') ' . $func . '()';
            }
            $callstack = array_reverse($callstack);

            foreach($callstack as $i => $val) {
                $data .= ($i == 0) ? 'CallStack: ' : '           ';
                $data .= str_pad($i, 2, ' ', STR_PAD_LEFT) . '. ' . $val . "\n";
            }
            $data .= str_repeat('-', 70) . "\n";

            @file_put_contents( $folderName . '/' . $fileName, $data, FILE_APPEND );
            @chmod($folderName . '/' . $fileName, 0666);
        }
    }

}
