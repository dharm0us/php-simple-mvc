<?php

use SimpleMVC\BaseEntity;

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

    protected static function getFKs()
    {
        $a = array();
        $a['playerId'] = array('table' => 'players', 'column' => 'id');
        $a['categoryId'] = array('table' => 'categories', 'column' => 'id');
        return $a;
    }

    protected static function getTableName()
    {
        return "rankings";
    }
}
