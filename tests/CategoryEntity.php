<?php

use SimpleMVC\BaseEntity;

class CategoryEntity extends BaseEntity
{
    protected $cat;
    protected $subcat;

    public function getCat()
    {
        return $this->cat;
    }
    public function setCat($cat)
    {
        $this->cat = $cat;
    }

    public function getSubcat()
    {
        return $this->subcat;
    }
    public function setSubcat($subcat)
    {
        $this->subcat = $subcat;
    }

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['cat'] = 'varchar(32) UNIQUE COLLATE utf8mb4_unicode_ci NOT NULL';
        $defs['subcat'] = 'varchar(32) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL';
        return $defs;
    }

    public static function getTableName()
    {
        return "categories";
    }
}
