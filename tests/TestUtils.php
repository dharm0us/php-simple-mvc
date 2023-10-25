<?php
$dir = dirname(__FILE__);
chdir($dir . '/../');
require_once 'env.php.test';
require_once 'common.inc.php';
require_once 'PlayerEntity.php';
require_once 'CategoryEntity.php';
require_once 'RankingEntity.php';

class TestUtils
{
    public static function setUpTestDB()
    {
        try {
            $pdo = new PDO("mysql:host=127.0.0.1", 'root', 'root1234');
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $creds_sql = file_get_contents('tests/test_data/user_creds.sql');
        $pdo->exec($creds_sql);

        $drop_db_sql = "drop DATABASE if exists " . DB_NAME;
        $pdo->exec($drop_db_sql);
        $create_db_sql = "CREATE DATABASE if not exists " . DB_NAME . " /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */";
        $pdo->exec($create_db_sql);
        $pdo->exec('USE ' . DB_NAME . ';');

        PlayerEntity::createOrUpdateTable();
        CategoryEntity::createOrUpdateTable();
        RankingEntity::createOrUpdateTable();
    }
}
