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
 * Copy Document
 *
 * @package Commands
 * @author Adam Venturella
 */
class CDBCopyDocument implements CouchDBCommand 
{
	private $database;
	private $from_id;
	private $to_id;
	private $rev;
	
	/**
	 * undocumented function
	 *
	 * @param string $database
	 * @param string $from_id
	 * @param string $to_id
	 * @param string $rev
	 * @author Adam Venturella
	 */
	public function __construct($database, $from_id, $to_id, $rev=null)
	{
		$this->database = $database;
		$this->from_id  = $from_id;
		$this->to_id    = $to_id;
		$this->rev      = $rev;
	}
	
	public function request()
	{
		$destination = $this->rev ? $this->to_id.'?rev='.$this->rev : $this->to_id;
		
		return <<<REQUEST
COPY /$this->database/$this->from_id HTTP/1.0
Host: {host}
Connection: Close
Destination: $destination
{authorization}


REQUEST;
	}
	
	public function __toString()
	{
		return 'CDBCopyDocument';
	}
}
?>