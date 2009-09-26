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
 * Get Server Version Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class UserDelete implements CouchDBCommand 
{
	public $id;
	public $revision;
	
	public function __construct($id, $revision)
	{
		$this->id       = $id;
		$this->revision = $revision;
	}
	
	public function request()
	{
		
		return <<<REQUEST
DELETE /users/$this->id HTTP/1.0
If-Match: "$this->revision"
{authorization}


REQUEST;
	}
	
	public function __toString()
	{
		return 'UserDelete';
	}
}
?>