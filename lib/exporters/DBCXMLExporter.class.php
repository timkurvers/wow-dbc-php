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
 * XML Exporter
 */
class DBCXMLExporter implements IDBCExporter {
	
	/**
	 * Exports given DBC in XML format to given target (defaults to output stream)
	 */
	public function export(DBC $dbc, $target=self::OUTPUT) {
		$map = $dbc->getMap();
		if($map === null) {
			throw new DBCException(self::NO_MAP);
			return;	
		}
		
		$dom = new DOMDocument('1.0');
		$dom->formatOutput = true;
		
		$edbc = $dom->appendChild($dom->createElement('dbc'));
		$efields = $edbc->appendChild($dom->createElement('fields'));
		$erecords = $edbc->appendChild($dom->createElement('records'));
		
		$fields = $map->getFields();
		foreach($fields as $name=>$rule) {
			$count = max($rule & 0xFF, 1);
			if($rule & DBCMap::UINT_MASK) {
				$type = 'uint';
			}else if($rule & DBCMap::INT_MASK) {
				$type = 'int';
			}else if($rule & DBCMap::FLOAT_MASK) {
				$type = 'float';
			}else if($rule & DBCMap::STRING_MASK || $rule & DBCMap::STRING_LOC_MASK) {
				$type = 'string';
			}
			for($i=1; $i<=$count; $i++) {
				$suffix = ($count > 1) ? $i : '';
				$efields->appendChild($dom->createElement($name.$suffix, $type));
			}
		}
		foreach($dbc as $i=>$record) {
			$pairs = $record->extract();
			$erecord = $erecords->appendChild($dom->createElement('record'));
			foreach($pairs as $field=>$value) {
				$erecord->appendChild($dom->createElement($field, $value));
			}
		}
		
		$data = $dom->saveXML();
		
		file_put_contents($target, $data);
	}
	
}
