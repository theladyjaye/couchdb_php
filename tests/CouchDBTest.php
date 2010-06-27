<?php
/* 
	// users -- needs : password_sha
	POST /_session -d 'username=foo&password=bar' to get the AuthSession cookie
	
	eric@thelog:~$ curl -X GET http://oYeZDICkHV:pwadmin@localhost:5988/users/luser
	{"_id":"luser","_rev":"2-3d5791248864646ce3243e233fb6906f","username":"luser","roles":["gnome"],"password":"shh"}
	eric@thelog:~$ curl -X GET http://oYeZDICkHV:pwadmin@localhost:5988/users/_local%2F_acl
	{"_id":"_local/_acl","_rev":"0-2","rules":[{"db":"*","role":"_admin","allow":"write"},{"db":"test","role":"gnome","allow":"write"}]}
	eric@thelog:~$ curl -X GET http://luser:shh@localhost:5988/test
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
date_default_timezone_set('America/Los_Angeles');
require_once 'PHPUnit/Framework.php';
require_once 'couchdb/CouchDB.php';
require_once 'CouchDBTestConstants.php';


	
class CouchDBTest extends PHPUnit_Framework_TestCase
{
	const kDatabaseName             = 'couchdb_tests';
	const kAltDatabaseName          = 'couchdb_tests2';
	const kAlt3DatabaseName         = 'couchdb_tests3';
	const kDefaultType              = 'default';
	const kUpdateType               = 'update';
	const kEmailAddress             = 'email@example.com';
	                                
	const kAttachmentDirectory      = '_files';
	const kAttachmentName1          = 'avatar';
	const kAttachmentName2          = 'avatar2';
	const kAttachmentFilename1      = 'avatar.png';
	const kAttachmentFilename2      = 'avatar2.png';
	
	const kAttachmentNameHTML       = 'attachmentHTML';
	const kAttachmentNameXML        = 'attachmentXML';
	const kAttachmentNameRSS        = 'attachmentRSS';
	const kAttachmentNameATOM       = 'attachmentATOM';
	const kAttachmentNamePY         = 'attachmentPY';
	const kAttachmentNamePHP        = 'attachmentPHP';
	const kAttachmentNamePDF        = 'attachmentPDF';
	
	const kAttachmentFilenameHTML   = 'document.html';
	const kAttachmentFilenameXML    = 'data.xml';
	const kAttachmentFilenameRSS    = 'feed.rss';
	const kAttachmentFilenameATOM   = 'feed.atom';
	const kAttachmentFilenamePY     = 'script.py';
	const kAttachmentFilenamePHP    = 'script.php';
	const kAttachmentFilenamePDF    = 'document.pdf';
	
	const kCustomContentType        = 'foo/bar';
	
	const kAdminPrimaryUsername     = 'admin';
	const kAdminPrimaryPassword     = 'admin';
	
	const kAdminSecondaryUsername   = 'testadmin';
	const kAdminSecondaryPassword   = 'secretpassword';
	
	protected $couchdbNoAuth;
	protected $couchdb;
	
	public static $id;
	public static $id_pdf;
	
	protected function pathForResource($resource)
	{
		return implode(DIRECTORY_SEPARATOR, array(__DIR__, CouchDBTestConstants::kAttachmentDirectory, $resource));
	}
	
	/**
	 * @covers CouchDB::__construct
	 */
	protected function setUp()
	{
		$optionsAuth         = null;//array('database'=>CouchDBTestConstants::kDatabaseName, 'authorization'=>'basic', 'username'=>CouchDBTestConstants::kAdminPrimaryUsername, 'password'=>CouchDBTestConstants::kAdminPrimaryPassword);
		$optionsNoAuth       = array('database'=>CouchDBTestConstants::kDatabaseName);
		$this->couchdb       = new CouchDB($optionsNoAuth);
		$this->couchdbNoAuth = new CouchDB($optionsNoAuth);
	}
	
	/* VERSION */
	
	/**
	 * @covers CouchDB::__get
	 * @covers CouchDB::_version
	 * @covers Version
	 */
	public function testDatabaseVersion()
	{
		$version = null;
		
		// ignore any authorization
		
		$version = $this->couchdb->version;
		$this->assertNotNull($version);
		
		echo 'CouchDB Version: '.$version."\n";
	}
	
	/* USERS */
	/*public function testAdminCreate()
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
	
	public function testSessionLogin()
	{
		$response = $this->couchdb->session_login('auser', 'shhh');
		print_r($response);
	}*/
	
	/* DATABASE */
	/**
	 * @expectedException Exception
	 */
	public function testDatabaseCreateWithNoValue()
	{
		$couchdb = new CouchDB();
		$couchdb->create_database();
		
	}
	
	public function testDatabaseCreationWithValue()
	{
		$this->couchdb->create_database(CouchDBTestConstants::kAltDatabaseName);
		$info = $this->couchdb->info(CouchDBTestConstants::kAltDatabaseName);
		
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $info);
		$this->assertEquals($info['db_name'], CouchDBTestConstants::kAltDatabaseName);
	}
	
	public function testDatabaseDeleteWithValue()
	{
		$this->couchdb->delete_database(CouchDBTestConstants::kAltDatabaseName);
		$info = $this->couchdb->info(CouchDBTestConstants::kAltDatabaseName);
		
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $info);
		$this->assertEquals($info['error'], "not_found");
	}
	
	/**
	 * @covers CouchDB::info
	 * @covers CouchDB::create_database
	 */
	public function testDatabaseCreationWithDefaultOptions()
	{
		$this->couchdb->create_database();
		$info = $this->couchdb->info(CouchDBTestConstants::kDatabaseName);
		
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $info);
		$this->assertEquals($info['db_name'], CouchDBTestConstants::kDatabaseName);
	}
	
	/* DOCUMENTS */
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentGetWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->document(1234);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentDeleteWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->delete(1234);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentPutWithNoDatabaseOption()
	{
		$document               = new stdClass();
		$document->label        = 'Test0';
		$document->type         = CouchDBTestConstants::kDefaultType;
		$document->creationDate = date('c', time());
		
		$couchdb = new CouchDB();
		$couchdb->put($document);
	}
	
	public function testDocumentCreateStdClass()
	{
		$document               = new stdClass();
		$document->label        = 'Test1';
		$document->type         = CouchDBTestConstants::kDefaultType;
		$document->creationDate = date('c', time());
		
		$result  = $this->couchdb->put($document);
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);
		$this->assertEquals($result['ok'], true);
		
		CouchDBTest::$id  = $result['id'];
		$this->assertNotNull($result['id']);
	}
	
	public function testDocumentCeateArray()
	{
		$document                 = array();
		$document['label']        = 'Test2';
		$document['type']         = CouchDBTestConstants::kDefaultType;
		$document['creationDate'] = date('c', time());
		
		$result  = $this->couchdb->put($document);
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);
		$this->assertEquals($result['ok'], true);
		
		$this->assertNotNull($result['id']);
	}
	
	public function testDocumentCeateJSON()
	{
		$document                 = array();
		$document['label']        = 'Test3';
		$document['type']         = CouchDBTestConstants::kDefaultType;
		$document['creationDate'] = date('c', time());
		
		$result  = $this->couchdb->put(json_encode($document));
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);
		$this->assertEquals($result['ok'], true);
		
		$this->assertNotNull($result['id']);
	}
	
	public function testDocumentUpdate()
	{
		$document = $this->couchdb->document(CouchDBTest::$id);
		
		$this->assertNotNull($document['type']);
		$this->assertEquals($document['_id'], CouchDBTest::$id);
		$this->assertEquals($document['type'], CouchDBTestConstants::kDefaultType);
		$this->assertNotNull($document['_rev']);
		$this->assertNotNull($document['label']);
		
		$this->assertNotNull($document['creationDate']);
		
		$document['type']  = CouchDBTestConstants::kUpdateType;
		$document['email'] = CouchDBTestConstants::kEmailAddress;
		
		$result            = $this->couchdb->put($document, CouchDBTest::$id);
		
		$this->assertEquals($result['ok'], true);
		$this->assertEquals($result['id'], CouchDBTest::$id);
		
		$document = $this->couchdb->document(CouchDBTest::$id);
		
		$this->assertEquals($document['type'], CouchDBTestConstants::kUpdateType);
		$this->assertEquals($document['email'], CouchDBTestConstants::kEmailAddress);
	}
	
	/* ATTACHMENTS */
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentGetAttachmentWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentDeleteAttachmentWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->delete_attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentPutAttachmentWithNoDatabaseOption()
	{
		$attachment       = new stdClass();
		$attachment->name = CouchDBTestConstants::kAttachmentName1;
		$attachment->path = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		
		$couchdb = new CouchDB();
		$couchdb->put_attachment($attachment);
	}

	public function testDocumentUpdateWithAttachmentAsStdClassPNG()
	{
		$attachment       = new stdClass();
		$attachment->name = CouchDBTestConstants::kAttachmentName1;
		$attachment->path = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		$result = $this->couchdb->put_attachment($attachment, CouchDBTest::$id);
		$this->assertEquals($result['ok'], true);
	}
	
	public function testDocumentUpdateWithAttachmentAsStdClassPNGNoName()
	{
		$attachment       = new stdClass();
		$attachment->path = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		$result = $this->couchdb->put_attachment($attachment);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$document = $this->couchdb->document($id);
		$this->assertArrayHasKey(basename($attachment->path), $document['_attachments']);
	}
	
	public function testDocumentGetAttachmentPNG()
	{
		$original_path   = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		$attachment      = $this->couchdb->attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName1);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testDocumentUpdateWithAdditionalAttachmentAsArrayPNG()
	{
		$attachment         = array();
		$attachment['name'] = CouchDBTestConstants::kAttachmentName2;
		$attachment['path'] = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename2);
		$result = $this->couchdb->put_attachment($attachment, CouchDBTest::$id);
		$this->assertEquals($result['ok'], true);
	}
	
	public function testDocumentUpdateWithAttachmentAsArrayPNGNoName()
	{
		$attachment         = array();
		$attachment['path'] = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename2);
		$result = $this->couchdb->put_attachment($attachment);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$document = $this->couchdb->document($id);
		$this->assertArrayHasKey(basename($attachment['path']), $document['_attachments']);
	}
	
	public function testDocumentGetAdditionalAttachmentPNG()
	{
		$original_path   = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename2);
		$attachment      = $this->couchdb->attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName2);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testDocumentAttachmentCountPNG()
	{
		$document   = $this->couchdb->document(CouchDBTest::$id);
		$attachments = count($document['_attachments']);
		$this->assertEquals($attachments, 2);
	}
	
	public function testDocumentDeleteAttachmentPNG()
	{
		$result = $this->couchdb->delete_attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName2);
		$this->assertEquals($result['ok'], true);
		
		$document   = $this->couchdb->document(CouchDBTest::$id);
		$this->assertArrayHasKey(CouchDBTestConstants::kAttachmentName1, $document['_attachments']);
		$this->assertArrayNotHasKey(CouchDBTestConstants::kAttachmentName2, $document['_attachments']);
	}
	
	public function testDocumentCreateWithAttachmentPNG()
	{
		
		$attachment       = new stdClass();
		$attachment->name = CouchDBTestConstants::kAttachmentName1;
		$attachment->path = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		
		$result = $this->couchdb->put_attachment($attachment);
		$this->assertEquals($result['ok'], true);
		
		$id = $result['id'];
		
		$document = $this->couchdb->document($id);
		$this->assertArrayHasKey(CouchDBTestConstants::kAttachmentName1, $document['_attachments']);
		
		
		$original_path = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		$data1         = hash('md5', $this->couchdb->attachment($id, CouchDBTestConstants::kAttachmentName1));
		$data2         = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testDocumentCreateWithAttachmentHTML()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNameHTML;
		$filename         = CouchDBTestConstants::kAttachmentFilenameHTML;
		$content_type     = 'text/html';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentXML()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNameXML;
		$filename         = CouchDBTestConstants::kAttachmentFilenameXML;
		$content_type     = 'text/xml';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentRSS()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNameRSS;
		$filename         = CouchDBTestConstants::kAttachmentFilenameRSS;
		$content_type     = 'application/rss+xml';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentATOM()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNameATOM;
		$filename         = CouchDBTestConstants::kAttachmentFilenameATOM;
		$content_type     = 'application/atom+xml';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentPY()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNamePY;
		$filename         = CouchDBTestConstants::kAttachmentFilenamePY;
		$content_type     = 'text/plain';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentPHP()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNamePHP;
		$filename         = CouchDBTestConstants::kAttachmentFilenamePHP;
		$content_type     = 'text/plain';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentPDF()
	{
		
		$key              = CouchDBTestConstants::kAttachmentNamePDF;
		$filename         = CouchDBTestConstants::kAttachmentFilenamePDF;
		$content_type     = 'application/pdf';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		CouchDBTest::$id_pdf = $id;
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentCustomContentType()
	{
		
		$attachment                   = new stdClass();
		$attachment->name             = CouchDBTestConstants::kAttachmentNameXML;
		$attachment->path             = $this->pathForResource(CouchDBTestConstants::kAttachmentFilenameXML);
		$attachment->content_type     = CouchDBTestConstants::kCustomContentType;
		
		$result = $this->couchdb->put_attachment($attachment, CouchDBTest::$id);
		$this->assertEquals($result['ok'], true);
		
		$id = $result['id'];
		$document = $this->couchdb->document($id);
		
		$this->assertArrayHasKey(CouchDBTestConstants::kAttachmentNameXML, $document['_attachments']);
		$this->assertEquals($document['_attachments'][CouchDBTestConstants::kAttachmentNameXML]['content_type'], CouchDBTestConstants::kCustomContentType);
	}
	
	private function putAttachment($key, $filename)
	{
		$attachment       = new stdClass();
		
		$attachment->name = $key;
		$attachment->path = $this->pathForResource($filename);
		
		$result = $this->couchdb->put_attachment($attachment);
		
		return $result;
	}
	
	private function verifyAttachmentForDocument($key, $filename, $id, $content_type)
	{
		$document = $this->couchdb->document($id);
		
		$this->assertArrayHasKey($key, $document['_attachments']);
		$this->assertEquals($document['_attachments'][$key]['content_type'], $content_type);
		
		$original_path = $this->pathForResource($filename);
		$attachment = $this->couchdb->attachment($id, $key);
		
		$data1         = hash('md5', $this->couchdb->attachment($id, $key));
		$data2         = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	/* VIEWS */
	
	public function testViewCreate()
	{
		$type = CouchDBTestConstants::kDefaultType;
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$type')
			{ 
				emit(doc._id, doc);
			}
		}
FUNCTION;
		
		$result = $this->couchdb->create_view('records/default', $map);
		$this->assertEquals($result['ok'], true);
		
	}
	
	public function testViewCreateWithReduce()
	{
		$type = CouchDBTestConstants::kDefaultType;
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$type')
			{ 
				emit(doc._id, 1);
			}
		}
FUNCTION;

		$reduce = <<<FUNCTION
		function(keys, values, rereduce)
		{
			return sum(values);
		}
FUNCTION;
		
		$result = $this->couchdb->create_view('records/default_count', $map, $reduce);
		$this->assertEquals($result['ok'], true);
		
	}
	
	public function testViewCreateUpdate()
	{
		$type = CouchDBTestConstants::kUpdateType;
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$type')
			{ 
				emit(doc._id, doc);
			}
		}
FUNCTION;
		
		$result = $this->couchdb->create_view('records/update', $map);
		$this->assertEquals($result['ok'], true);
	}
	
	public function testViewGetDefault()
	{
		$result = $this->couchdb->view('records/default');
		$records = count($result);
		$this->assertEquals(2, $records);
	}
	
	public function testViewGetDefaultIteration()
	{
		$result = $this->couchdb->view('records/default');
		$count  = count($result);
		$this->assertEquals(2, $count);
		
		foreach($result as $document)
		{
			$this->assertNotNull($document);
			$this->assertArrayHasKey('_id', $document);
			$this->assertArrayHasKey('_rev', $document);
			$this->assertArrayHasKey('label', $document);
			$this->assertArrayHasKey('type', $document);
			$this->assertArrayHasKey('creationDate', $document);
			
			$this->assertNotNull($document['_id']);
			$this->assertNotNull($document['_rev']);
			$this->assertNotNull($document['label']);
			$this->assertNotNull($document['type']);
			$this->assertNotNull($document['creationDate']);
		}
		
		$this->assertTrue(isset($result[0]));
		
		try
		{
			unset($result[1]);
			// should never get here, exception should be thrown
			$this->assertFalse(true);
		}
		catch(Exception $e)
		{
			// CouchDBView is read-only
			$this->assertTrue(true);
		}
		
		try
		{
			$result[0] = array();
			// should never get here, exception should be thrown
			$this->assertFalse(true);
		}
		catch(Exception $e)
		{
			// CouchDBView is read-only
			$this->assertTrue(true);
		}
	}
	
	public function testViewGetDefaultCount()
	{
		$result = $this->couchdb->view('records/default_count');
		$this->assertEquals(2, $result[0]['value']);
	}
	
	public function testViewGetUpdate()
	{
		$result = $this->couchdb->view('records/update');
		$records = count($result);
		$this->assertEquals(1, $records);
	}
	
	public function testViewGetUpdateWithKey()
	{
		$result = $this->couchdb->view('records/update', array('key'=>CouchDBTest::$id));
		$records = count($result);
		$this->assertEquals(1, $records);
	}
	
	public function testViewGetDefaultWithKey()
	{
		$result = $this->couchdb->view('records/default', array('key'=>CouchDBTest::$id));
		$records = count($result);
		$this->assertEquals(0, $records);
	}
	
	public function testTempView3()
	{
		$default = CouchDBTestConstants::kDefaultType;
		$update  = CouchDBTestConstants::kUpdateType;
		
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$default' || doc.type == '$update')
			{ 
				emit(doc._id, doc);
			}
		}
