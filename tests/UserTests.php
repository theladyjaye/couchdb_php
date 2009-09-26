<?php
/* 
	// users -- needs : password_sha
	POST /_session -d 'username=foo&password=bar' to get the AuthSession cookie
	
	eric@thelog:~$ curl -X GET http://oYeZDICkHV:pwadmin@localhost:5988/users/luser
	{"_id":"luser","_rev":"2-3d5791248864646ce3243e233fb6906f","username":"luser","roles":["gnome"],"password":"shh"}
	eric@thelog:~$ curl -X GET http://oYeZDICkHV:pwadmin@localhost:5988/users/_local%2F_acl
	{"_id":"_local/_acl","_rev":"0-2","rules":[{"db":"*","role":"_admin","allow":"write"},{"db":"test","role":"gnome","allow":"write"}]}

	{"error":"unauthorized","reason":"Name or password is incorrect."}
*/
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
require_once 'PHPUnit/Framework.php';
require_once 'couchdb/CouchDB.php';
require_once 'CouchDBTestConstants.php';

class UserTests extends PHPUnit_Framework_TestCase
{
	protected $couchdb;
	protected $couchdbLogin;
	
	/**
	 * @covers CouchDB::__construct
	 */
	protected function setUp()
	{
		
		$options             = array('authorization'=>'basic', 'username'=>'admin', 'password'=>'admin');
		$this->couchdb       = new CouchDB($options);
		$this->couchdbLogin  = new CouchDB();
	}
	
	/* USERS */
	public function testAdminCreate()
	{
		$response = $this->couchdb->admin_create(CouchDBTestConstants::kAdminSecondaryUsername, CouchDBTestConstants::kAdminSecondaryPassword);
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
	}
	
	public function testAdminDelete()
	{
		$response = $this->couchdb->admin_delete(CouchDBTestConstants::kAdminSecondaryUsername);
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
	}
	
	public function testUserCreate()
	{
		$response = $this->couchdb->user_create(CouchDBTestConstants::kUserUsername, CouchDBTestConstants::kUserPassword, CouchDBTestConstants::kUserEmail, array(CouchDBTestConstants::kUserRole1, CouchDBTestConstants::kUserRole2));
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
	}
	
	public function testUser()
	{
		$user = $this->couchdb->user(CouchDBTestConstants::kUserUsername);
		$this->assertEquals(CouchDBTestConstants::kUserUsername, $user['username']);
		$this->assertEquals(CouchDBTestConstants::kUserEmail, $user['email']);
		
		$count = count($user['roles']);
		print_r($user);
		$this->assertEquals(2, $count);
		$this->assertEquals(CouchDBTestConstants::kUserRole1, $user['roles'][0]);
		$this->assertEquals(CouchDBTestConstants::kUserRole2, $user['roles'][1]);
	}
	
	public function testUserUpdateEmailAndRoles()
	{
		$response = $this->couchdb->user_update(CouchDBTestConstants::kUserUsername, null, null, CouchDBTestConstants::kUserAltEmail, array(CouchDBTestConstants::kUserRole2));
		
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
		
		$user = $this->couchdb->user(CouchDBTestConstants::kUserUsername);
		
		print_r($user);
		$this->assertEquals(urlencode(CouchDBTestConstants::kUserAltEmail), $user['email']);
		
		$count = count($user['roles']);
		$this->assertEquals(1, $count);
		$this->assertEquals(CouchDBTestConstants::kUserRole2, $user['roles'][0]);
	}
	
	public function testUserUpdatePassword()
	{
		$response = $this->couchdb->user_update(CouchDBTestConstants::kUserUsername, CouchDBTestConstants::kUserAltPassword, CouchDBTestConstants::kUserPassword, null, null);
		
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
		
		$user = $this->couchdb->user(CouchDBTestConstants::kUserUsername);
		//$this->assertEquals(urlencode(CouchDBTestConstants::kUserAltEmail), $user['email']);
		
		print_r($user);
		/*
		$count = count($user['roles']);
		$this->assertEquals(1, $count);
		$this->assertEquals(CouchDBTestConstants::kUserRole2, $user['roles'][0]);
		*/
	}
	
	public function testSessionInvalidLogin()
	{
		$response = $this->couchdbLogin->session_login(CouchDBTestConstants::kUserUsername.'xx', CouchDBTestConstants::kUserPassword.'xx');
		$this->assertEquals(null, $response);
	}
	
	public function testSessionLogin()
	{
		$response = $this->couchdbLogin->session_login(CouchDBTestConstants::kUserUsername, CouchDBTestConstants::kUserAltPassword);
		$this->assertNotNull($response);
	}
	
	public function testSessionLogout()
	{
		$response = $this->couchdbLogin->session_logout();
	}
	
	public function testUserDelete()
	{
		$response = $this->couchdb->user_delete(CouchDBTestConstants::kUserUsername);
		//print_r($response);
	}
}
?>