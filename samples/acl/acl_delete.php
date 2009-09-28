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
	
	$options = array('authorization'=>'basic', 'username'=>'admin', 'password'=>'admin');
	$db      = new CouchDB($options);
	
	// can be an array of arrays or an array of objects:
	
	$rules   = array(array('db'=>'couchdb_php_testdb1', 'role'=>'user', 'allow'=>'write'),
	                 array('db'=>'couchdb_php_testdb2', 'role'=>'foo', 'allow'=>'write'));
	// OR
	/*
	$rule1 = new stdClass();
	$rule1->db    = "*";
	$rule1->role  = "_admin";
	$rule1->allow = "write";
	
	$rule2 = new stdClass();
	$rule2->db    = "*";
	$rule2->role  = "couchdb_php_testdb1";
	$rule2->allow = "write";
	
	$rule3 = new stdClass();
	$rule3->db    = "*";
	$rule3->role  = "couchdb_php_testdb2";
	$rule3->allow = "write";
	
	$rules = array($rule1, $rule2, $rule3);
	*/
	
	$this->couchdb->acl_delete_rules($rules);
	
	$acl = $this->couchdb->acl();
	print_r($acl->result);
?>