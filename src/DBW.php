<?php

namespace SimpleMVC;

class DBW extends DBP
{
    protected static $dbh = null;
    protected static $sth = null;
    protected static $dbHost = null;
    protected static $dbName = null;
    protected static $dbUser = null;
    protected static $dbPass = null;

    public static function configure()
    {
        self::$dbHost = DB_HOST;
        self::$dbName = DB_NAME;
        self::$dbUser = DBW_USER;
        self::$dbPass = DBW_PASS;
        self::$dbh = null;
    }
}
