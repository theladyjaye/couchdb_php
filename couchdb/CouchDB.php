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
 **/
require 'CouchDBFunctions.php';
require 'CouchDBView.php';
require 'commands/Info.php';
require 'commands/TempView.php';
require 'commands/View.php';
require 'commands/Version.php';
require 'commands/PutDocument.php';
require 'commands/PutAttachment.php';
require 'commands/DeleteAttachment.php';
require 'commands/GetAttachment.php';
require 'commands/GetDocument.php';
require 'commands/DeleteDocument.php';
require 'commands/CreateDatabase.php';
require 'commands/DeleteDatabase.php';
require 'commands/Replicate.php';
require 'commands/AdminCreate.php';
require 'commands/AdminDelete.php';
require 'commands/SessionLogin.php';
require 'commands/SessionLogout.php';
require 'commands/UserCreate.php';
require 'commands/UserDelete.php';
require 'commands/UserUpdate.php';
require 'commands/Compact.php';
require 'net/CouchDBConnection.php';
require 'net/CouchDBResponse.php';

/**
 * Principal CouchDB Class.
 * A Wrapper for all CouchDB Commands
 * 
 * @package Core 
 */
class CouchDB
{
	const kDefaultLanguage = 'javascript';
	const kViewPrefix      = '_design';
	private $connectionOptions;
	
	/**
	 * CouchDB Constructor
	 * 
	 * Available option keys:
	 * 'database'
	 * 'host'
	 * 'port'
	 * 'timeout'
	 * 'transport'
	 * 'authorization'
	 * 'authorization_session'
	 * 'username'
	 * 'password'
	 *
	 * All have default values with the exception of 'database', 'authorization', 'authorization_session', 'username', 'password'
	 * 
	 * @see CouchDBConnection::__construct()
	 * 
	 * @param array $options 
	 * @author Adam Venturella
	 * @example ../samples/setup/connect.php Sample instantiation.
	 * @example ../samples/setup/connect_basic_auth.php Sample instantiation with Basic Auth.
	 */
	public function __construct($options=null)
	{
		if(isset($options['database'])) $options['database'] = urlencode($options['database']);
		$this->connectionOptions = $options;
	}
	
