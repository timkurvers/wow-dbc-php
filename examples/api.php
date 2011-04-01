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
 * This example shows basic usage of the World of Warcraft DBC Library API
 */

// Open the given DBC (ensure read-access)
$dbc = new DBC('./dbcs/Sample.dbc');

// Fetch the first record (zero-based); Will return null if no record was found
$record = $dbc->getRecord(0);

// Fetch a record by its first field (generally containing the id); Will return null if no record was found
$record = $dbc->getRecordByID(3);

// Check for the 11th record's existence
$exists = $dbc->hasRecord(1);

// Loop over all records in this DBC and query each one
foreach($dbc as $r) {
	var_dump('Record #'.$r->getID().' with name: '.$r->getString(1));
	$r->dump(true);
}
