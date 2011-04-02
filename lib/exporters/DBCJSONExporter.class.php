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
 * JSON Exporter
 */
class DBCJSONExporter implements IDBCExporter {
	
	/**
	 * Exports given DBC in JSON format to given target (defaults to output stream)
	 */
	public function export(DBC $dbc, $target=self::OUTPUT) {
		$map = $dbc->getMap();
		if($map === null) {
			throw new DBCException(self::NO_MAP);
			return;	
		}
		
		$data = array(
			'fields'=>array(),
			'records'=>array()
			);
		
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
				$data['fields'][$name.$suffix] = $type;
			}
		}
		foreach($dbc as $record) {
			$data['records'][] = array_values($record->extract());
		}
		
		file_put_contents($target, json_encode($data));
	}
	
}
