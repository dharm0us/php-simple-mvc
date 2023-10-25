<?php

namespace SimpleMVC;

class DBR extends DBP
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
        self::$dbUser = DBR_USER;
        self::$dbPass = DBR_PASS;
        self::$dbh = null;
    }
}
