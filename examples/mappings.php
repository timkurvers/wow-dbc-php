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
