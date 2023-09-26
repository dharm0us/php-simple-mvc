<?php

use SimpleMVC\BaseEntity;
use SimpleMVC\ForeignKey;

class RankingEntity extends BaseEntity
{
    protected $playerId;
    protected $categoryId;

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['playerId'] = 'bigint(20)';
        $defs['categoryId'] = 'bigint(20)';
        return $defs;
    }

    public static function getFKs()
    {
        $a = array();
        $a[] = new ForeignKey(columnName: 'playerId', refTable: 'players', refColumn: 'id');
        $a[] = new ForeignKey(columnName: 'categoryId', refTable: 'categories', refColumn: 'id');
        return $a;
    }

    public static function getTableName()
    {
        return "rankings";
    }
}
