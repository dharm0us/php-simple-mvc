<?php

use SimpleMVC\DBP;

class DBRW extends DBP
{
    public static function configure()
    {
        self::$dbHost = DB_HOST;
        self::$dbName = DB_NAME;
        self::$dbUser = DB_USER;
        self::$dbPass = DB_PASS;
        self::$dbh = null;
    }
}
