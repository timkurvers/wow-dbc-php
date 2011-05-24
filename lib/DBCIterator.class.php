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
 * The contents of this file are subject to the MIT License, under which 
 * this library is licensed. See the LICENSE file for the full license.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY 
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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
