<?php

use SimpleMVC\BaseEntity;

class RankingRowEntity extends BaseEntity
{
    protected int $rowNum;
    protected string $cat;
    protected string $subcat;
    protected $rankingDate;
    protected string $rankingFile;
    protected int $rankOnDate;
    protected float $rankingPoints;

    protected string $name;
    protected string $registration;
    protected $dob;
    protected string $state;
    protected string $gender;

    protected static function getColumnDefinitions()
    {
        $defs = array();

        $defs['rowNum'] = 'int NOT NULL';
        $defs['cat'] = 'varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL';
        $defs['subcat'] = 'varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL';
        $defs['rankingDate'] = 'DATE DEFAULT NULL';
        $defs['rankingFile'] = 'varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL';
        $defs['rankOnDate'] = 'int NOT NULL';
        $defs['rankingPoints'] = 'float NOT NULL';
        $defs['name'] = 'varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL';
        $defs['registration'] = 'varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL';
        $defs['dob'] = 'DATE DEFAULT NULL';
        $M = 'M';
        $F = 'F';
        $defs['gender'] = "ENUM('$M', '$F') NOT NULL";
        $defs['state'] = 'varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL';
        return $defs;
    }

    protected static function getIndexDefinitions()
    {
        $indices = array();
        $indices[] = array("FULLTEXT", "name_idx_ft", "name");
        $indices[] = array("INDEX", "name_idx", "name");
        $indices[] = array("INDEX", "registration_idx", "registration");
        $indices[] = array("INDEX", "dob_idx", "dob");
        $indices[] = array("INDEX", "state_idx", "state");
        $indices[] = array("INDEX", "cat_idx", "cat");
        $indices[] = array("INDEX", "subcat_idx", "subcat");
        $indices[] = array("INDEX", "rankingDate_idx", "rankingDate");
        $indices[] = array("INDEX", "rankingFile_idx", "rankingFile");
        $indices[] = array("INDEX", "rankOnDate_idx", "rankOnDate");
        $indices[] = array("INDEX", "rankingPoints_idx", "rankingPoints");
        $indices[] = array("INDEX", "cat_subcat_rankingDate_idx", "cat,subcat,rankingDate");
        $indices[] = array("INDEX", "registration_cat_subcat_rankOnDate_idx", "registration,cat,subcat,rankOnDate");
        // for explain select cat, subcat, min(rankOnDate) as bestRank FROM rankings where registration = '400026' group by cat, subcat;
        $indices[] = array("INDEX", "registration_cat_subcat_rankingDate_idx", "registration,cat,subcat,rankingDate DESC");
        // for explain select rankOnDate,rankingDate,rankingPoints from rankings where registration = '439332' and cat = 'Boys' and subcat = 'U12' order by rankingDate desc
        $indices[] = array("INDEX", "registration_rankingDate_idx", "registration,rankingDate DESC");
        // for explain select * from rankings where registration = '421966' order by rankingDate desc limit 1;

        $indices[] = array("INDEX", "idx_rank_registration_cat_subcat", "rankOnDate,registration,cat,subcat");
        // for getting all the number 1 players ever

        $indices[] = array("UNIQUE KEY", "rownum_filename_idx", "rowNum,rankingFile");
        return $indices;
    }

    public static function getTableName()
    {
        return "rankings";
    }
}
