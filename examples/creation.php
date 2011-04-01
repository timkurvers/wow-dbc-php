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

error_reporting(E_ALL | E_STRICT);

require('../lib/bootstrap.php');

/**
 * This example shows how to create a DBC-file from scratch using DBC mappings
 */

// File we'll be using in this example
$file = './dbcs/Sample.dbc';

// Load map from given INI-file (ensure read-access)
$map = DBCMap::fromINI('./maps/Sample.ini');

// Attempt to create a new DBC at given path (ensure write-access) with given map
$dbc = DBC::create($file, $map);

// Adding a single record
$dbc->add(1, 'John', 100, 1.80, 2, 0);

// Adding multiple records in one call
$dbc->add(array(2, 'Tim', 1337, 1.80, 1, 0), array(3, 'Pete', -10, 1.55, 1, 2));

// Failing to match the defined fields in the map (six, in this example) will silently and gracefully continue
// The following will leave 100, 200, 0, 'Hello' out of the actual record
$dbc->add(11, 'I am providing too many fields', 123, 1.20, 0, 0, 100, 200, 0, 'Hello');
// The following will append 0, 0, 0, 0 to the actual record
$dbc->add(12, 'I am providing too little fields');

// Setting up a collection of records
$records = array();
$records[] = array(4, 'Helen',	100,	1.80,	0,	0);
$records[] = array(8, 'Frank',	1337,	1.73,	0,	0);
$records[] = array(10, 'Brad',	-10,	1.55,	0,	0);

// Adding a collection of records in bulk
$dbc->add($records);

// Loop through all the records and dump their information using the map
foreach($dbc as $record) {
	$record->dump(true);
}
