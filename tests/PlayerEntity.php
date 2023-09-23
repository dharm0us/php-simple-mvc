<?php

use SimpleMVC\BaseEntity;

class PlayerEntity extends BaseEntity
{
    protected $name;
    protected $registration;

    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    public function getRegistration()
    {
        return $this->registration;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['name'] = 'text COLLATE utf8mb4_unicode_ci NOT NULL';
        $defs['registration'] = 'varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL';
        return $defs;
    }

    protected static function getIndexDefinitions()
    {
        $indices = array();
        $indices[] = array("FULLTEXT", "name_idx", "name");
        $indices[] = array("UNIQUE KEY", "registration_idx", "registration");
        return $indices;
    }

    protected static function getTableName()
    {
        return "players";
    }
}
