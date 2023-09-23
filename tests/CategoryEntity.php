<?php

use SimpleMVC\BaseEntity;

class CategoryEntity extends BaseEntity
{
    protected $cat;
    protected $subcat;

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['cat'] = 'varchar(32) UNIQUE COLLATE utf8mb4_unicode_ci NOT NULL';
        $defs['subcat'] = 'varchar(32) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL';
        return $defs;
    }

    protected static function getTableName()
    {
        return "categories";
    }
}
