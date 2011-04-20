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
 * Denotes a DBC exporter
 */
interface IDBCExporter {
	
	/**
	 * Denotes the standard PHP output stream (default export target)
	 */
	const OUTPUT    = 'php://output';
	
	/**
	 * Exception message when DBC has no mapping attached
	 */
	const NO_MAP    = 'Given DBC has no map attached';
	
	/**
	 * Exports the given DBC using this exporter to the given target(-stream)
	 */
	public function export(DBC $dbc, $target=self::OUTPUT);

}
