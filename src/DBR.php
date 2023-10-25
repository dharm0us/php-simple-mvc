<?php

namespace SimpleMVC;

class DBR extends DBP
{

    public static function configure()
    {
        self::$dbHost = DB_HOST;
        self::$dbName = DB_NAME;
        self::$dbUser = DBR_USER;
        self::$dbPass = DBR_PASS;
        self::$dbh = null;
    }
}
