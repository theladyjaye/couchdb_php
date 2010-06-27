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
 * Authenticate for a Session
 *
 * @package Commands
 * @author Adam Venturella
 */
class SessionLogin implements CouchDBCommand
{
	public $username;
	public $password;
	
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	public function request()
	{
		$data           = "username=".urlencode($this->username).'&password='.urlencode($this->password);
		$content_length = strlen($data);
		
		return <<<REQUEST
POST /_session HTTP/1.0
Host: {host}
Connection: Close
Content-Type: application/x-www-form-urlencoded
X-CouchDB-WWW-Authenticate: Cookie
Content-Length: $content_length

$data
REQUEST;
	}
	
	public function __toString()
	{
		return 'SessionLogin';
	}
}
?>