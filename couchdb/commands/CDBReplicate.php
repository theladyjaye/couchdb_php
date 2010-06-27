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

/**
 * Includes
 */
require_once 'CouchDBCommand.php';

/**
 * Database Replicate Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class CDBReplicate implements CouchDBCommand 
{
	private $source;
	private $target;
	
	/**
	 * undocumented function
	 *
	 * @param string $database 
	 * @author Adam Venturella
	 */
	public function __construct($source, $target)
	{
		$this->source = $source;
		$this->target = $target;
	}
	
	public function request()
	{
		$object         = new stdClass();
		$object->source = $this->source;
		$object->target = $this->target;
		$json           = couchdb_json_encode($object);
		$content_length = strlen($json);
		
		return <<<REQUEST
POST /_replicate HTTP/1.0
Host: {host}
Connection: Close
Content-Length: $content_length
Content-Type: application/json
{authorization}

$json
REQUEST;
	}
	
	public function __toString()
	{
		return 'Replicate';
	}
}
?>