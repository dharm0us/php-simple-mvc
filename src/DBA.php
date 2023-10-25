<?php

namespace SimpleMVC;

class DBA extends DBP
{

    public static function configure()
    {
        self::$dbHost = DB_HOST;
        self::$dbName = DB_NAME;
        self::$dbUser = DBA_USER;
        self::$dbPass = DBA_PASS;
        self::$dbh = null;
    }
}