FUNCTION;

		$result = $this->couchdb->temp_view($map);
		$records = count($result);
		
		$this->assertEquals($records, 3);
	}
	
	public function testTempView1()
	{
		$update  = CouchDBTestConstants::kUpdateType;
		
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$update')
			{ 
				emit(doc._id, doc);
			}
		}
FUNCTION;

		$result  = $this->couchdb->temp_view($map);
		$records = count($result);
		
		$this->assertEquals($records, 1);
	}
	
	public function testTempViewWithKey()
	{
		$default = CouchDBTestConstants::kDefaultType;
		$update  = CouchDBTestConstants::kUpdateType;
		
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$update')
			{ 
				emit(doc.email, doc);
			}
		}
FUNCTION;
		

		$result = $this->couchdb->temp_view($map);
		$records = count($result);
		$this->assertEquals(1, $records);
		$this->assertEquals($result[0]['value']['email'], CouchDBTestConstants::kEmailAddress);
	}
	
	public function testTempViewWithReduce()
	{
		$default = CouchDBTestConstants::kDefaultType;
		$update  = CouchDBTestConstants::kUpdateType;
		
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$default' || doc.type == '$update')
			{ 
				emit(doc._id, 1);
			}
		}
FUNCTION;

		$reduce = <<<FUNCTION
		function(keys, values, rereduce)
		{
			return sum(values);
		}
		
