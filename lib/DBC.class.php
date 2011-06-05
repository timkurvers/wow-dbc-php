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
	const UINT          = 'L';
	
	/**
	 * Denotes a signed integer field type
	 */
	const INT           = 'l';
	
	/**
	 * Denotes a float field type
	 */
	const FLOAT         = 'f';
	
	/**
	 * Denotes a string field type
	 */
	const STRING        = 's';
	
	/**
	 * Denotes a localized string field type
	 */
	const STRING_LOC    = 'sl';
	
	/**
	 * Number of localization string fields
	 */
	const LOCALIZATION = 16;
	
	/**
	 * Holds a reference to this DBC on disk
	 */
	private $_handle = null;
	
	/**
	 * Holds path to this DBC on disk
	 */
	private $_path = null;
	
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
	private $_stringBlock = self::NULL_BYTE;
	
	/**
	 * Size of the string-block
	 */
	private $_stringBlockSize = 1;
	
	/**
	 * Whether this DBC is writable (enables adding records and strings)
	 */
	private $_writable = true;
	
	/**
	 * Constructs a new DBC instance from given path with an optional DBCMap to attach as the default
	 */
	public function __construct($path, DBCMap $map=null) {
		if(!is_file($path)) {
			throw new DBCException('DBC "'.$path.'" could not be found');
			return;
		}
		
		$this->_path = $path;
		
		$this->_handle = @fopen($path, 'r+b');
		if(!$this->_handle) {
			$this->_handle = @fopen($path, 'rb');
			$this->_writable = false;
			if(!$this->_handle) {
				throw new DBCException('DBC "'.$path.'" is not readable');
				return;
			}
		}
		$size = filesize($path);
		
		$sig = fread($this->_handle, 4);
		if($sig !== self::SIGNATURE) {
			throw new DBCException('DBC "'.$path.'" has an invalid signature and is therefore not valid');
			return;
		}
		if($size < self::HEADER_SIZE) {
			throw new DBCException('DBC "'.$path.'" has a malformed header');
			return;
		}
		
		list(, $this->_recordCount, $this->_fieldCount, $this->_recordSize, $this->_stringBlockSize) = unpack(self::UINT.'4', fread($this->_handle, 16));
		
		$offset = self::HEADER_SIZE + $this->_recordCount * $this->_recordSize;
		
		if($size < $offset) {
			throw new DBCException('DBC "'.$path.'" is short of '.($offset - $size).' bytes for '.$this->_recordCount.' records');
			return;
		}
		fseek($this->_handle, $offset);
		
		if($size < $offset + $this->_stringBlockSize) {
			throw new DBCException('DBC "'.$path.'" is short of '.($offset + $this->_stringBlockSize - $size).' bytes for string-block');
			return;
		}
		$this->_stringBlock = fread($this->_handle, $this->_stringBlockSize);
		
		$this->attach($map);
	}

	/**
	 * Destructs this DBC instance
	 */
	public function __destruct() {
		$this->finalize();
		if($this->_handle !== null) {
			fclose($this->_handle);
			$this->_handle = null;
		}
		$this->_index = null;
		$this->_map = null;
		$this->_stringBlock = null;
	}
	
	/**
	 * Finalizes this writable DBC, updating its header and writing the string block
	 */
	public function finalize() {
		$size = strlen($this->_stringBlock);
		if($this->_handle !== null && $this->_writable && $this->_stringBlockSize !== $size) {
			fseek($this->_handle, self::HEADER_SIZE + $this->_recordCount * $this->_recordSize);
			fwrite($this->_handle, $this->_stringBlock);
			
			$this->_stringBlockSize = $size;
			
			fseek($this->_handle, 16);
			fwrite($this->_handle, pack(self::UINT, $this->_stringBlockSize));
		}
	}
	
	/**
	 * Attaches a mapping
	 */
	public function attach(DBCMap $map=null) {
		$this->_map = null;
		if($map !== null) {
			$delta = $map->getFieldCount() - $this->getFieldCount();
			if($delta !== 0) {
				throw new DBCException('Mapping holds '.$map->getFieldCount().' fields, but DBC "'.$this->_path.'" expects '.$this->getfieldCount());
				return $this;
			}
			$this->_map = clone $map;
		}
		return $this;
	}
	
	/**
	 * Generates an index of this DBC consisting of ID/position pairs and optionally updates given ID to given position
	 */
	public function index($id=null, $position=null) {
		if($this->_index === null) {
			$this->_index = array();
			fseek($this->_handle, DBC::HEADER_SIZE);
			for($i=0; $i<$this->_recordCount; $i++) {
				list(,$rid) = unpack(self::UINT, fread($this->_handle, 4));
				$this->_index[$rid] = $i;
				fseek($this->_handle, $this->_recordSize - 4, SEEK_CUR);
			}
		}
		if($id !== null) {
			$prev = array_search($position, $this->_index, true);
			if($prev !== false) {
				unset($this->_index[$prev]);
			}
			$this->_index[$id] = $position;
		}
		return $this;
	}
	
	/**
	 * Adds a set of scalar values as a record or adds given arrays as records (nesting is allowed)
	 */
	public function add() {
		if(!$this->_writable || $this->_map === null) {
			throw new DBCException('Adding records requires DBC "'.$this->_path.'" to be writable and have a valid mapping attached');
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
		
		fseek($this->_handle, self::HEADER_SIZE + $this->_recordCount * $this->_recordSize);
		
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
				}else if($rule & DBCMap::STRING_MASK || $rule & DBCMap::STRING_LOC_MASK) {
					$offset = $this->addString($item);
					$value = pack(DBC::UINT, $offset);
				}
				fwrite($this->_handle, $value);
				if($rule & DBCMap::STRING_LOC_MASK) {
					fseek($this->_handle, DBC::LOCALIZATION * DBC::FIELD_SIZE, SEEK_CUR);
				}
			}
		}
		
		fseek($this->_handle, 4);
		fwrite($this->_handle, pack(self::UINT, ++$this->_recordCount));
	}
	
	/**
	 * Whether this DBC is writable
	 */
	public function isWritable() {
		return ($this->_handle !== null && $this->_writable);
	}
	
	/**
	 * Returns the handle to the DBC on disk
	 */
	public function getHandle() {
		return $this->_handle;
	}
	
	/**
	 * Returns the path to the DBC on disk
	 */
	public function getPath() {
		return $this->_path;
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
	 * Whether this DBC has a record identified by given ID (first field)
	 */
	public function hasRecordByID($id) {
		if($this->_index === null) {
			$this->index();
		}
		return (isset($this->_index[$id]));
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
		if($offset < 1 || $offset > strlen($this->_stringBlock)) {
			return null;
		}
		$length = strpos($this->_stringBlock, self::NULL_BYTE, $offset) - $offset;
		return substr($this->_stringBlock, $offset, $length);
	}
	
	/**
	 * Adds a string to the string-block and returns the offset in bytes
	 */
	public function addString($string) {
		if(!$this->_writable) {
			throw new DBCException('Adding strings requires DBC "'.$this->path.'" to be writable');
			return 0;
		}
		$offset = strlen($this->_stringBlock);
		$this->_stringBlock .= $string.self::NULL_BYTE;
		return $offset;
	}
	
	/**
	 * Returns the entire string-block
	 */
	public function getStringBlock() {
		return $this->_stringBlock;
	}
	
	/**
	 * Creates an empty DBC using the given mapping (will overwrite any existing DBCs)
	 */
	public static function create($file, $count) {
		$handle = @fopen($file, 'w+b');
		if(!$handle) {
			throw new DBCException('New DBC "'.$file.'" could not be created/opened for writing');
			return null;
		}
		
		$map = null;
		if($count instanceof DBCMap) {
			$map = $count;
			$count = $map->getFieldCount();	
		}
		
		fwrite($handle, self::SIGNATURE);
		fwrite($handle, pack(self::UINT.'4', 0, $count, $count * self::FIELD_SIZE, 1));
		fwrite($handle, self::NULL_BYTE);
		fclose($handle);
		
		$dbc = new self($file, $map);
		return $dbc;
	}
	
}
