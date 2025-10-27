<?php

use SimpleMVC\BaseEntity;
use SimpleMVC\ForeignKey;

class TestEntity extends BaseEntity
{
    protected $playerId;
    protected $categoryId;

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['playerId'] = 'bigint';
        $defs['categoryId'] = 'bigint';
        $defs['registration'] = 'varchar(20)';
        $defs['name'] = 'varchar(20)';
        return $defs;
    }

    public static function getFKs()
    {
        $a = array();
        $a[] = new ForeignKey(columnName: 'playerId', refTable: 'players', refColumn: 'id');
        $a[] = new ForeignKey(columnName: 'categoryId', refTable: 'user_notes', refColumn: 'id');
        return $a;
    }

    protected static function getIndexDefinitions()
    {
        $indices = array();
        $indices[] = array("FULLTEXT", "name_idx", "name");
        $indices[] = array("UNIQUE KEY", "registration_idx", "registration");
        return $indices;
    }

    public static function getTableName()
    {
        return "test_entity";
    }
}
