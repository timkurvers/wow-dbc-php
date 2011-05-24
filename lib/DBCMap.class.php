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
 * Defines a set of masks used in the DBC mappings
 */
define('DBC_UINT',          DBCMap::UINT_MASK);
define('DBC_INT',           DBCMap::INT_MASK);
define('DBC_FLOAT',         DBCMap::FLOAT_MASK);
define('DBC_STRING',        DBCMap::STRING_MASK);
define('DBC_STRING_LOC',    DBCMap::STRING_LOC_MASK);

/**
 * Mapping of fields for a DBC
 */
class DBCMap {
	
	/**
	 * Unsigned integer bit mask
	 */
	const UINT_MASK         = 0x0100;
	
	/**
	 * Signed integer bit mask
	 */
	const INT_MASK          = 0x0200;
	
	/**
	 * Float bit mask
	 */
	const FLOAT_MASK        = 0x0400;
	
	/**
	 * String bit mask
	 */
	const STRING_MASK       = 0x0800;
	
	/**
	 * Localized string bit mask
	 */
	const STRING_LOC_MASK   = 0x1000;
	
	/**
	 * Sample count
	 */
	const SAMPLES = 255;
	
	/**
	 * Holds all fields defined in this mapping in name/rule pairs
	 */
	private $_fields = null;
	
	/**
	 * Actual number of raw fields in this mapping
	 */
	private $_count = 0;
	
	/**
	 * Constructs a new mapping (with optional given fields)
	 */
	public function __construct(array $fields=null) {
		$this->_fields = ($fields !== null) ? $fields : array();
		foreach($this->_fields as $field=>&$rule) {
			if($rule === null) {
				$rule = self::UINT_MASK;
			}
			$rule = (int)$rule;
			$this->_count += self::countInBitmask($rule);
		}
	}
	
	/**
	 * Destructs this mapping
	 */
	public function __destruct() {
		$this->_fields = null;	
	}
	
	/**
	 * Returns the set of fields currently in this mapping
	 */
	public function getFields() {
		return $this->_fields;
	}
	
	/**
	 * Returns the actual amount of fields required in the DBC
	 */
	public function getFieldCount() {
		return $this->_count;
	}
	
	/**
	 * Returns the actual offset for given field
	 */
	public function getFieldOffset($field) {
		$suffix = 0;
		if(!isset($this->_fields[$field])) {
			if(preg_match('#(.+?)(\d+?)$#', $field, $match) === 1) {
				list(, $field, $suffix) = $match;
				$suffix = (int)$suffix - 1;
			}
		}
		
		if(!isset($this->_fields[$field])) {
			return -1;
		}
		
		$target = $field;
		
		$offset = 0;
		foreach($this->_fields as $field=>$rule) {
			if($target === $field) {
				$offset += self::countInBitmask($rule, $suffix);
				return $offset;
			}
			$offset += self::countInBitmask($rule);
		}
		return -1;
	}
	
	/**
	 * Adds a field using given type and count
	 */
	public function add($field, $type=DBC::UINT, $count=0) {
		$bitmask = $count;
		if($type === DBC::UINT) {
			$bitmask |= self::UINT_MASK;
		}else if($type === DBC::INT) {
			$bitmask |= self::INT_MASK;
		}else if($type === DBC::FLOAT) {
			$bitmask |= self::FLOAT_MASK;
		}else if($type === DBC::STRING) {
			$bitmask |= self::STRING_MASK;
		}else if($type === DBC::STRING_LOC) {
			$bitmask |= self::STRING_LOC_MASK;
		}
		if($this->exists($field)) {
			$this->_count -= self::countInBitmask($this->_fields[$field]);
		}
		$this->_count += self::countInBitmask($bitmask);
		$this->_fields[$field] = $bitmask;
	}
	
	/**
	 * Whether given field exists in this mapping
	 */
	public function exists($field) {
		return (isset($this->_fields[$field]));
	}
	
	/**
	 * Removes given field from the mapping provided it exists
	 */
	public function remove($field) {
		if($this->exists($field)) {
			$this->_count -= self::countInBitmask($this->_fields[$field]);
			unset($this->_fields[$field]);
		}
	}
	
