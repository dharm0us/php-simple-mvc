<?php

namespace SimpleMVC;

class DBW extends DBP
{

    public static function configure()
    {
        self::$dbHost = DB_HOST;
        self::$dbName = DB_NAME;
        self::$dbUser = DBW_USER;
        self::$dbPass = DBW_PASS;
        self::$dbh = null;
    }
}
