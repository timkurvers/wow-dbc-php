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
 * This example shows how to edit an existing a DBC-file
 */

// Open given DBC and given map (editing requires a writable DBC)
$dbc = new DBC('./dbcs/Sample.dbc', DBCMap::fromINI('./maps/Sample.ini'));

// Grab the first record
$rec = $dbc->getRecord(0);

// Dump the record in its initial state
$rec->dump(true);

// Read the points value (field 18 or 'points')
$points = $rec->getInt(18);
$points = $rec->getInt('points');

// Write the points value +1 (again through field 18 or 'points') and verify the record-dump
$rec->setInt(18, $points + 1);
$rec->setInt('points', $points + 1);
$rec->dump(true);

// Write a random string to the name field and verify the record-dump
$rec->setString('name', uniqid());
$rec->dump(true);

// Also, note how the string-block of the DBC file increases per string-write!
var_dump($dbc);
