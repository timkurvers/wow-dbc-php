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
 * Represents a World of Warcraft DBC
 */
class DBC implements IteratorAggregate {
	
	/**
	 * Defines signature for a DBC file
	 */
	const SIGNATURE = 'WDBC';
	
	/**
	 * Defines the size of the header in bytes 
	 */
	const HEADER_SIZE = 20;
	
	/**
	 * Defines the field size in bytes
	 */
	const FIELD_SIZE = 4;
	
	/**
	 * Convenience NULL-byte constant
	 */
	const NULL_BYTE = "\0";
	
	/**
	 * Denotes an unsigned integer field type
	 */
	const UINT			= 'L';
	
	/**
	 * Denotes a signed integer field type
	 */
	const INT			= 'l';
	
	/**
	 * Denotes a float field type
	 */
	const FLOAT			= 'f';
	
	/**
	 * Denotes a string field type
	 */
	const STRING		= 's';
	
	/**
	 * Number of localization string fields
	 */
	const LOCALIZATION = 16;
	
	/**
	 * Holds a reference to this DBC on disk
	 */
	private $_handle = null;
	
	/**
	 * Filesize of this DBC on disk in bytes
	 */
	private $_size = 0;
	
	/**
	 * Represents the index for the records in this DBC paired by ID/position
	 */
	private $_index = null;
	
	/**
	 * Amount of records in this DBC
	 */
	private $_recordCount = 0;
	
	/**
	 * Record size in bytes
	 */
	private $_recordSize = 0;
	
	/**
	 * Amount of fields in this DBC
	 */
	private $_fieldCount = 0;
	
	/**
	 * Reference to the attached map (if any)
	 */
	private $_map = null;
	
	/**
	 * String-block contains all strings defined in the DBC file
	 */
	private $_stringBlock = null;
	
	/**
	 * Size of the string-block
	 */
	private $_stringBlockSize = 0;
	
	/**
	 * Byte-offset of the string-block
	 */
	private $_stringBlockOffset = 0;
	
	/**
	 * Constructs a new DBC instance from given path with an optional DBCMap to attach as the default
	 */
	public function __construct($path, DBCMap $map=null) {
		if(!is_file($path)) {
			throw new DBCException('DBC "'.$path.'" could not be found');
			return;
		}
		$this->_handle = fopen($path, 'rb');
		$this->_size = filesize($path);
		
		$sig = fread($this->_handle, 4);
		if($sig !== self::SIGNATURE) {
			throw new DBCException('DBC "'.$path.'" has an invalid signature and is therefore not valid');
			return;
		}
		if($this->_size < 20) {
			throw new DBCException('DBC "'.$path.'" has a malformed header');
			return;
		}
		
		list(, $this->_recordCount, $this->_fieldCount, $this->_recordSize, $this->_stringBlockSize) = unpack(self::UINT.'4', fread($this->_handle, 16));
		
		$this->_stringBlockOffset = self::HEADER_SIZE + $this->_recordCount * $this->_recordSize;
		fseek($this->_handle, $this->_stringBlockOffset);
		if($this->_size < $this->_stringBlockOffset) {
			throw new DBCException('DBC "'.$path.'" is short of '.($this->_stringBlockOffset - $this->_size).' bytes for '.$this->_recordCount.' records');
			return;
		}
		if($this->_stringBlockSize > 0) {
			$this->_stringBlock = fread($this->_handle, $this->_size - $this->_stringBlockOffset);
		}
		
		$this->attach($map);
	}

	/**
	 * Destructs this DBC instance
	 */
	public function __destruct() {
		if($this->_handle !== null) {
			fclose($this->_handle);
			$this->_handle = null;
		}
		$this->_index = null;
		$this->_map = null;
		$this->_stringBlock = null;
	}
	
	/**
	 * Attaches a mapping 
	 */
	public function attach(DBCMap $map=null) {
		$this->_map = $map;
	}
	
	/**
	 * Generates an index of this DBC consisting of ID/position pairs
	 */
	public function index() {
		if($this->_index !== null) {
			return;
		}
		$this->_index = array();
		fseek($this->_handle, DBC::HEADER_SIZE);
		for($i=0; $i<$this->_recordCount; $i++) {
			list(,$id) = unpack(self::UINT, fread($this->_handle, 4));
			$this->_index[$id] = $i;
			fseek($this->_handle, $this->_recordSize - 4, SEEK_CUR);
		}
	}
	
	/**
	 * Returns the handle to the DBC on disk
	 */
	public function getHandle() {
		return $this->_handle;
	}
	
	/**
	 * Returns the size of this DBC on disk
	 */
	public function getSize() {
		return $this->_size;
	}
	
	/**
	 * Returns the amount of records in this DBC
	 */
	public function getRecordCount() {
		return $this->_recordCount;
	}
	
	/**
	 * Returns the size of each record in bytes
	 */	
	public function getRecordSize() {
		return $this->_recordSize;
	}
	
	/**
	 * Fetches a record by zero-based position (if any)
	 */
	public function getRecord($pos) {
		if($this->hasRecord($pos)) {
			return new DBCRecord($this, $pos);
		}
		return null;
	}
	
	/**
	 * Fetches a record by ID (first field) and will ensure the index has been generated
	 */
	public function getRecordByID($id) {
		if($this->_index === null) {
			$this->index();
		}
		if(isset($this->_index[$id])) {
			return new DBCRecord($this, $this->_index[$id]);
		}
		return null;
	}
	
	/**
	 * Whether this DBC has a record at the given zero-based position
	 */
	public function hasRecord($pos) {
		return ($pos >= 0 && $pos < $this->_recordCount);
	}
	
	/**
	 * Returns the amount of fields in this DBC
	 */
	public function getFieldCount() {
		return $this->_fieldCount;
	}
	
	/**
	 * Whether the field given by the zero-based offset exists in this DBC
	 */
	public function hasField($field) {
		return ($field >= 0 && $field < $this->_fieldCount);
	}
	
	/**
	 * Returns the map attached to this DBC (if any)
	 */
	public function getMap() {
		return $this->_map;
	}
	
	/**
	 * Generates a new iterator to iterate over the records in this DBC
	 */
	public function getIterator() {
		return new DBCIterator($this);
	}
	
	/**
	 * Returns the string found in the string-block given by the offset in bytes (if any)
	 */
	public function getString($offset) {
		if($this->_stringBlock === null || $offset < 1 || $offset > strlen($this->_stringBlock)) {
			return null;
		}
		$length = strpos($this->_stringBlock, self::NULL_BYTE, $offset) - $offset;
		return substr($this->_stringBlock, $offset, $length);
	}
	
	/**
	 * Returns the entire string-block
	 */
	public function getStringBlock() {
		return $this->_stringBlock;
	}
	
}
