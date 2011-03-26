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
 * Represents a single record in a DBC
 */
class DBCRecord {
	
	/**
	 * Identifier (first field) for this record (if any)
	 */
	private $_id = null;
	
	/**
	 * Position of this record in the DBC
	 */
	private $_pos = 0;
	
	/**
	 * Offset of this record in the DBC in bytes
	 */
	private $_offset = 0;
	
	/**
	 * Data contained in this record in a byte-string
	 */
	private $_data = null;
	
	/**
	 * Reference to the associated DBC
	 */
	private $_dbc = null;
	
	/**
	 * Constructs a new record found at the given zero-based position in the associated DBC
	 */
	public function __construct(DBC $dbc, $pos) {
		$this->_dbc = $dbc;
		$this->_pos = $pos;
		
		$this->_offset = DBC::HEADER_SIZE + $pos * $dbc->getRecordSize();
		
		$handle = $dbc->getHandle();
		fseek($handle, $this->_offset);
		if($dbc->getRecordSize() > 0) {
			$this->_data = fread($handle, $dbc->getRecordSize());
		}
	}
	
	/**
	 * Extracts all data from this record using mappings in either the given or default DBCMap
	 */
	public function extract(DBCMap $map=null) {
		$map = ($map) ? $map : $this->_dbc->getMap();
		if($map === null) {
			return null;
		}
		$bytes = 0;
		$strings = array();
		$format = array();
		$fields = $map->getFields();
		foreach($fields as $name=>$rule) {
			$count = max($rule & 0xFF, 1);
			$bytes += DBC::FIELD_SIZE * $count;
			if($rule & DBCMap::UINT_MASK) {
				$format[] = DBC::UINT.$count.$name;
			}else if($rule & DBCMap::INT_MASK) {
				$format[] = DBC::INT.$count.$name;
			}else if($rule & DBCMap::FLOAT_MASK) {
				$format[] = DBC::FLOAT.$count.$name;
			}else if($rule & DBCMap::STRING_MASK) {
				$bytes += DBC::FIELD_SIZE * DBC::LOCALIZATION * $count;
				$format[] = DBC::UINT.$count.$name.'/@'.$bytes;
				$strings[] = $name;
			}
		}
		$format = implode('/', $format);
		$fields = unpack($format, $this->_data);
		foreach($strings as $string) {
			$fields[$string] = $this->_dbc->getString($fields[$string]);
		}
		return $fields;
	}
	
	/**
	 * Returns a collection of fields contained within this record as unsigned integers
	 */
	public function asArray() {
		return unpack(DBC::UINT.$this->_dbc->getFieldCount(), $this->_data);
	}
	
	/**
	 * Returns the identifier of this record (first field)
	 */
	public function getID() {
		if($this->_id === null) {
			$this->_id = $this->getUInt(0);
		}
		return $this->_id;
	}
	
	/**
	 * Returns the position of this record
	 */
	public function getPos() {
		return $this->_pos;
	}
	
	/**
	 * Reads data from this record for given field and length in bytes
	 */
	public function getData($field, $length=4) {
		return substr($this->_data, $field * DBC::FIELD_SIZE, $length);
	}
	
	/**
	 * Reads an unsigned integer for given field from this record
	 */
	public function getUInt($field) {
		if(!$this->_dbc->hasField($field)) {
			return null;
		}
		list(,$uint) = unpack(DBC::UINT, $this->getData($field));
		return $uint;
	}
	
	/**
	 * Reads a signed integer for given field from this record
	 */
	public function getInt($field) {
		if(!$this->_dbc->hasField($field)) {
			return null;
		}
		list(,$int) = unpack(DBC::INT, $this->getData($field));
		return $int;
	}
	
	/**
	 * Reads a float for given field from this record
	 */
	public function getFloat($field) {
		if(!$this->_dbc->hasField($field)) {
			return null;
		}
		list(,$float) = unpack(DBC::FLOAT, $this->getData($field));
		return $float;
	}
	
	/**
	 * Reads a string for given field from this record
	 */
	public function getString($field) {
		if(!$this->_dbc->hasField($field)) {
			return null;
		}
		list(,$offset) = unpack(DBC::UINT, $this->getData($field));
		return $this->_dbc->getString($offset);
	}
	
	/**
	 * Dumps field information for this record (optionally uses the default map attached to the associated DBC) 
	 */
	public function dump($useMap=false) {
		if(!$useMap || $this->_dbc->getMap() === null) {
			$fields = $this->asArray();
		}else{
			$fields = $this->extract();
		}
		var_dump($fields);
	}
	
}
