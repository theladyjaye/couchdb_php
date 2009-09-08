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
	
	$newdb        = 'newdb';
	$options      = array('database'=>$newdb);
	$db           = new CouchDB($options);
	$document_id  = 'target_documnet_id';
	
	// Get an existing document
	$document               = $db->document($document_id);
	echo '<h2>BEFORE</h2>';
	echo '<pre>',print_r($document, true),'</pre>';
	
	// Add a new value
	$document['screenname'] = 'lipsum';
	
	// As JSON
	$json                   = couchdb_json_encode($document);
	$db->put($json, $document_id);
	
	// OR As Array:
	//$db->put($document, $document_id);
	
	// Get the document again to show it changed
	echo '<h2>AFTER</h2>';
	echo '<pre>',print_r($db->document($document_id), true),'</pre>';
	
?>