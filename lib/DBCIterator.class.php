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
 * Allows iteration over DBC records
 */
class DBCIterator implements Iterator {
	
	/**
	 * Holds a reference to the DBC being iterated
	 */
	private $_dbc = null;
	
	/**
	 * Current position in the DBC
	 */
	private $_pos = 0;
	
	/**
	 * Constructs a new iterator for given DBC
	 */
	public function __construct(DBC $dbc) {
		$this->_dbc = $dbc;
	}
	
	/**
	 * Destructs this iterator
	 */
	public function __destruct() {
		$this->_dbc = null;
	}
	
	/**
	 * Rewinds the position
	 */
	public function rewind() {
		$this->_pos = 0;
	}
	
	/**
	 * Returns the record at the current position (if any)
	 */
	public function current() {
		return $this->_dbc->getRecord($this->_pos);
	}
	
	/**
	 * Returns the current position
	 */
	public function key() {
		return $this->_pos;
	}
	
	/**
	 * Advances the position
	 */
	public function next() {
		$this->_pos++;
	}
	
	/**
	 * Retracts the position
	 */
	public function prev() {
		$this->_pos--;
	}
	
	/**
	 * Seeks to given position
	 */
	public function seek($pos) {
		$this->_pos = $pos;
	}
	
	/**
	 * Whether the DBC has a record at the current position
	 */
	public function valid() {
		return $this->_dbc->hasRecord($this->_pos);
	}
	
}
