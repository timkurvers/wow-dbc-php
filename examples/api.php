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
