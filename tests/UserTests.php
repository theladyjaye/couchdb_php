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
require_once 'PHPUnit/Framework.php';
require_once 'couchdb/CouchDB.php';
require_once 'CouchDBTestConstants.php';

class UserTests extends PHPUnit_Framework_TestCase
{
	private $couchdb;
	private $couchdbLogin;
	private static $startAclCount;
	private static $resetAcl;
	/**
	 * @covers CouchDB::__construct
	 */
	protected function setUp()
	{
		
		$options             = array('authorization'=>'basic', 'username'=>'admin', 'password'=>'admin');
		$this->couchdb       = new CouchDB($options);
		$this->couchdbLogin  = new CouchDB();
		
		if(UserTests::$startAclCount === null)
		{
			$acl = $this->couchdb->acl();
			
			if($acl->error)
			{
				UserTests::$startAclCount = 0;
				UserTests::$resetAcl = true;
			}
			else
			{
				UserTests::$startAclCount = count($acl->result['rules']);
			}
		}
	}
	
	/* USERS */
	public function testAcl()
	{
		$acl = $this->couchdb->acl();
		if(!$acl->error)
		{
			$this->assertEquals('_local/_acl', $acl->result['_id']);
		}
	} 
	
	public function testAclCreate()
	{
		$rules  = array(array('db'=>'couchdb_php_testdb1', 'role'=>'user', 'allow'=>'write'),
		                array('db'=>'couchdb_php_testdb1', 'role'=>'user', 'allow'=>'write'),
		                array('db'=>'couchdb_php_testdb1', 'role'=>'user', 'allow'=>'write'),
		                array('db'=>'couchdb_php_testdb2', 'role'=>'user', 'allow'=>'write'));
		
		$this->couchdb->acl_create_rules($rules);
		
		$acl = $this->couchdb->acl();
		$this->assertEquals(count($rules), (count($acl->result['rules']) - UserTests::$startAclCount));
	}
	
	public function testAclDelete()
	{
		// should remove 3 records
		$remove  = 3;
		$created = 4;
		$total   = $created + UserTests::$startAclCount;
		
		$acl = $this->couchdb->acl();
		
		$this->couchdb->acl_delete_rules(array(array('db'=>'couchdb_php_testdb1', 'role'=>'user', 'allow'=>'write')));
		
		$acl = $this->couchdb->acl();
		$this->assertEquals(($total-$remove), count($acl->result['rules']));
		
		$this->couchdb->acl_delete_rules(array(array('db'=>'couchdb_php_testdb2', 'role'=>'user', 'allow'=>'write')));
		
		$acl = $this->couchdb->acl();
		$this->assertEquals(UserTests::$startAclCount, count($acl->result['rules']));
		
		if(UserTests::$resetAcl)
		{
			$options    = array('authorization'=>'basic', 'username'=>'admin', 'password'=>'admin');
			$connection = new CouchDBConnection($options);
			$response   = $connection->execute(new DeleteDocument('users', '_local/_acl', $acl->result['_rev']));
			
			$acl = $this->couchdb->acl();
			$this->assertNotNull($acl->error);
		}
	}
	
	/*
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
		
		//print_r($user);
		
		// $count = count($user['roles']);
		// $this->assertEquals(1, $count);
		// $this->assertEquals(CouchDBTestConstants::kUserRole2, $user['roles'][0]);
		
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
	*/
}
?>


























































































































































































































































































































































































































































































