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
 *    @package Sample 
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2009 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/

/**
 * Sample
 */
	require 'couchdb/CouchDB.php';
	
	$newdb   = 'newdb';
	$options = array('database'=>$newdb);
	$db      = new CouchDB($options);
	
	
	/*
	$map = <<<FUNCTION
	function(doc)
	{
		if(doc.type == 'category')
		{
			emit(doc._id, 1);
		}
	}
FUNCTION;

	$reduce = <<<FUNCTION
	function(keys, values, rereduce)
	{
		return sum(values);
	}
FUNCTION;
	*/
	
	// EITHER WILL WORK
	
	$map    = "function(doc) { if(doc.type == 'category') { emit(doc._id, 1);}}";
	$reduce = "function(keys, values, rereduce) { return sum(values); }";
	
	$result = $db->temp_view($map, $reduce);
	
	print_r($result);
	
?>