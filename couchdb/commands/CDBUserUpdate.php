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
class CDBUserUpdate implements CouchDBCommand 
{
	public $username;
	public $password;
	public $old_password;
	public $email;
	public $roles;
	
	
	public function __construct($username, $password=null, $old_password=null, $email=null, $roles=null)
	{
		$this->username     = $username;
		$this->password     = $password;
		$this->old_password = $old_password;
		$this->email        = $email;
		$this->roles        = $roles;
	}
	
	public function request()
	{
		$username         = urlencode($this->username);
		$form             = array();
		
		if($this->email){
			$form['email'] = urlencode($this->email);
		}
		
		if($this->password){
			$form['password'] = $this->password;
		}
		
		if($this->old_password){
			$form['old_password'] = $this->old_password;
		}
		
		//$form = array_map("urlencode", $form);
		
		$data = http_build_query($form);
		
		if($this->roles)
		{
			$rolesCount = count($this->roles);
		
			for ($i=0; $i < $rolesCount; $i++) {
				$data .= "&roles=".urlencode($this->roles[$i]);
			}
		}
		
		//echo "\n\n".$data."\n\n";
		
		
		$content_length = strlen($data);
		
		/*echo <<<REQUEST
PUT /_user/$username HTTP/1.0
Content-Length: $content_length
Connection: Close
Content-Type: application/x-www-form-urlencoded
{authorization}

$data



REQUEST;
*/
		
		return <<<REQUEST
PUT /_user/$username HTTP/1.0
Host: {host}
Connection: Close
Content-Length: $content_length
Content-Type: application/x-www-form-urlencoded
{authorization}

$data
REQUEST;
	}
	
	public function __toString()
	{
		return 'UserUpdate';
	}
}
?>