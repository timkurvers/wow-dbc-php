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