	/**
	 * Exports this mapping in INI-format to given target (defaults to output stream) with given tab width in spaces
	 */
	public function toINI($target=IDBCExporter::OUTPUT, $tabWidth=4) {
		$maxlen = max(array_map('strlen', array_keys($this->_fields)));
		$spaces = ceil($maxlen / $tabWidth) * $tabWidth;
		
		$handle = fopen($target, 'w+');
		foreach($this->_fields as $field=>$bitmask) {
			$line = $field;
			$diff = $spaces - strlen($field);
			$line .= str_repeat("\t", ceil($diff / $tabWidth));
			$line .= "=\t";
			if($bitmask & self::UINT_MASK) {
				$line .= 'DBC_UINT';
			}else if($bitmask & self::INT_MASK) {
				$line .= 'DBC_INT';
			}else if($bitmask & self::FLOAT_MASK) {
				$line .= 'DBC_FLOAT';
			}else if($bitmask & self::STRING_MASK) {
				$line .= 'DBC_STRING';
			}else if($bitmask & self::STRING_LOC_MASK) {
				$line .= 'DBC_STRING_LOC';
			}
			$count = $bitmask & 0xFF;
			if($count > 1) {
				$line .= ' | '.$count;
			}
			$line .= PHP_EOL;
			
			fwrite($handle, $line);
		}
		fclose($handle);
	}
	
	/**
	 * Calculates the number of fields used up by the given bitmask
	 */
	public static function countInBitmask($bitmask, $upTo=PHP_INT_MAX) {
		$count = min(max($bitmask & 0xFF, 1), $upTo);
		if($bitmask & self::STRING_LOC_MASK) {
			$count += $count * DBC::LOCALIZATION;
		}
		return $count;
	}
	
	/**
	 * Constructs a new mapping from given INI-file
	 */
	public static function fromINI($ini) {
		return new self(parse_ini_file($ini));
	}
	
	/**
	 * Attempts to construct a map based on given DBC, predicting what each field could possibly hold by way of sampling
	 */
	public static function fromDBC(DBC $dbc, $attach=true) {
		
		$fields = $dbc->getFieldCount();
		$samples = ($dbc->getRecordCount() > self::SAMPLES) ? self::SAMPLES : $dbc->getRecordCount();
		
		$block = $dbc->getStringBlock();
		preg_match_all('#\0#', $block, $matches, PREG_OFFSET_CAPTURE);
		$strings = array();
		foreach($matches[0] as $offset) {
			$offset = (int)$offset[1] + 1;
			if($offset < strlen($block) - 1) {
				$strings[$offset] = true;
			}
		}
		
		$matrix = array_fill(1, $fields, 0);
		
		for($i=0; $i<$samples; $i++) {
			$record = $dbc->getRecord($i);
			$values = $record->asArray();
			foreach($values as $offset=>$value) {
				if($value < 0) {
					$matrix[$offset] += (1 << 0);
				}
				if(self::isProbableFloat($value)) {
					$matrix[$offset] += (1 << 8);
				}
				if(isset($strings[$value]) || $value === 0) {
					$matrix[$offset] += (1 << 16);
					if($value !== 0) {
						$matrix[$offset] |= (1 << 24);
					}
				}
			}
		}
		
		$map = new self();
		
		for($i=1; $i<=$fields; $i++) {
			$probs = $matrix[$i];
			$int = ($probs & 0x000000FF) / $samples;
			$flt = (($probs & 0x0000FF00) >> 8) / $samples;
			$str = (($probs & 0x00FF0000) >> 16) / $samples;
			$strbit = ($probs & 0xFF000000) >> 24;
			$field = 'field'.$i;
			if($flt > 0.6) {
				$type = DBC::FLOAT;
			}else if($strbit > 0 && $str > 0.99) {
				$type = DBC::STRING;
				if($i + DBC::LOCALIZATION <= $fields) {
					$type = DBC::STRING_LOC;
					for($j=$i+1; $j<=$i+DBC::LOCALIZATION; $j++) {
						$probs = $matrix[$j];
						$str = (($probs & 0x00FF0000) >> 16) / $samples;
						$strbit = ($probs & 0xFF000000) >> 24;
						if($str !== 1 || $strbit !== 0) {
							$type = DBC::STRING;
						}
					}
					if($type === DBC::STRING_LOC) {
						$i += DBC::LOCALIZATION;
					}
				}
			}else if($int > 0.01) {
				$type = DBC::INT;
			}else{
				$type = DBC::UINT;
			}
			$map->add($field, $type);
		}
		
		if($attach && $dbc->getMap() === null) {
			$dbc->attach($map);
		}
		
		return $map;
	}
	
	/**
	 * Whether given set of bits is a probable IEEE-754 single precision floating point number
	 * @see	http://stackoverflow.com/questions/2485388/heuristic-to-identify-if-a-series-of-4-bytes-chunks-of-data-are-integers-or-float/2953466#2953466
	 */
	public static function isProbableFloat($bits) {
		$sign = ($bits & 0x80000000) != 0;
		$exp = (($bits & 0x7F800000) >> 23) - 127;
		$mant = $bits & 0x007FFFFF;
		
		if(-30 <= $exp && $exp <= 30) {
			return true;
		}
		if($mant !== 0 && ($mant & 0x0000FFFF) == 0) {
			return true;
		}
		return false;
	}
	
}
