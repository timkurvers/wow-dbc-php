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
 * This example shows how to manually construct a DBC mapping through the API
 */

// Construct a new (empty) DBC mapping
$map = new DBCMap();

// Add 'id' field (defaults to unsigned integer)
$map->add('id');

// Add 'name' as a string field (Use DBC::STRING_LOC for a localized string)
$map->add('name', DBC::STRING_LOC);

// Add 'points' as a signed integer field
$map->add('points', DBC::INT);

// Add 'height' as a float field
$map->add('height', DBC::FLOAT);

// Add 'friend1' and 'friend2' as signed integer fields
$map->add('friend', DBC::INT, 2);

// Add a random field
$map->add('remove-me');

// And remove it again
$map->remove('remove-me');

var_dump($map);

// Write the mappings to given INI-file
$map->toINI('./maps/Sample.ini');
