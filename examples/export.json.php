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
 * This example shows how to export a DBC-file to JSON format
 */

// Open given DBC and given map (ensure read-access on both)
$dbc = new DBC('./dbcs/Sample.dbc', DBCMap::fromINI('./maps/Sample.ini'));

// When exporting to the standard PHP output, ensure the browser expects a JSON-document
header('Content-Type: application/json');

// Set up a new JSON exporter
$json = new DBCJSONExporter();

// And instruct it to export the given DBC (ensure the DBC has an attached map)
$json->export($dbc);

// Alternatively supports exporting to a file by providing a second argument 
$json->export($dbc, './export/sample.json');
