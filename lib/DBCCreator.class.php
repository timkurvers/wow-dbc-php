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
 * Allows creation of a DBC
 */
class DBCCreator {
	
	/**
	 * Amount of records in the DBC being created
	 */
	private $_recordCount = 0;

	/**
	 * Amount of fields in the DBC being created
	 */
	private $_fieldCount = 0;
	
	/**
	 * String-block of the DBC being created
	 */
	private $_stringBlock = DBC::NULL_BYTE;
	
	/**
	 * Handle to the DBC on disk
	 */
	private $_handle = null;
	
	/**
	 * Map used to read field information
	 */
	private $_map = null;
	
	/**
	 * Constructs a new DBC creator, creates a new DBC at given path (overwrites an existing one) and uses the given map to read field information
	 */
	public function __construct($path, DBCMap $map) {
		$this->_handle = fopen($path, 'wb');
		fwrite($this->_handle, DBC::SIGNATURE);
		fseek($this->_handle, DBC::HEADER_SIZE);
		
		$this->_map = $map;
		
		$fields = $this->_map->getFields();
		foreach($fields as $name=>$rule) {
			$count = max($rule & 0xFF, 1);
			for($i=0; $i<$count; $i++) {
				$this->_fieldCount++;
				if($rule & DBCMap::STRING_MASK) {
					$this->_fieldCount += DBC::LOCALIZATION;
				}
			}
		}
	}
	
	/**
	 * Destructs this DBC creator (will finalize the DBC being created)
	 */
	public function __destruct() {
		$this->finalize();
		$this->_stringBlock = null;
		$this->_mapping = null;
	}
	
	/**
	 * Adds a set of scalar values as a record or adds given arrays as records (nesting is allowed)
	 */
	public function add() {
		if($this->_handle === null) {
			return $this;
		}
		$args = func_get_args();
		if(isset($args[0])) {
			$scalars = true;
			foreach($args as $arg) {
				if($scalars && !is_scalar($arg)) {
					$scalars = false;
				}
				if(is_array($arg)) {
					call_user_func_array(array($this, __METHOD__), $arg);
				}
			}
			if($scalars) {
				$this->_add($args);
			}
		}
		return $this;
	}
	
	/**
	 * Adds the given record of scalar values to the DBC being created
	 */
	private function _add(array $record) {
		$fields = $this->_map->getFields();
		
		foreach($fields as $name=>$rule) {
			$count = max($rule & 0xFF, 1);
			for($i=0; $i<$count; $i++) {
				$item = array_shift($record);
				if($item === null) {
					$value = pack(DBC::UINT, 0);
				}else if($rule & DBCMap::UINT_MASK) {
					$value = pack(DBC::UINT, $item);
				}else if($rule & DBCMap::INT_MASK) {
					$value = pack(DBC::INT, $item);
				}else if($rule & DBCMap::FLOAT_MASK) {
					$value = pack(DBC::FLOAT, $item);
				}else if($rule & DBCMap::STRING_MASK) {
					$offset = strlen($this->_stringBlock);
					$this->_stringBlock .= $item.DBC::NULL_BYTE;
					$value = pack(DBC::UINT, $offset);
				}
				fwrite($this->_handle, $value);
				if($rule & DBCMap::STRING_MASK) {
					fseek($this->_handle, DBC::LOCALIZATION * DBC::FIELD_SIZE, SEEK_CUR);
				}
			}
		}
		
		$this->_recordCount++;
	}
	
	/**
	 * Finalizes the DBC being created, writes the string-block and header and finally closes the file handle
	 */
	public function finalize() {
		if($this->_handle === null) {
			return $this;
		}
		
		if(strlen($this->_stringBlock) > 0) {
			fwrite($this->_handle, $this->_stringBlock);
		}
		
		fseek($this->_handle, strlen(DBC::SIGNATURE));
		fwrite($this->_handle, pack(DBC::UINT.'4', $this->_recordCount, $this->_fieldCount, $this->_fieldCount * DBC::FIELD_SIZE, strlen($this->_stringBlock)));
		fclose($this->_handle);
		$this->_handle = null;
		return $this;
	}

}
	