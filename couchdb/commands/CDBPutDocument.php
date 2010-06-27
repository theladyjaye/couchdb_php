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
 * Put Document Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class CDBPutDocument implements CouchDBCommand 
{
	private $database;
	private $id;
	private $json;
	private $batch;
	
	/**
	 * undocumented function
	 *
	 * @param string $database 
	 * @param string $json 
	 * @param string $id 
	 * @author Adam Venturella
	 */
	public function __construct($database, $json, $id=null, $batch)
	{
		$this->database = $database;
		$this->json     = $json;
		$this->id       = $id;
		$this->batch    = $batch;
	}
	
	public function request()
	{
		$content_length = strlen($this->json);
		$batch          = null;
		if(!$this->id){
			$this->id  = couchdb_generate_id($this->json);
		}
		
		if($this->batch){
			$batch          = "?batch=ok";
		}
		
		return <<<REQUEST
PUT /$this->database/$this->id/$batch HTTP/1.0
Host: {host}
Connection: Close
Content-Length: $content_length
Content-Type: application/json
{authorization}

$this->json
REQUEST;
	}
	
	public function __toString()
	{
		return 'PutDocument';
	}
}
?>