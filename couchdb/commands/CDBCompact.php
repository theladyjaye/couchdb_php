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
 * Database Compact Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class CDBCompact implements CouchDBCommand 
{
	private $database;
	
	/**
	 * undocumented function
	 *
	 * @param string $database 
	 * @author Adam Venturella
	 */
	public function __construct($database)
	{
		$this->database = $database;
	}
	
	public function request()
	{
		
		return <<<REQUEST
POST /$this->database/_compact HTTP/1.0
Host: {host}
Connection: Close


REQUEST;
	}
	
	public function __toString()
	{
		return 'Compact';
	}
}
?>