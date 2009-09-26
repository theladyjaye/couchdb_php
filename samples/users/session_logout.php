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
	
	// need to be a server administrator to delete users
	$db      = new CouchDB();
	$result  = $db->session_logout();
	
	if($result == null)
	{
		echo 'Did not log out'
	}
	else
	{
		// result will be the cookie that should be set.  
		// It will be the empty version of the cookie that was provied for login
		echo $result;
	}
	
	// NOTE! if you are doing this work on the server side, you can log out user in like this as well:
	
	$result  = $db->session_logout(true);
	
	// the only difference is the addition of the extra 'true' which instructs the library to attempt 
	// to set the cookie on the browser via : header('Set-Cookie:' . $cookie);
	
?>