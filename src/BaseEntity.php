<?php

namespace SimpleMVC;

class BaseEntity
{

	protected int $id = 0;
	protected $createdAt;
	protected $updatedAt;
	protected $createdBy;
	protected $updatedBy;
	protected $isActive;
	protected $isDeleted;

	public function getIsDeleted()
	{
		return $this->isDeleted;
	}

	public function setIsDeleted($isDeleted)
	{
		$this->isDeleted = $isDeleted;
	}

	public function getIsActive()
	{
		return $this->isActive;
	}

	public function setIsActive($isActive)
	{
		$this->isActive = $isActive;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	public function setCreatedBy($createdBy)
	{
		$this->createdBy = $createdBy;
	}

	public function getUpdatedBy()
	{
		return $this->updatedBy;
	}

	public function setUpdatedBy($updatedBy)
	{
		$this->updatedBy = $updatedBy;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	public function __construct($id = '')
	{
		if ($id) {
			$this->id = $id;
			$this->buildFromDb();
		}
	}

	public function buildFromDb($row = array())
	{
		if (!$row) {
			$columnNames = $this->getCommaSeparatedColumnNames();
			$query = "select $columnNames from " . static::getTableName() . " where id = :id";
			$rows = DBG::getResultSet($query, array('id' => $this->id));
			if ($rows) {
				$row = $rows[0];
			}
		}

		if ($row) {
			$this->fillThisWithDBValues($row);
		} else {
			$this->id = null;
		}
	}

	public static function findByFields(array $fields, bool $ignoreDeleted = true): array
	{
		if (!$fields) {
			return array();
		}
		$query = 'select * from ' . static::getTableName() . ' where ';
		if ($ignoreDeleted) {
			$query .= '(isDeleted = 0 or isDeleted is null) and ';
		}
		$bindings = array();
		foreach ($fields as $fieldName => $fieldValue) {
			$query .= "$fieldName = :$fieldName and ";
			$bindings[$fieldName] = $fieldValue;
		}
		$query = substr($query, 0, strlen($query) - 4); //remove "and " from the end 
		return DBG::getResultSet($query, $bindings);
	}

	public static function findById(int $id, bool $ignoreDeleted = true): array
	{
		$rows = static::findByFields(array("id" => $id), $ignoreDeleted);
		if ($rows) {
			return $rows[0];
		}
		return array();
	}

	private function fillThisWithDBValues($row)
	{
		$fieldNames = static::getColumnNames();
		foreach ($fieldNames as $fieldName) {
			if (isset($row[$fieldName])) {
				$setterMethod = "set" . ucfirst($fieldName);
				$this->$setterMethod($row[$fieldName]);
			}
		}
	}

	public function getAssocVersion()
	{
		$row = array();
		$fieldNames = static::getColumnNames();
		foreach ($fieldNames as $fieldName) {
			$getterMethod = "get" . ucfirst($fieldName);
			$row[$fieldName] = $this->$getterMethod();
		}
		return $row;
	}

	public static function getTableName()
	{
		return null;
	}

	public function getFields()
	{
		/*
		 * For get_class_vars to work correctly - 
		 * all inheriting classes should have all those variables as protected which we want in getFields().
		 */
		$fields = get_class_vars(get_called_class());
		foreach ($fields as $key => $value) {
			$getterMethod = "get" . ucfirst($key);
			$fields[$key] = $this->$getterMethod();
		}
		if (isset($fields['id'])) {
			unset($fields['id']);
		}
		return $fields;
	}

	private static function getColumnNames()
	{
		return array_keys(get_class_vars(get_called_class()));
	}

	public static function getCommaSeparatedColumnNames()
	{
		return implode(",", static::getColumnNames());
	}

	private static function getDefaultDefs()
	{
		$defs = array();
		$defs['id'] = 'bigint(20) primary key auto_increment';
		$defs['createdAt'] = 'int(11) NOT NULL';
		$defs['updatedAt'] = 'int(11) NOT NULL';
		$defs['createdBy'] = 'varchar(64) default null';
		$defs['updatedBy'] = 'varchar(64) default null';
		$defs['isActive'] = 'int(11) default 1';
		$defs['isDeleted'] = 'int(11) default 0';
		return $defs;
	}

	protected static function getIndexDefinitions()
	{
		return array();
	}

	protected static function getColumnDefinitions()
	{
		return array();
	}

	public static function getFKs()
	{
		return array();
	}

	protected static function getExistingFKs()
	{
		$fks = array();
		$query = "SELECT 
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME 
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE 
    REFERENCED_TABLE_NAME IS NOT NULL 
    AND TABLE_SCHEMA = :db_name 
    AND TABLE_NAME = :table_name;";
		$bindings = array('db_name' => DB_NAME, 'table_name' => static::getTableName());
		$rows = DBG::getResultSet($query, $bindings);
		foreach ($rows as $row) {
			$columnName = $row['COLUMN_NAME'];
			$refTable = $row['REFERENCED_TABLE_NAME'];
			$refColumn = $row['REFERENCED_COLUMN_NAME'];
			$fk = new ForeignKey(columnName: $columnName, refTable: $refTable, refColumn: $refColumn);
			$fks[] = $fk;
		}
		return $fks;
	}

	protected static function getExistingIndices()
	{
		$indices = array();
		$query = "SELECT `INDEX_NAME` FROM `INFORMATION_SCHEMA`.`STATISTICS` WHERE `TABLE_SCHEMA`=:db_name AND `TABLE_NAME`=:table_name";
		$bindings = array('db_name' => DB_NAME, 'table_name' => static::getTableName());
		$rows = DBG::getResultSet($query, $bindings);
		foreach ($rows as $row) {
			$indices[] = $row['INDEX_NAME'];
		}
		return $indices;
	}

	protected static function getExistingColumns()
	{
		$cols = array();
		$query = "SELECT `COLUMN_NAME` as col FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`=:db_name AND `TABLE_NAME`=:table_name";
		$bindings = array('db_name' => DB_NAME, 'table_name' => static::getTableName());
		$rows = DBG::getResultSet($query, $bindings);
		foreach ($rows as $row) {
			$cols[] = $row['col'];
		}
		return $cols;
	}

	private static function generateFKString($fkList, $forUpdate)
	{
		$table = static::getTableName();
		$fkText = "";
		foreach ($fkList as $fk) {
			$col = $fk->columnName;
			$refTable = $fk->refTable;
			$refColumn = $fk->refColumn;
			$keyName = "fk_" . $table . "_" . $col;
			$prefix = "";
			if ($forUpdate) {
				$prefix = "ADD";
			}
			$fkText .= "$prefix CONSTRAINT $keyName FOREIGN KEY ($col) REFERENCES  $refTable($refColumn) ON DELETE NO ACTION ON UPDATE NO ACTION,";
		}
		$fkText = rtrim($fkText, ",");
		return $fkText;
	}


	private static function createTable($columnDefs, $indexDefs, $fkDefs)
	{
		$tableName = static::getTableName();
		$query = "create table $tableName (";
		foreach ($columnDefs as $col => $def) {
			$query .= "$col $def,";
		}
		foreach ($indexDefs as $index) {
			$index_type = $index[0];
			$index_name = $index[1];
			$column_name = $index[2];
			$query .= "$index_type $index_name ($column_name),";
		}
		$query .= static::generateFKString(fkList: $fkDefs, forUpdate: false);
		$query = rtrim($query, ",");
		$query .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		DBG::runQuery($query);
	}

	private static function updateTable($columnDefs, $indexDefs, $fkDefs)
	{
		$existingColumnNames = static::getExistingColumns();
		$existingIndexNames = static::getExistingIndices();
		$existingFKs = static::getExistingFKs();
		$tableName = static::getTableName();

		$missingColumnDefs = array();
		foreach ($columnDefs as $col => $def) {
			if (!in_array($col, $existingColumnNames)) {
				$missingColumnDefs[$col] = $def;
			}
		}
		$query = "alter table $tableName";
		foreach ($missingColumnDefs as $col => $def) {
			$query .= " add column $col $def,";
		}

		foreach ($indexDefs as $index) {
			$index_type = $index[0];
			$index_name = $index[1];
			$column_name = $index[2];
			if (!in_array($index_name, $existingIndexNames)) {
				$query .= " add $index_type $index_name ($column_name),";
			}
		}

		$missing_fks = array();
		foreach ($fkDefs as $fk) {
			$missing = true;
			foreach ($existingFKs as $efk) {
				if ($fk->refTable == $efk->refTable && $fk->refColumn == $efk->refColumn) {
					$missing = false;
					break;
				}
			}
			if ($missing) {
				$missing_fks[] = $fk;
			}
		}
		if ($missing_fks) {
			$query .= " " . static::generateFKString(fkList: $missing_fks, forUpdate: true);
		}
		$query = rtrim($query, ",");
		DBG::runQuery($query);
	}

	public static function createOrUpdateTable()
	{
		$baseColumnDefs = self::getDefaultDefs();
		$extraColumnDefs = static::getColumnDefinitions();
		$finalColumnDefs = array_merge($baseColumnDefs, $extraColumnDefs);
		$indexDefs = static::getIndexDefinitions();
		$fkDefs = static::getFKs();
		if (static::getExistingColumns()) {
			self::updateTable($finalColumnDefs, $indexDefs, $fkDefs);
		} else {
			self::createTable($finalColumnDefs, $indexDefs, $fkDefs);
		}
	}

	public function save()
	{
		if (!$this->id) {
			$this->createdAt = time();
			$this->updatedAt = $this->createdAt;
			DBG::insert(static::getTableName(), $this->getFields());
			$this->id = DBG::getLastInsertId();
		} else {
			$this->updatedAt = time();
			DBG::update(static::getTableName(), $this->getFields(), $this->getId());
		}
	}

	public function delete()
	{
		DBG::delete(static::getTableName(), $this->getId());
	}
}