FUNCTION;

		$result = $this->couchdb->temp_view($map, $reduce);
		$this->assertEquals(3, $result[0]['value']);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testTempViewWithNoDatabaseOption()
	{
		$default = CouchDBTestConstants::kDefaultType;
		$update  = CouchDBTestConstants::kUpdateType;
		
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$default' || doc.type == '$update')
			{ 
				emit(doc._id, 1);
			}
		}
FUNCTION;

		$couchdb = new CouchDB();
		$couchdb->temp_view($map);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testViewWithNoDatabaseOption()
	{
		$type = CouchDBTestConstants::kUpdateType;
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$type')
			{ 
				emit(doc._id, doc);
			}
		}
FUNCTION;
		
		$couchdb = new CouchDB();
		$couchdb->create_view('records/no_database_selected', $map);
	}
	
	/* COMPACTION */
	
	public function testCompaction()
	{
		$response = $this->couchdb->compact(CouchDBTestConstants::kDatabaseName);
		$this->assertEquals('202', $response->headers['status']['code']);
		$this->assertEquals($response->result['ok'], true);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testCompactionWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->compact();
	}
	
	/* CLEANUP */
	public function testDocumentDelete()
	{
		$result = $this->couchdb->delete(CouchDBTest::$id);
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);
		$this->assertEquals($result['ok'], true);
	}
	
	public function testDocumentConfirmDelete()
	{
		$document = $this->couchdb->document(CouchDBTest::$id);
		$this->assertEquals($document['error'], 'not_found');
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDatabaseDeleteWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->delete_database();
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDatabaseInfoWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->info();
	}
	
	public function testDatabaseDeleteWithDefaultOptions()
	{
		$this->couchdb->delete_database();
		$info = $this->couchdb->info(CouchDBTestConstants::kDatabaseName);
		$this->assertEquals($info['error'], 'not_found');
	}
	

}
?>