<?php

use SimpleMVC\DBA;
use SimpleMVC\DBR;
use SimpleMVC\DBW;

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

    public function testPrivileges()
    {
        $adminQuery_1 = "create table testing_table (a int, b int)";
        $adminQuery_2 = "drop table testing_table";
        $writeQuery = "insert into testing_table values (1,2)";
        $readQuery = "select * from testing_table";

        $exception = null;
        try {
            DBR::runQuery($adminQuery_1);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertTrue($exception instanceof PDOException);
        $this->assertTrue(str_starts_with($exception->getMessage(), 'SQLSTATE[42000]: Syntax error or access violation: 1142 CREATE command denied to user'));

        $exception = null;
        try {
            DBW::runQuery($adminQuery_1);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertTrue($exception instanceof PDOException);
        $this->assertTrue(str_starts_with($exception->getMessage(), 'SQLSTATE[42000]: Syntax error or access violation: 1142 CREATE command denied to user'));

        $exception = null;
        DBA::runQuery($adminQuery_1);

        try {
            DBR::runQuery($writeQuery);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertTrue($exception instanceof PDOException);
        $this->assertTrue(str_starts_with($exception->getMessage(), 'SQLSTATE[42000]: Syntax error or access violation: 1142 INSERT command denied to user'));

        DBW::runQuery($writeQuery);
        DBA::runQuery($writeQuery);
        $rows = DBR::getResultSet($readQuery);
        $this->assertEquals(2, count($rows));
        $this->assertEquals(1, $rows[0]['a']);
        $this->assertEquals(2, $rows[1]['b']);

        $exception = null;
        try {
            DBR::runQuery($adminQuery_2);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertTrue($exception instanceof PDOException);
        $this->assertTrue(str_starts_with($exception->getMessage(), 'SQLSTATE[42000]: Syntax error or access violation: 1142 DROP command denied to user'));

        $exception = null;
        try {
            DBW::runQuery($adminQuery_2);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertTrue($exception instanceof PDOException);
        $this->assertTrue(str_starts_with($exception->getMessage(), 'SQLSTATE[42000]: Syntax error or access violation: 1142 DROP command denied to user'));

        DBW::runQuery($writeQuery);
        DBA::runQuery($writeQuery);
        $rows = DBW::getResultSet($readQuery);
        $this->assertEquals(4, count($rows));
        $this->assertEquals(1, $rows[2]['a']);
        $this->assertEquals(2, $rows[3]['b']);

        DBA::runQuery($adminQuery_2);
    }


    public function testInsertMultiple()
    {
        DBA::runQuery("drop table if exists test_table");
        DBA::runQuery("create table test_table (a int, b int)");
        $fields_arr = array();
        $fields_arr[] = array("a" => 9, "b" => 10);
        $fields_arr[] = array("a" => 11, "b" => 12);
        DBW::insertMultiple("test_table", $fields_arr);
        $rows = DBR::getResultSet("select * from test_table");
        $this->assertEquals(2, count($rows));
        $this->assertEquals($fields_arr, $rows);

        DBW::runQuery("delete from " . CategoryEntity::getTableName());
        $currTime = time();
        $cat1 = new CategoryEntity();
        $cat1->setCat("Boys");
        $cat1->setSubcat("U-12");
        $cat1->setCreatedAt($currTime);
        $cat1->setUpdatedAt($currTime);

        $cat2 = new CategoryEntity();
        $cat2->setCat("Girls");
        $cat2->setSubcat("U-14");
        $cat2->setCreatedAt($currTime);
        $cat2->setUpdatedAt($currTime);

        $categories = array($cat1->getFields(), $cat2->getFields());
        DBW::insertMultiple(CategoryEntity::getTableName(), $categories);
        $rows = DBR::getResultSet("select * from " . CategoryEntity::getTableName());
        $this->assertEquals($cat1->getCat(), "Boys");
        $this->assertEquals($cat1->getSubcat(), "U-12");
        $this->assertEquals($cat2->getCat(), "Girls");
        $this->assertEquals($cat2->getSubcat(), "U-14");
    }
}
