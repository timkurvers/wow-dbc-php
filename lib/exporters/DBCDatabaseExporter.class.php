<?php
/**
 * World of Warcraft DBC Library
 * Copyright (c) 2011 Tim Kurvers <http://www.moonsphere.net>
 * 
 * This library allows creation, reading and export of World of Warcraft's
 * client-side database files. These so-called DBCs store information
 * required by the client to operate successfully and can be extracted
 * from the MPQ archives of the actual game client.
 * 
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * Alternatively, the contents of this file may be used under the terms of
 * the GNU General Public License version 3 license (the "GPLv3"), in which
 * case the provisions of the GPLv3 are applicable instead of the above.
 * 
 * @author	Tim Kurvers <tim@moonsphere.net>
 */

/**
 * Database Exporter
 */
class DBCDatabaseExporter implements IDBCExporter {
	
	/**
	 * Default maximum number of records per INSERT-query
	 */
	const RECORDS_PER_QUERY = 1000;
	
	/**
	 * Reference to PDO instance (if any)
	 */
	private $_pdo = null;
	
	/**
	 * Maximum number of records per INSERT-query
	 */
	public $recordsPerQuery = self::RECORDS_PER_QUERY;
	
	/**
	 * Constructs a new database exporter with given PDO instance (optional)
	 */
	public function __construct(PDO $pdo=null, $recordsPerQuery=self::RECORDS_PER_QUERY) {
		$this->setPDO($pdo);
	}
	
	/**
	 * Sets new PDO instance
	 */
	public function setPDO(PDO $pdo=null) {
		$this->_pdo = $pdo;
	}
	
	/**
	 * Retrieves the PDO instance
	 */
	public function getPDO() {
		return $this->_pdo;
	}
	
	/**
	 * Escapes given string (nested collections of strings allowed)
	 * @see	http://php.net/manual/en/function.mysql-real-escape-string.php#101248
	 */
	public function escape($string) {
		if(is_array($string)) {
			return array_map(__METHOD__, $string);
		}
		if($this->_pdo !== null) {
			return $this->_pdo->quote($string);
		}else{
			if(!empty($string) && is_string($string)) {
				return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string);
			}
		}
		return $string;
	}
	
	/**
	 * Joins given fields into a comma-separated string
	 */
	public function join(array $fields) {
		$copy = $fields;
		foreach($copy as &$value) {
			if(is_string($value)) {
				$value = '\''.$this->escape($value).'\'';
			}
			if($value === null) {
				$value = 'NULL';
			}
		}
		return implode(', ', $copy);
	}	
	
	/**
	 * Exports given DBC in SQL format to given target (defaults to output stream) using given table name
	 */
	public function export(DBC $dbc, $target=self::OUTPUT, $table='dbc') {
		$target = ($target === null) ? self::OUTPUT : $target;
		
		$map = $dbc->getMap();
		if($map === null) {
			throw new DBCException(self::NO_MAP);
			return;	
		}
		
		$sql = fopen($target, 'w+');
		
		$table = "`".$this->escape($table)."`";
		
		fwrite($sql, "DROP TABLE IF EXISTS ".$table.";".PHP_EOL.PHP_EOL);
		$dd = array();
		$fields = $map->getFields();
		foreach($fields as $name=>$rule) {
			$count = max($rule & 0xFF, 1);
			$null = false;
			if($rule & DBCMap::UINT_MASK) {
				$type = 'INT(11) UNSIGNED';
			}else if($rule & DBCMap::INT_MASK) {
				$type = 'INT(11) SIGNED';
			}else if($rule & DBCMap::FLOAT_MASK) {
				$type = 'FLOAT';
			}else if($rule & DBCMap::STRING_MASK || $rule & DBCMap::STRING_LOC_MASK) {
				$type = 'TEXT';
				$null = true;
			}
			for($i=1; $i<=$count; $i++) {
				$suffix = ($count > 1) ? $i : '';
				$dd[] = '	`'.$this->escape($name).$suffix.'` '.$type.' '.((!$null) ? 'NOT' : '').' NULL';
			}
		}
		reset($fields);
		fwrite($sql, "CREATE TABLE ".$table." (".PHP_EOL.implode(', '.PHP_EOL, $dd).', '.PHP_EOL.'	PRIMARY KEY (`'.key($fields).'`) '.PHP_EOL.');'.PHP_EOL.PHP_EOL);
		foreach($dbc as $i=>$record) {
			if($i % $this->recordsPerQuery === 0) {
				fwrite($sql, "INSERT INTO ".$table." VALUES ".PHP_EOL."	(");
			}else{
				fwrite($sql, ",".PHP_EOL."	(");
			}
			fwrite($sql, $this->join($record->extract()));
			if(($i+1) % $this->recordsPerQuery === 0 || $i === $dbc->getRecordCount() - 1) {
				fwrite($sql, ");".PHP_EOL.PHP_EOL);
			}else{
				fwrite($sql, ")");
			}
		}
		fclose($sql);
		
		return $target;
		
	}
	
}
