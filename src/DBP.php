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

    public static function enableSlowQueryErrorLog()
    {
        self::$slowQueryErrorLog = true;
    }

    public static function disableSlowQueryErrorLog()
    {
        self::$slowQueryErrorLog = false;
    }

    public static function enableQueryExceptionLog()
    {
        self::$logQueryExceptions = true;
    }

    public static function disableQueryExceptionLog()
    {
        self::$logQueryExceptions = false;
    }

    public static function configure($db_host, $db_name, $db_user, $db_pass)
    {
        self::$dbHost = $db_host;
        self::$dbName = $db_name;
        self::$dbUser = $db_user;
        self::$dbPass = $db_pass;
        self::$dbh = null;
    }

    protected static function init()
    {
        if (!self::isConfigured()) {
            self::configure(DB_HOST, DB_NAME, DB_USER, DB_PASS);
        }
        try {
            $dsn = 'mysql:host=' . self::$dbHost . ';dbname=' . self::$dbName . ';';
            self::$dbh = new PDO($dsn, self::$dbUser, self::$dbPass, array(
                PDO::ATTR_PERSISTENT => true
            ));
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$sth = null;
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public static function getResultSet($query, $bindings = array())
    {
        self::runQuery($query, $bindings);
        return self::getRows();
    }

    public static function beginTransaction()
    {
        self::runQuery('start transaction');
    }

    public static function commit()
    {
        self::runQuery('commit');
    }

    public static function rollback()
    {
        self::runQuery('rollback');
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
        self::runQuery($query);
        $rows = self::getRows();
        return $rows[0]['cnt'];
    }

    public static function getCountFromQuery($query, array $bindings = array())
    {
        $query = "select count(1) as cnt from ($query)t";
        self::runQuery($query, $bindings);
        $rows = self::getRows();
        return $rows[0]['cnt'];
    }

    public static function getLastInsertId()
    {
        return self::$dbh->lastInsertId();
    }

    protected static function isConfigured()
    {
        return (self::$dbHost && self::$dbName && self::$dbUser);
    }

    public static function runQuery($query, $bindings = array(), $attempt = 0)
    {
        $maxAttempts = 2;
        if ($attempt < $maxAttempts) {
            try {
                if ((stripos($query, "select") !== 0) && (isset($_SESSION['viewOnly']))) {
                    return;
                }
                if (!self::$dbh) {
                    self::init();
                }
                $bt = microtime(true);
                if (!self::$dbh) {
                    throw new Exception('DBH is null');
                }
                self::$sth = self::$dbh->prepare($query);
                self::$sth->execute($bindings);
                $at = microtime(true);
                $diff = ($at - $bt);
                if ($diff > 2) {
                    if (self::$slowQueryErrorLog) {
                        Log::error("Time Taken : $diff seconds", $bindings, $query);
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                if (self::$logQueryExceptions) {
                    Log::error($error, $bindings, $query);
                }
                if (($e->getCode() == 'HY000') && ($attempt < $maxAttempts)) { //MySql server has gone away and other General errors denoted by HY000
                    Log::error("Sleeping before attempting again to handle HY000 attempt = $attempt");
                    self::$dbh = null;
                    sleep(5);
                    self::runQuery($query, $bindings, $attempt + 1);
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
        self::runQuery($query, array('id' => $id));
    }

    public static function insert($tableName, $fields)
    {
        $query = 'INSERT INTO ' . $tableName;
        $query .= '(`' . implode('`,`', array_keys($fields)) . '`) ';
        $query .= 'VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')';
        self::runQuery($query, array_values($fields));
    }

    public static function insertMultiple($tableName, $fields_arr)
    {
        $query = 'INSERT INTO ' . $tableName;
        $query .= '(`' . implode('`,`', array_keys($fields_arr[0])) . '`) VALUES ';
        $insertData = array();
        foreach ($fields_arr as $fields) {
            $query .= '(' . implode(',', array_fill(0, count($fields), '?')) . '),';
            $insertData = array_merge($insertData, array_values($fields));
        }
        $query = rtrim($query, ",");
        self::runQuery($query, $insertData);
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
        $query .= implode(',', array_map(self::getPartialUpdateStmt(), array_keys($fields)));
        $query .= " WHERE id = :id";
        self::runQuery($query, array_merge(array('id' => $id), $fields));
    }

    protected static function getRows()
    {
        return self::$sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSingleResult($query, $bindings = array())
    {
        self::runQuery($query, $bindings);
        $rows = self::getRows();
        if (count($rows) > 1) {
            throw new NonUniqueResultException("$query has Multiple results,  bindings = " . implode(",", $bindings));
        } else {
            return $rows ? $rows[0] : null;
        }
    }

    public static function getObject($query, array $bindings, $className)
    {
        $obj = null;
        $row = self::getSingleResult($query, $bindings);
        if ($row) {
            $obj = new $className();
            $obj->buildFromDB($row);
        }
        return $obj;
    }
}
