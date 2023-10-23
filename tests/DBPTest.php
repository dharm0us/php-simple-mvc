<?php

use SimpleMVC\DBP;

require_once 'test_setup.php';
require_once 'PlayerEntity.php';
require_once 'CategoryEntity.php';
require_once 'RankingEntity.php';
class DBPTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        TestUtils::setUpTestDB();
    }

    public function testInsertMultiple()
    {
        DBP::runQuery("drop table if exists test_table");
        DBP::runQuery("create table test_table (a int, b int)");
        $fields_arr = array();
        $fields_arr[] = array("a" => 9, "b" => 10);
        $fields_arr[] = array("a" => 11, "b" => 12);
        DBP::insertMultiple("test_table", $fields_arr);
        $rows = DBP::getResultSet("select * from test_table");
        $this->assertEquals(2, count($rows));
        $this->assertEquals($fields_arr, $rows);

        DBP::runQuery("delete from " . CategoryEntity::getTableName());
        $cat1 = new CategoryEntity();
        $cat1->setCat("Boys");
        $cat1->setSubcat("U-12");
        $cat1->setCreatedAt(time());
        $cat1->setUpdatedAt(time());

        $cat2 = new CategoryEntity();
        $cat2->setCat("Girls");
        $cat2->setSubcat("U-14");
        $cat2->setCreatedAt(time());
        $cat2->setUpdatedAt(time());
        $categories = array($cat1->getFields(), $cat2->getFields());
        DBP::insertMultiple(CategoryEntity::getTableName(), $categories);
        $rows = DBP::getResultSet("select * from " . CategoryEntity::getTableName());
        $this->assertEquals($cat1->getCat(), "Boys");
        $this->assertEquals($cat1->getSubcat(), "U-12");
        $this->assertEquals($cat2->getCat(), "Girls");
        $this->assertEquals($cat2->getSubcat(), "U-14");
    }
}
