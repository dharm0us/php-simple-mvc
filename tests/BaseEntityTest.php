<?php
require_once 'test_setup.php';
require_once 'PlayerEntity.php';
require_once 'CategoryEntity.php';
require_once 'RankingEntity.php';
class BaseEntityTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        TestUtils::setUpTestDB();
    }

    public function testTableCreation()
    {
        $this->assertEquals(
            $this->getActualTableCreationString('players'),
            $this->expectedPlayersTableCreationString()
        );
        $this->assertEquals(
            $this->getActualTableCreationString('categories'),
            $this->expectedCategoriesTableCreationString()
        );
        $this->assertEquals(
            $this->getActualTableCreationString('rankings'),
            $this->expectedRankingsTableCreationString()
        );
    }

    private function getActualTableCreationString($tableName)
    {
        try {
            $pdo = new PDO("mysql:host=127.0.0.1", DB_USER, DB_PASS);
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('USE ' . DB_NAME . ';');
        // Execute the query and fetch the result
        $result = $pdo->query("SHOW CREATE TABLE $tableName;");
        $row = $result->fetch(PDO::FETCH_ASSOC);

        $createTableStatement = $row['Create Table'];
        return $createTableStatement;
    }

    private function expectedRankingsTableCreationString()
    {
        return "CREATE TABLE `rankings` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdAt` int NOT NULL,
  `updatedAt` int NOT NULL,
  `createdBy` varchar(64) DEFAULT NULL,
  `updatedBy` varchar(64) DEFAULT NULL,
  `isActive` int DEFAULT '1',
  `isDeleted` int DEFAULT '0',
  `playerId` bigint DEFAULT NULL,
  `categoryId` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_rankings_playerId` (`playerId`),
  KEY `fk_rankings_categoryId` (`categoryId`),
  CONSTRAINT `fk_rankings_categoryId` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_rankings_playerId` FOREIGN KEY (`playerId`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    }

    private function expectedCategoriesTableCreationString()
    {
        return "CREATE TABLE `categories` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdAt` int NOT NULL,
  `updatedAt` int NOT NULL,
  `createdBy` varchar(64) DEFAULT NULL,
  `updatedBy` varchar(64) DEFAULT NULL,
  `isActive` int DEFAULT '1',
  `isDeleted` int DEFAULT '0',
  `cat` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subcat` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat` (`cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    }

    private function expectedPlayersTableCreationString()
    {
        return "CREATE TABLE `players` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdAt` int NOT NULL,
  `updatedAt` int NOT NULL,
  `createdBy` varchar(64) DEFAULT NULL,
  `updatedBy` varchar(64) DEFAULT NULL,
  `isActive` int DEFAULT '1',
  `isDeleted` int DEFAULT '0',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration` (`registration`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    }
}
