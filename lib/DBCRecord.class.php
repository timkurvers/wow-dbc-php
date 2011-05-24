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
	 * Destructs this record
	 */
	public function __destruct() {
		$this->_id = null;
		$this->_data = null;
		$this->_dbc = null;
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
				$format[] = DBC::UINT.$count.$name;
				$strings[] = $name;
			}else if($rule & DBCMap::STRING_LOC_MASK) {
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
	 * Reads data from this record for given field of given type
	 */
	public function get($field, $type=DBC::UINT) {
		if(is_string($field)) {
			if($map = $this->_dbc->getMap()) {
				$field = $map->getFieldOffset($field);
			}else{
				throw new DBCException('Addressing fields through string values requires DBC "'.$this->_dbc->getPath().'" to have a valid mapping attached');
				return null;
			}
		}
		
		$offset = $field * DBC::FIELD_SIZE;
		if($offset >= strlen($this->_data)) {
			return null;
		}
		
		if($string = ($type === DBC::STRING || $type === DBC::STRING_LOC)) {
			$type = DBC::UINT;
		}
		list(,$value) = unpack($type, substr($this->_data, $offset, DBC::FIELD_SIZE));
		if($string) {
			$value = $this->_dbc->getString($value);
		}
		return $value;
	}
	
	/**
	 * Writes data into this record for given field as given type
	 */
	public function set($field, $value, $type=DBC::UINT) {
		if(!$this->_dbc->isWritable()) {
			throw new DBCException('Modifying records requires DBC "'.$this->_dbc->getPath().'" to be writable');
			return $this;
		}
		
		if(is_string($field)) {
			if($map = $this->_dbc->getMap()) {
				$field = $map->getFieldOffset($field);
			}else{
				throw new DBCException('Addressing fields through string values requires DBC "'.$this->_dbc->getPath().'" to have a valid mapping attached');
				return $this;
			}
		}
		
		$offset = $field * DBC::FIELD_SIZE;
		if($offset >= strlen($this->_data)) {
			return $this;
		}
		
		$handle = $this->_dbc->getHandle();
		
		if($string = ($type === DBC::STRING || $type === DBC::STRING_LOC)) {
			$value = $this->_dbc->addString($value);
			$type = DBC::UINT;
		}
		$value = pack($type, $value);
		
		fseek($handle, $this->_offset + $offset);
		fwrite($handle, $value);
		$this->_data = substr_replace($this->_data, $value, $offset, 4);
		
		if($field === 0) {
			$this->_dbc->index($value, $this->_pos);
		}
		return $this;
	}
	
	/**
	 * Reads an unsigned integer for given field from this record
	 */
	public function getUInt($field) {
		return $this->get($field, DBC::UINT);
	}
	
	/**
	 * Writes an unsigned integer to given field into this record
	 */
	public function setUInt($field, $uint) {
		return $this->set($field, $uint, DBC::UINT);
	}
	
	/**
	 * Reads a signed integer for given field from this record
	 */
	public function getInt($field) {
		return $this->get($field, DBC::INT);
	}
	
	/**
	 * Writes a signed integer for given field into this record
	 */
	public function setInt($field, $int) {
		return $this->set($field, $int, DBC::INT);
	}
	
	/**
	 * Reads a float for given field from this record
	 */
	public function getFloat($field) {
		return $this->get($field, DBC::FLOAT);
	}
	
	/**
	 * Writes a float for given field into this record
	 */
	public function setFloat($field, $float) {
		return $this->set($field, $float, DBC::FLOAT);
	}
	
	/**
	 * Reads a string for given field from this record
	 */
	public function getString($field) {
		return $this->get($field, DBC::STRING);
	}
	
	/**
	 * Writes a string for given field into this record
	 */
	public function setString($field, $string) {
		return $this->set($field, $string, DBC::STRING);
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
