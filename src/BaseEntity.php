<?php

namespace SimpleMVC;

class BaseEntity {

	protected $id;
	protected $createdAt;
	protected $updatedAt;
	protected $createdBy;
	protected $updatedBy;
	protected $isActive;

	public function getIsActive() {
		return $this->isActive;
	}

	public function setIsActive($isActive) {
		$this->isActive = $isActive;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getCreatedBy() {
		return $this->createdBy;
	}

	public function setCreatedBy($createdBy) {
		$this->createdBy = $createdBy;
	}

	public function getUpdatedBy() {
		return $this->updatedBy;
	}

	public function setUpdatedBy($updatedBy) {
		$this->updatedBy = $updatedBy;
	}

	public function getCreatedAt() {
		return $this->createdAt;
	}

	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
	}

	public function getUpdatedAt() {
		return $this->updatedAt;
	}

	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}

	public function __construct($id = '') {
		if ($id) {
			$this->id = $id;
			$this->buildFromDb();
		}
	}

	public function buildFromDb($row = array()) {
		if (!$row) {
			$columnNames = $this->getCommaSeparatedColumnNames();
			$query = "select $columnNames from " . static::getTableName() . " where id = :id";
			$rows = DBP::getResultSet($query, array('id' => $this->id));
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

	private function fillThisWithDBValues($row) {
		$fieldNames = static::getColumnNames();
		foreach ($fieldNames as $fieldName) {
			if (isset($row[$fieldName])) {
				$setterMethod = "set" . ucfirst($fieldName);
				$this->$setterMethod($row[$fieldName]);
			}
		}
	}

	public function getAssocVersion() {
		$row = array();
		$fieldNames = static::getColumnNames();
		foreach ($fieldNames as $fieldName) {
			$getterMethod = "get" . ucfirst($fieldName);
			$row[$fieldName] = $this->$getterMethod();
		}
		return $row;
	}
	protected static function getTableName() {
		return null;
	}

	public function getFields() {
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

	private static function getColumnNames() {
		return array_keys(get_class_vars(get_called_class()));
	}

	public static function getCommaSeparatedColumnNames() {
		return implode(",", static::getColumnNames());
	}

	private static function getDefaultDefs(){
		$defs = array();
		$defs['id'] = 'bigint(20) primary key auto_increment';
		$defs['createdAt'] = 'int(11) NOT NULL';
		$defs['updatedAt'] = 'int(11) NOT NULL';
		$defs['createdBy'] = 'varchar(64) default null';
		$defs['updatedBy'] = 'varchar(64) default null';
		$defs['isActive'] = 'int(11) default 1';
		return $defs;
	}

	protected static function getColumnDefinitions() {
		return array();
	}

	protected static function getExistingColumns() {
		$cols = array();
		$query = "SELECT `COLUMN_NAME` as col FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`=:db_name AND `TABLE_NAME`=:table_name";
		$bindings = array('db_name' => DB_NAME, 'table_name' => static::getTableName());
		$rows = DBP::getResultSet($query,$bindings);
		foreach ($rows as $row) {
			$cols[] = $row['col'];
		}
		return $cols;
	}

	public static function createOrUpdateTable() {
        $existingColumnNames = static::getExistingColumns();
        $update = false;
        if($existingColumnNames) {
            $update = true;
        }
        $defs = self::getDefaultDefs();
        $tableName = static::getTableName();
        $extraDefs = static::getColumnDefinitions();
        $finalDefs = array_merge($defs,$extraDefs);
        if(!$update) {
            $query = "create table $tableName (";
            foreach ($finalDefs as $col => $def) {
                $query .= "$col $def,";
            }
            $query = rtrim($query, ",");
            $query .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8";
            DBP::runQuery($query);
        } else {
            $newColumnDefs = array();
            foreach($finalDefs as $col => $def) {
                if (!in_array($col, $existingColumnNames)) {
                    $newColumnDefs[$col] = $def;
                }
            }
            $query = "alter table $tableName";
            foreach($newColumnDefs as $col => $def) {
                $query .= " add column $col $def,";
            }
            $query = rtrim($query,",");
            //echo $query;die;
            DBP::runQuery($query);
        }
    }

	public function save() {
		if (!$this->id) {
			$this->createdAt = time();
			$this->updatedAt = $this->createdAt; 
			DBP::insert(static::getTableName(), $this->getFields());
			$this->id = DBP::getLastInsertId();
		} else {
			$this->updatedAt = time(); 
			DBP::update(static::getTableName(), $this->getFields(), $this->getId());
		}
	}

	public function delete() {
		DBP::delete(static::getTableName(), $this->getId());
	}

}

