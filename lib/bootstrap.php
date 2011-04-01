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

if(!defined('DBC_DIR')) {
	define('DBC_DIR', dirname(__FILE__));
}
if(!defined('DBC_DS')) {
	define('DBC_DS', DIRECTORY_SEPARATOR);
}
if(!defined('DBC_EXPORT')) {
	define('DBC_EXPORT', DBC_DIR.DBC_DS.'exporters');
}

require(DBC_DIR.DBC_DS.'DBC.class.php');
require(DBC_DIR.DBC_DS.'DBCException.class.php');
require(DBC_DIR.DBC_DS.'DBCExporter.interface.php');
require(DBC_DIR.DBC_DS.'DBCIterator.class.php');
require(DBC_DIR.DBC_DS.'DBCMap.class.php');
require(DBC_DIR.DBC_DS.'DBCRecord.class.php');

require(DBC_EXPORT.DBC_DS.'DBCDatabaseExporter.class.php');
require(DBC_EXPORT.DBC_DS.'DBCJSONExporter.class.php');
require(DBC_EXPORT.DBC_DS.'DBCXMLExporter.class.php');