	/**
	 * Get a document with a given id from the database.
	 *
	 * @param string $id the id of the document you wish to retrieve
	 * @param bool $json default is false, weather or not to return the result as JSON
	 * @return void
	 * @author Adam Venturella
	 * @example ../samples/commands/get_document.php Get an existing document from the database
	 */
	public function document($id, $json=false)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new GetDocument($this->connectionOptions['database'], $id));
			
			if($json)
			{
				return $response->data;
			}
			else
			{
				return $response->result;
			}
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Delete a document in the database
	 *
	 * Delete a document with a given id and revision.
	 * if no revision is given, a request will be made to get
	 * the latest revision for the provided document id
	 *
	 * @param string $id
	 * @param string $revision
	 * @return CouchDBResponse
	 * @example ../samples/commands/delete_document.php Delete an existing document from the database
	 */
	public function delete($id, $revision=null)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			if(!$revision)
			{
				$revision = $this->_revisionForDocument($id);
			}
			
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new DeleteDocument($this->connectionOptions['database'], $id, $revision));
			return $response->result;
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Put a new document into the database
	 *
	 * @param string $json The valid JSON describing the document you wish you add to the database 
	 * @param string $id optional id for the document. If no id is specififed a new id will be generated and a new document created
	 * @param bool $batch put is part of a batch operation.  See: {@link http://wiki.apache.org/couchdb/HTTP_Document_API CouchDB Document API for PUT}. Can be used to achieve higher throughput at the cost of lower guarantees. 
	 *                     When a PUT (or a document POST as described below) is sent using this option, it is not immediately written to disk. 
	 *                     Instead it is stored in memory on a per-user basis for a second or so (or the number of docs in memory reaches a certain point). 
	 *                     After the threshold has passed, the docs are committed to disk. Instead of waiting for the doc to be written to disk before 
	 *                     responding, CouchDB sends an HTTP 202 Accepted response immediately.
	 *
     *                     batch is not suitable for crucial data, but it ideal for applications like logging which can accept the risk that a small 
     *                     proportion of updates could be lost due to a crash. Docs in the batch can also be flushed manually using the _ensure_full_commit API.
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/commands/put_document1.php Create a new document in the database
	 * @example ../samples/commands/put_document2.php Update an existing document
	 */
	public function put($document, $id=null, $batch=false)
	{
		$json = null;
		
		if(is_array($document) || is_a($document, 'stdClass'))
		{
			$json = couchdb_json_encode($document);
		}
		else if(is_object($document))
		{
			$json = $document->__toString();
		}
		else if(is_string($document))
		{
			$json = $document;
		}
		
		if($this->shouldPerformActionWithDatabase())
		{
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new PutDocument($this->connectionOptions['database'], $json, $id, $batch));
			return $response->result;
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Get an attachment of a given document with the given name
	 * it will return the body of the attachment, without any HTTP headers
	 * if that body represents an image, it will return the raw image data
	 *
	 * @param string $document the id of the document
	 * @param string $name the name of the attachment
	 * @return mixed
	 * @author Adam Venturella
	 * @example ../samples/commands/get_attachment.php Get an attachment from an existing document
	 */
	public function attachment($document, $name)
	{

		if($this->shouldPerformActionWithDatabase())
		{
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new GetAttachment($this->connectionOptions['database'], $document, $name));
			return $response->result;
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Delete an attachment
	 *
	 * @param string $document the id of the document 
	 * @param string $name the name of the attachment
	 * @param string $revision optional the revision of the document.
	 *               if no revision is provided, a lookup will be performed
	 *               with the given document id to reteive the latest revision id
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/commands/delete_attachment.php Delete an attachment from an existing document
	 */
	public function delete_attachment($document, $name, $revision=null)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			if(!$revision)
			{
				$revision = $this->_revisionForDocument($document);
			}

			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new DeleteAttachment($this->connectionOptions['database'], $document, $name, $revision));
			return $response->result;
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Add an attachment to a given document 
	 * or create a new document with an attachment
	 *
	 * @param array $attachment an array with the following keys:
	 *              'name' required the name of the attachment to add to the database
	 *              'path' required the path to the file whose contents will be added
	 *              'content-type' optional the content type, eg: image/jpeg, image/png, text/plain
	 *               of the attachment. If this is not provided, a best guess will be made.
	 *               the system will currently figure out gif, jpeg, and png. if it is not
	 *               one of those then binary/octet-stream will be used unless the 'content-type'
	 *               key is specified.
	 * @param string $document optional the id of the document.
	 *               if no $document is provided, a new document will be
	 *               will be created.
	 * @param string $revision optional the revision of the document to add
	 *               the attachment too. If no document id is provided, this value 
	 *               should be null
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/commands/put_attachment1.php Create a new document starting with an attachment
	 * @example ../samples/commands/put_attachment2.php Add an attachment to an existing document
	 */
	public function put_attachment($attachment, $document=null, $revision=null)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			if(!$revision)
			{
				$revision = $this->_revisionForDocument($document);
			}

			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new PutAttachment($this->connectionOptions['database'], $attachment, $document, $revision));
			return $response->result;
		}
		else
		{
			$this->throw_no_database_exception();
		}
		
	}
	
	/**
	 * Get a Temp View
	 * Temporary views are only good during development. Final code 
	 * should not rely on them as they are very expensive to compute 
	 * each time they get called and they get increasingly slower the 
	 * more data you have in a database.
	 *
	 * See Temporary Views
	 * @link http://wiki.apache.org/couchdb/HTTP_view_API
	 *
	 * @param string $map the map function to execute to generate the view
	 * @param string $reduce optional reduce function for the map
	 * @param array $options optional array of querying options.
	 *              key=aValue
	 *              startkey=aValue
	 *              startkey_docid=aDocid
	 *              endkey=aValue
	 *              endkey_docid=aDocid
	 *              limit=max rows to return
	 *              stale=ok
	 *              descending=true
	 *              skip=number of rows to skip
	 *              group=true
	 *              group_level=int
	 *              reduce=false
	 *              include_docs=true
	 *              Values must valid JSON for key, startkey, endkey
	 * @param bool $json default is false, weather or not to return the result as JSON
	 * @return string|CouchDBView
	 * @author Adam Venturella
	 * @example ../samples/commands/temp_view.php Execute a temp view.
	 * @example ../samples/commands/temp_view_reduce.php Execute a temp view with a reduce function.
	 */
	public function temp_view($map, $reduce=null, $options=null, $json=false)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new TempView($this->connectionOptions['database'], $map, $reduce, $options));
			
			if($json)
			{
				return $response->data;
			}
			else
			{
				return CouchDBView::viewWithJSON($response->data);
			}
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Create a View within a design document.  If no design document exists, 
	 * one will be created.
	 *
	 * @param string $design The design document and view you wish to created, eg: 'document/view'
	 *                       If the design document does not exist, it will be created.
	 * @param string $map The map function for the view
	 * @param string $reduce optional reduce function for the view
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/commands/create_view.php Create a view.
	 * @example ../samples/commands/create_view_reduce.php Create a view with a reduce function.
	 */
	public function create_view($designView, $map, $reduce=null)
	{
		list($design, $view) = explode('/', $designView);
		
		$document = null;
		$id       = CouchDB::kViewPrefix."/".$design;
		$map      = strtr($map, array("\n"=>'', "\t"=>''));
		
		if($reduce)
		{
			$reduce      = strtr($reduce, array("\n"=>'', "\t"=>''));
		}
		
		try
		{
			$document = $this->document($id);
			$document['views'][$view]['map'] = $map;
			
			if($reduce)
			{
				$document['views'][$view]['reduce'] = $reduce;
			}
		}
		catch(Exception $e)
		{
			$document                    = new stdClass();
			$document->language          = CouchDB::kDefaultLanguage;
			$document->views             = new stdClass();
			$document->views->$view      = new stdClass();
			$document->views->$view->map = $map;
			
			if($reduce)
			{
				$document->views->$view->reduce = $reduce;
			}
			
		}
		
		return $this->put($document, $id);
	}
	
	/*
		TODO Flush these 2 guys out. Note thatlisting views differs significantly from 0.9 to 0.10
	*/
	
	/*
	public function create_show()
	{
	
	}
	
	public function create_list()
	{
	
	}
	
	public function list()
	{
		
	}
	
	public function show()
	{
		
	}
	*/
	
	/**
	 * Get a View
	 * See Querying Options
	 * @link http://wiki.apache.org/couchdb/HTTP_view_API
	 *
	 * @param string $target the view you wish to retrieve, eg: 'users/all', 'group/view'
	 * @param array $options optional array of querying options.
	 *              key=aValue
	 *              startkey=aValue
	 *              startkey_docid=aDocid
	 *              endkey=aValue
	 *              endkey_docid=aDocid
	 *              limit=max rows to return
	 *              stale=ok
	 *              descending=true
	 *              skip=number of rows to skip
	 *              group=true
	 *              group_level=int
	 *              reduce=false
	 *              include_docs=true
	 *              Values must valid JSON for key, startkey, endkey
	 * @param bool $json default is false, weather or not to return the result as JSON
	 * @return string|CouchDBView
	 * @author Adam Venturella
	 * @example ../samples/commands/view.php Execute a view, and a view with query options, print the results.
	 */
	public function view($target, $options=null, $json=false)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new View($this->connectionOptions['database'], $target, $options));
			
			if($json)
			{
				return $response->data;
			}
			else
			{
				return CouchDBView::viewWithJSON($response->data);
			}
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	/**
	 * Create a new database
	 *
	 * @param string $value optional the name of the database to create.
	 *               if no value is given, the database specified in the
	 *               connection options for CouchDB::__construct() will
	 *               attempt to be created 
	 * @return void
	 * @author Adam Venturella
	 * @example ../samples/databases/create_database.php Create a database
	 */
	public function create_database($value=null)
	{
		if(!$value)
		{
			if($this->shouldPerformActionWithDatabase())
			{
				$value = $this->connectionOptions['database'];
			}
			else
			{
				throw new Exception('CouchDB create_database failed: no value provided');
			}
		}
		
		$connection = new CouchDBConnection($this->connectionOptions);
		$connection->execute(new CreateDatabase($value));
	}
	
	/**
	 * Delete a database
	 *
	 * @param string $value optional the name of the database to delete.
	 *               if no value is given, the database specified in the
	 *               connection options for CouchDB::__construct() will
	 *               attempt to be deleted 
	 * @return void
	 * @author Adam Venturella
	 * @example ../samples/databases/delete_database.php Delete a database
	 */
	public function delete_database($value=null)
	{
		if(!$value)
		{
			if($this->shouldPerformActionWithDatabase())
			{
				$value = $this->connectionOptions['database'];
			}
			else
			{
				throw new Exception('CouchDB delete_database failed: no value provided');
			}
		}
		
		$connection = new CouchDBConnection($this->connectionOptions);
		$connection->execute(new DeleteDatabase($value));
	}
	
	/**
	 * Get information for a given database
	 *
	 * @param string $value optional the name of the database to retrive the info for.
	 *               if no value is given, the database specified in the
	 *               connection options for CouchDB::__construct() will
	 *               be used
	 * @param bool $json default is false, weather or not to return the result as JSON
	 * @return string|array
	 * @author Adam Venturella
	 * @example ../samples/databases/info1.php Get info as array using connection options database
	 * @example ../samples/databases/info2.php Get info as array specifying desired database
	 * @example ../samples/databases/info3.php Get info as JSON specifying desired database
	 */
	public function info($value=null, $json=false)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			if(!$value)
			{
				$value = $this->connectionOptions['database'];
			}
		}
		
		if($value == null){
			$this->throw_no_database_exception();
		}
			
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new Info($value));
			
		if($json)
		{
			return $response->data;
		}
		else
		{
			return $response->result;
		}
	}
	
	/**
	 * Compact a database by removing outdated document revisions and deleted documents.
	 *
	 * @param string $database optional database name to compact.  If no database name is specified will attempt to use
	 *               the database specified in the connection options.
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 */
	
	public function compact($database=null)
	{
		if(!$database)
		{
			$database = $this->connectionOptions['database'];
		}
		
		if($database == null){
			$this->throw_no_database_exception();
		}
		
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new Compact($database));
		return $response;
	}
	
	/**
	 * Replicates all active documents on the source database to the destination database.  
	 * Additionally, all documents that were deleted in the source databases are also 
	 * deleted (if exists) on the destination database.
	 * 
	 * The replication process only copies the last revision of a document, so all 
	 * previous revisions that were only on the source database are not copied to 
	 * the destination database.
	 *
	 * See replication API Documentation for more info.
	 * @link http://wiki.apache.org/couchdb/Replication
	 *
	 * @param string $source the source database, remote or local
	 * @param string $target the target database, remote or local
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 */
	public function replicate($source, $target)
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new Replicate($source, $target));
	    return $response;
	}
	
	/**
	 * Create an administrator
	 *
	 * @param string $username  
	 * @param string $password 
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/setup/admin_create.php Create an administrator - Basic Auth
	 */
	public function admin_create($username, $password)
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new AdminCreate($username, $password));
		return $response;
	}
	
	/**
	 * Delete an administrator
	 *
	 * @param string $username
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/setup/admin_create.php Delete an administrator
	 */
	public function admin_delete($username)
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new AdminDelete($username));
		return $response;
	}
	
	/**
	 * Create a user
	 *
	 * @param string $username 
	 * @param string $password 
	 * @param string $email 
	 * @param array $roles 
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/users/user_create.php Create a user
	 */
	public function user_create($username, $password, $email, $roles)
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new UserCreate($username, $password, $email, $roles, $extras));
		return $response;
	}
	
	/**
	 * Delete a user
	 *
	 * @param string $username 
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/users/user_delete.php Delete a user
	 */
	public function user_delete($username)
	{
		$user = $this->user($username);
		
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new UserDelete($user['_id'], $user['_rev']));
		return $response;
	}
	
	/**
	 * Update a user's information, you cannot update the user's username
	 *
	 * @param string $username 
	 * @param string $password 
	 * @param string $old_password 
	 * @param string $email 
	 * @param string $roles 
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/users/user_update.php Update a user's account
	 */
	public function user_update($username, $password=null, $old_password=null, $email=null, $roles=null)
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new UserUpdate($username, $password, $old_password, $email, $roles));
		return $response;
	}
	
	/**
	 * Get a user
	 *
	 * @param string $username 
	 * @param string $json 
	 * @return array
	 * @author Adam Venturella
	 * @example ../samples/users/user_info.php Get info on a user
	 */
	public function user($username, $json=false)
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new View('users', '_auth/users', array('key'=>$username)));
		if($json)
		{
			return $response->data;
		}
		else
		{
			$view = CouchDBView::viewWithJSON($response->data);
			return $view[0]['value'];
		}
	}
	
	/**
	 * Get the _local/_acl
	 *
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/acl/acl_info.php get ACL Document
	 */
	public function acl()
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new GetDocument('users', '_local/_acl'));
		return $response;
	}
	/**
	 * Add ACL Rules to users/_local/_acl
	 *
	 * @param $collection An array of arrays representing rules
	 *                    or an array of objects representing rules
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/acl/acl_create.php Create ACL rules
	 */
	public function acl_create_rules($collection)
	{
		$rules    = array();
		$response = null;
		
		foreach($collection as $rule)
		{
			if($this->acl_rule_is_valid($rule))
			{
				//{"db":"*","role":"_admin","allow":"write"}
				//{"db":"*","role": "test", "allow":"read"}
				
				$object = new stdClass();
				
				if(is_array($rule))
				{
					$object->db    = $rule['db'];
					$object->role  = $rule['role'];
					$object->allow = $rule['allow'];
				}
				else if (is_object($rule))
				{
					$object->db    = $rule->db;
					$object->role  = $rule->role;
					$object->allow = $rule->allow;
				}
				
				$rules[] = $object;
			}
		}
		
		$acl      = $this->acl();
		$response = null;
		$id       = '_local/_acl';
		$batch    = false;
		
		if($acl->error)
		{
			$document        = new stdClass();
			$document->rules = $rules;
			$json            = couchdb_json_encode($document);
		}
		else
		{
			$document           = $acl->result;
			$document['rules']  = array_merge($document['rules'], $rules);
			$json               = couchdb_json_encode($document);
		}
		
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new PutDocument('users', $json, $id, $batch));
		
		return $response;
	}
	
	/**
	 * Delete ACL rules from users/_local/_acl
	 *
	 * @param array $collection An array of arrays representing rules 
	 *                          or an array of objects representing rules
	 * @return CouchDBResponse
	 * @author Adam Venturella
	 * @example ../samples/acl/acl_delete.php Delete ACL rules
	 */
	public function acl_delete_rules($collection)
	{
		static $matchDB    = 2;
		static $matchRole  = 4;
		static $matchAllow = 8;
		
		$matchAll          = $matchDB | $matchRole | $matchAllow;
		
		$acl               = $this->acl();
		$response          = null;
		
		if(!$acl->error)
		{
			$rules   = array();
			$document = $acl->result;
			
			foreach($document['rules'] as $rule)
			{
				foreach($collection as $target)
				{
					if(is_object($target)){
						$target = array('db'=>$target->db, 'role'=>$target->role, 'allow'=>$target->allow);
					}
					
					$match = 0;
					
					
					if($target['db']    == $rule['db'])    $match = $match | $matchDB;
					if($target['role']  == $rule['role'])  $match = $match | $matchRole;
					if($target['allow'] == $rule['allow']) $match = $match | $matchAllow;

					if($match != $matchAll)
					{
						$key = hash('md5', serialize($rule));
						if(!isset($rules[$key]))
						{
							$rules[$key] = $rule;
						}
					}
				}
			}
			$rules = array_values($rules);

			$document['rules'] = $rules;
			$json              = couchdb_json_encode($document);
			$id                = '_local/_acl';
			$batch             = false;
			$connection        = new CouchDBConnection($this->connectionOptions);
			$response          = $connection->execute(new PutDocument('users', $json, $id, $batch));
			
			return $response;
		}
	}
	
	/**
	 * Validate an ACL rule
	 *
	 * @param string $rule 
	 * @return boolean
	 * @author Adam Venturella
	 */
	private function acl_rule_is_valid($rule)
	{
		$result = false;
		
		if(is_array($rule))
		{
			if(isset($rule['db']) && isset($rule['role']) && isset($rule['allow'])){
				$result = true;
			}
		}
		else if (is_object($rule))
		{
			if(isset($rule->db) && isset($rule->role) && isset($rule->allow)){
				$result = true;
			}
		}
		
		return $result;
	}
	
	/**
	 * Log a user into a session, set the session cookie if desired.
	 * the default AuthSession is 10 minutes.  You can change this in your
	 * CouchDB config if desired
	 *
	 * @param string $username 
	 * @param string $password
	 * @param boolean $setcookie 
	 * @return string | null
	 * @author Adam Venturella
	 * @example ../samples/users/session_login.php Create a user session
	 */
	public function session_login($username, $password, $setcookie=false)
	{
		$session       = null;
		$connection    = new CouchDBConnection($this->connectionOptions);
		try
		{
			$response      = $connection->execute(new SessionLogin($username, $password));
		}
		catch(Exception $e){}
		
		if(isset($response->headers['Set-Cookie']) && strpos($response->headers['Set-Cookie'], 'AuthSession') !== false)
		{
			if(!$this->connectionOptions)
			{
				$this->connectionOptions = array();
			}

			$session = $response->headers['Set-Cookie'];
			
			$this->connectionOptions['authorization']          = 'cookie';
			$this->connectionOptions['authorization_session']  = $session;

			if($setcookie)
			{
				header('Set-Cookie: '.$session);
			}
		}
		
		return $session;
	}
	
	/**
	 * Log a user out of a session, set the logout cookie if desired
	 *
	 * @param boolean $setcookie
	 * @return string | null
	 * @author Adam Venturella
	 * @example ../samples/users/session_logout.php Destroy a user session
	 */
	public function session_logout($setcookie=false)
	{
		$session    = null;
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new SessionLogout());
		
		if($response->headers['status']['code'] == 200)
		{
			if($this->connectionOptions['authorization'] == 'cookie')
			{
				$this->connectionOptions['authorization'] = null;
			}
			
			if(isset($this->connectionOptions['authorization_session']))
			{
				$this->connectionOptions['authorization_session'] = null;
			}
			
			if(isset($response->headers['Set-Cookie']) && strpos($response->headers['Set-Cookie'], 'AuthSession') !== false)
			{
				$session = $response->headers['Set-Cookie'];
				
				if($setcookie)
				{
					header('Set-Cookie: '.$session);
				}
			}
		}
		
		return $session;
	}
	
	/**
	 * Function that determines weather or not an action should be performed
	 * on a database.  All CouchDB methods that require an active database
	 * call this method: CouchDB::document(), CouchDB::put(), CouchDB::delete()
	 * CouchDB::attachment(), CouchDB::put_attachment(), CouchDB::delete_attachment(), etc.
	 *
	 * @return bool
	 * @author Adam Venturella
	 */
	private function shouldPerformActionWithDatabase()
	{
		return isset($this->connectionOptions['database']);
	}
	
	/**
	 * Get the latest revision id for a given document
	 *
	 * @param string $document the document id
	 * @return string
	 * @author Adam Venturella
	 */
	private function _revisionForDocument($document)
	{
		if($this->shouldPerformActionWithDatabase())
		{
			$connection = new CouchDBConnection($this->connectionOptions);
			$response   = $connection->execute(new GetDocument($this->connectionOptions['database'], $document));
			return $response->result['_rev'];
		}
		else
		{
			$this->throw_no_database_exception();
		}
	}
	
	
	/**
	 * Get version information for the current CouchDB server
	 *
	 * @return void
	 * @author Adam Venturella
	 **/
	private function _version()
	{
		$connection = new CouchDBConnection($this->connectionOptions);
		$response   = $connection->execute(new Version());
		
		return $response->result['version'];
	}
	
	/**
	 * Function that is called when CouchDB::shouldPerformActionWithDatabase()
	 * return false
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	private function throw_no_database_exception()
	{
		throw new Exception('CouchDB no database has been specified');
	}

	/**
	 * Overloaded property handler.
	 * version is treated like a property not a function.
	 *
	 * @param string $value the name of property to retrieve 
	 * @return string
	 * @author Adam Venturella
	 * @example ../samples/setup/version.php Get the server version.
	 */	
	public function __get($key)
	{
		$value = null;
		switch($key)
		{
			case 'version':
				$value =  $this->_version();
				break;
		}
		
		return $value;
	}
	
}
?>
