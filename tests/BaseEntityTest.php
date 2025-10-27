<?php
require_once 'test_setup.php';
require_once 'PlayerEntity.php';
require_once 'CategoryEntity.php';
require_once 'RankingEntity.php';
require_once 'TestEntity.php';
class BaseEntityTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        TestUtils::setUpTestDB();
    }

    public function testTableUpdation()
    {
        $pdo = new PDO("mysql:host=127.0.0.1", DBG_USER, DBG_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $drop_column_sql = "alter table " . DB_NAME . ".players drop column registration";
        $pdo->exec($drop_column_sql);


        $this->assertNotEquals(
            $this->getActualTableCreationString('players'),
            $this->expectedPlayersTableCreationString()
        );
        PlayerEntity::createOrUpdateTable();

        $this->assertEquals(
            $this->getActualTableCreationString('players'),
            $this->expectedPlayersTableCreationString()
        );

        $drop_index_sql = "alter table " . DB_NAME . ".players drop index name_idx";
        $pdo->exec($drop_index_sql);

        $this->assertNotEquals(
            $this->getActualTableCreationString('players'),
            $this->expectedPlayersTableCreationString()
        );
        PlayerEntity::createOrUpdateTable();

        $this->assertEquals(
            $this->getActualTableCreationString('players'),
            $this->expectedPlayersTableCreationString()
        );

        $drop_fk_sql = "alter table " . DB_NAME . ".rankings drop foreign key fk_rankings_categoryId;";
        $pdo->exec($drop_fk_sql);

        $this->assertNotEquals(
            $this->getActualTableCreationString('rankings'),
            $this->expectedRankingsTableCreationString()
        );

        RankingEntity::createOrUpdateTable();

        $this->assertEquals(
            $this->getActualTableCreationString('rankings'),
            $this->expectedRankingsTableCreationString()
        );
    }

    public function testGetQueryStringForIndicesAndFKs()
    {
        $expected = 'alter table test_entity add FULLTEXT name_idx (name), add UNIQUE KEY registration_idx (registration), ADD CONSTRAINT fk_test_entity_playerId FOREIGN KEY (playerId) REFERENCES  players(id) ON DELETE NO ACTION ON UPDATE NO ACTION,ADD CONSTRAINT fk_test_entity_categoryId FOREIGN KEY (categoryId) REFERENCES  user_notes(id) ON DELETE NO ACTION ON UPDATE NO ACTION';
        $this->assertEquals(
            $expected,
            TestEntity::getQueryStringForIndicesAndFKs()
        );
    }

    public function testGetCreateTableStringWithoutAdditionalIndices()
    {
        $expected = 'create table test_entity (id bigint(20) primary key auto_increment,createdAt int(11) NOT NULL,updatedAt int(11) NOT NULL,createdBy varchar(64) default null,updatedBy varchar(64) default null,isActive int(11) default 1,isDeleted int(11) default 0,playerId bigint,categoryId bigint,registration varchar(20),name varchar(20)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        $this->assertEquals(
            $expected,
            TestEntity::getCreateTableStringWithoutAdditionalIndices()
        );
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

    public function testFindByFields()
    {
        $p = new PlayerEntity();
        $p->setName("suresh");
        $p->setRegistration("223456");
        $p->save();
        $rows = PlayerEntity::findByFields(array('name' => 'suresh', 'registration' => '223456'));
        $this->assertEquals($p->getName(), $rows[0]['name']);
        $this->assertEquals($p->getRegistration(), $rows[0]['registration']);
    }

    public function testFindById()
    {
        $p = new PlayerEntity();
        $p->setName("ramesh");
        $p->setRegistration("123456");
        $p->save();
        $newId = $p->getId();
        $row = PlayerEntity::findById($newId);
        $this->assertEquals($p->getName(), $row['name']);
        $this->assertEquals($p->getRegistration(), $row['registration']);

        $p->setIsDeleted(1);
        $p->save();
        $row = PlayerEntity::findById($newId);
        $this->assertEquals($row, array());

        $row = PlayerEntity::findById($newId, false); //fetch deleted rows also
        $this->assertEquals($p->getName(), $row['name']);
        $this->assertEquals($p->getRegistration(), $row['registration']);
        $this->assertTrue($p->getIsDeleted() === 1);
    }

    private function getActualTableCreationString($tableName)
    {
        try {
            $pdo = new PDO("mysql:host=127.0.0.1", DBR_USER, DBR_PASS);
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
  UNIQUE KEY `registration_idx` (`registration`),
  FULLTEXT KEY `name_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    }
}
