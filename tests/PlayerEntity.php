<?php

use SimpleMVC\BaseEntity;

class PlayerEntity extends BaseEntity
{
    protected $name;
    protected $registration;

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['name'] = 'text COLLATE utf8mb4_unicode_ci NOT NULL';
        $defs['dob'] = 'DATE NULL DEFAULT NULL';
        $defs['registration'] = 'varchar(32) UNIQUE COLLATE utf8mb4_unicode_ci NOT NULL';
        $defs['region'] = 'varchar(32) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL';
        return $defs;
    }

    protected static function getIndexDefinitions()
    {
        $indices = array();
        $indices[] = "FULLTEXT (name)";
        $indices[] = "INDEX idx_dob (dob)";
        $indices[] = "INDEX idx_region (region)";
        return $indices;
    }

    protected static function getTableName()
    {
        return "players";
    }
}
