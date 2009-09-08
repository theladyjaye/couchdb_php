<?php
/**
 *    CouchDB_PHP
 * 
 *    Copyright (C) 2009 Adam Venturella
 *
 *    LICENSE:
 *
 *    Licensed under the Apache License, Version 2.0 (the "License"); you may not
 *    use this file except in compliance with the License.  You may obtain a copy
 *    of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *    This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 *    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
 *    PURPOSE. See the License for the specific language governing permissions and
 *    limitations under the License.
 *
 *    Author: Adam Venturella - aventurella@gmail.com
 *
 *    @package CouchDB_PHP
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2009 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/

/* 
In the event that something better comes along for JSON encoding/decoding 
down the line, not that json_decode(), and json_encode() are bad -- they are great, 
or if someone wants to do their own encoding/decoding thing.

JSON encoding/decoding is pretty much the cruxt of the system, so probably best to
abstract it away.
*/

/**
 * Function for decoding JSON
 *
 * @param string $value JSON to decode into an associative array
 * @return array
 * @author Adam Venturella
 */
function couchdb_json_decode($value)
{
	return json_decode($value, true);
}

/**
 * Function for encoding JSON
 *
 * @param string $value object to encode into JSON
 * @return string
 * @author Adam Venturella
 */
function couchdb_json_encode($value)
{
	return json_encode($value);
}

/**
 * Function to generate unique document ids
 *
 * @param string $document optional JSON representation of the document
 * @return string
 * @author Adam Venturella
 */
function couchdb_generate_id($document=null)
{
	return hash('md5', $document.uniqid(mt_rand(), true));
}

?>