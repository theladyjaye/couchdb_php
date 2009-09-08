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
	
	$newdb              = 'newdb';
	$options            = array('database'=>$newdb);
	$db                 = new CouchDB($options);
	
	
	$attachmentName     = 'avatar';
	$attachmentPath     = '/path/to/file.png';
	
	
	$attachment['name'] = $attachmentName;
	$attachment['path'] = $attachmentPath;
	
	// The system will currently figure out the content type for gif, jpeg, and png
	// if the attachment is not one of those 3 then it will assume binary/octet-stream
	// unless the 'content-type' key is provided.
	
	$result = $db->put_attachment($attachment);
?>