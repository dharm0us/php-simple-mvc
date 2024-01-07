<?php

namespace SimpleMVC;

use \PDO;

class DBP
{

    protected static $dbh;
    protected static $sth;
    protected static $logQueryExceptions = true;
    protected static $slowQueryErrorLog = true;
    protected static $dbHost = null;
    protected static $dbName = null;
    protected static $dbUser = null;
    protected static $dbPass = null;
    protected static $logQuereies = false;

    public static function setQueryLog($val)
    {
        static::$logQuereies = $val;
    }

    public static function enableSlowQueryErrorLog()
    {
        static::$slowQueryErrorLog = true;
    }

    public static function disableSlowQueryErrorLog()
    {
        static::$slowQueryErrorLog = false;
    }

    public static function enableQueryExceptionLog()
    {
        static::$logQueryExceptions = true;
    }

    public static function disableQueryExceptionLog()
    {
        static::$logQueryExceptions = false;
    }

    public static function configure()
    {
        static::$dbHost = "";
        static::$dbName = "";
        static::$dbUser = "";
        static::$dbPass = "";
        static::$dbh = null;
    }

    protected static function init()
    {
        if (!static::isConfigured()) {
            static::configure();
        }
        try {
            $dsn = 'mysql:host=' . static::$dbHost . ';dbname=' . static::$dbName . ';';
            static::$dbh = new PDO($dsn, static::$dbUser, static::$dbPass, array(
                PDO::ATTR_PERSISTENT => true
            ));
            static::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            static::$sth = null;
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public static function getResultSet($query, $bindings = array())
    {
        static::runQuery($query, $bindings);
        return static::getRows();
    }

    public static function beginTransaction()
    {
        static::runQuery('start transaction');
    }

    public static function commit()
    {
        static::runQuery('commit');
    }

    public static function rollback()
    {
        static::runQuery('rollback');
    }

    public static function getPlaceHolderStringAndIdBindings(array $arr, $prefix = 'id')
    {
        $placeHolderStr = '';
        $idBindings = array();
        for ($i = 0; $i < count($arr); $i++) {
            $key = "$prefix$i";
            $placeHolderStr .= ":$key,";
            $idBindings[$key] = $arr[$i];
        }
        $placeHolderStr = rtrim($placeHolderStr, ",");
        return array($placeHolderStr, $idBindings);
    }

    public static function getTableRowsCount($table)
    {
        $query = "select count(1) as cnt from $table";
        static::runQuery($query);
        $rows = static::getRows();
        return $rows[0]['cnt'];
    }

    public static function getCountFromQuery($query, array $bindings = array())
    {
        $query = "select count(1) as cnt from ($query)t";
        static::runQuery($query, $bindings);
        $rows = static::getRows();
        return $rows[0]['cnt'];
    }

    public static function getLastInsertId()
    {
        return static::$dbh->lastInsertId();
    }

    protected static function isConfigured()
    {
        return (static::$dbHost && static::$dbName && static::$dbUser);
    }

    public static function runQuery($query, $bindings = array(), $attempt = 0)
    {
        if (static::$logQuereies) {
            Log::info($query);
        }
        $maxAttempts = 2;
        if ($attempt < $maxAttempts) {
            try {
                if ((stripos($query, "select") !== 0) && (isset($_SESSION['viewOnly']))) {
                    return;
                }
                if (!static::$dbh) {
                    static::init();
                }
                $bt = microtime(true);
                if (!static::$dbh) {
                    throw new Exception('DBH is null');
                }
                static::$sth = static::$dbh->prepare($query);
                static::$sth->execute($bindings);
                $at = microtime(true);
                $diff = ($at - $bt);
                if ($diff > SLOW_QUERY_THRESHOLD) {
                    if (static::$slowQueryErrorLog) {
                        Log::error("Time Taken : $diff seconds", array_slice($bindings, 0, 10), substr($query, 0, 200));
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                if (static::$logQueryExceptions) {
                    Log::error($error, $bindings, $query);
                }
                if (($e->getCode() == 'HY000') && ($attempt < $maxAttempts)) { //MySql server has gone away and other General errors denoted by HY000
                    Log::error("Sleeping before attempting again to handle HY000 attempt = $attempt");
                    static::$dbh = null;
                    sleep(5);
                    static::runQuery($query, $bindings, $attempt + 1);
                } else {
                    throw $e;
                }
            }
        } else {
            Log::error("max attempts crossed. $query");
        }
    }

    public static function delete($tableName, $id)
    {
        $query = "delete from $tableName where id = :id";
        static::runQuery($query, array('id' => $id));
    }

    public static function insert($tableName, $fields)
    {
        $query = 'INSERT INTO ' . $tableName;
        $query .= '(`' . implode('`,`', array_keys($fields)) . '`) ';
        $query .= 'VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')';
        static::runQuery($query, array_values($fields));
    }

    public static function insertMultiple($tableName, $fields_arr)
    {
        if (!$fields_arr) {
            return;
        }
        $query = 'INSERT INTO ' . $tableName;
        $query .= '(`' . implode('`,`', array_keys($fields_arr[0])) . '`) VALUES ';
        $insertData = array();
        foreach ($fields_arr as $fields) {
            $query .= '(' . implode(',', array_fill(0, count($fields), '?')) . '),';
            $insertData = array_merge($insertData, array_values($fields));
        }
        $query = rtrim($query, ",");
        static::runQuery($query, $insertData);
    }

    protected static function getPartialUpdateStmt()
    {
        return function ($value) {
            return "`$value`" . '=:' . $value;
        };
    }

    public static function update($tableName, $fields, $id)
    {
        $query = 'UPDATE ' . $tableName . ' SET ';
        $query .= implode(',', array_map(static::getPartialUpdateStmt(), array_keys($fields)));
        $query .= " WHERE id = :id";
        static::runQuery($query, array_merge(array('id' => $id), $fields));
    }

    protected static function getRows()
    {
        return static::$sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSingleResult($query, $bindings = array())
    {
        static::runQuery($query, $bindings);
        $rows = static::getRows();
        if (count($rows) > 1) {
            throw new NonUniqueResultException("$query has Multiple results,  bindings = " . implode(",", $bindings));
        } else {
            return $rows ? $rows[0] : null;
        }
    }

    public static function getObject($query, array $bindings, $className)
    {
        $obj = null;
        $row = static::getSingleResult($query, $bindings);
        if ($row) {
            $obj = new $className();
            $obj->buildFromDB($row);
        }
        return $obj;
    }
}
