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
	
	const kAdminUsername            = 'testadmin';
	const kAdminPassword            = 'secretpassword';
	
	protected $couchdbNoAuth;
	protected $couchdb;
	
	public static $id;
	public static $id_pdf;
	
	protected function pathForResource($resource)
	{
		return implode(DIRECTORY_SEPARATOR, array(__DIR__, CouchDBTest::kAttachmentDirectory, $resource));
	}
	
	/**
	 * @covers CouchDB::__construct
	 */
	protected function setUp()
	{
		$optionsAuth         = array('database'=>CouchDBTest::kDatabaseName, 'authorization'=>'basic', 'username'=>CouchDBTest::kAdminUsername, 'password'=>CouchDBTest::kAdminPassword);
		$optionsNoAuth       = array('database'=>CouchDBTest::kDatabaseName);
		$this->couchdb       = new CouchDB($optionsAuth);
		$this->couchdbNoAuth = new CouchDB($optionsNoAuth);
	}
	
	/* DATABASE */
	
	/**
	 * @covers CouchDB::__get
	 * @covers CouchDB::_version
	 * @covers Version
	 */
	public function testDatabaseVersion()
	{
		$version = null;
		
		// ignore any authorization
		
		$version = $this->couchdbNoAuth->version;
		$this->assertNotNull($version);
		
		echo 'CouchDB Version: '.$version."\n";
	}
	
	/* USERS */
	public function testAdminCreate()
	{
		$response = $this->couchdbNoAuth->admin_create(CouchDBTest::kAdminUsername, CouchDBTest::kAdminPassword);
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
	}
	
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
		$this->couchdb->create_database(CouchDBTest::kAltDatabaseName);
		$info = $this->couchdb->info(CouchDBTest::kAltDatabaseName);
		
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $info);
		$this->assertEquals($info['db_name'], CouchDBTest::kAltDatabaseName);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDatabaseDeleteWithValue()
	{
		$this->couchdb->delete_database(CouchDBTest::kAltDatabaseName);
		$info = $this->couchdb->info(CouchDBTest::kAltDatabaseName);
	}
	
	/**
	 * @covers CouchDB::info
	 * @covers CouchDB::create_database
	 */
	public function testDatabaseCreationWithDefaultOptions()
	{
		$this->couchdb->create_database();
		$info = $this->couchdb->info(CouchDBTest::kDatabaseName);
		
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $info);
		$this->assertEquals($info['db_name'], CouchDBTest::kDatabaseName);
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
		$document->type         = CouchDBTest::kDefaultType;
		$document->creationDate = date('c', time());
		
		$couchdb = new CouchDB();
		$couchdb->put($document);
	}
	
	public function testDocumentCreateStdClass()
	{
		$document               = new stdClass();
		$document->label        = 'Test1';
		$document->type         = CouchDBTest::kDefaultType;
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
		$document['type']         = CouchDBTest::kDefaultType;
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
		$document['type']         = CouchDBTest::kDefaultType;
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
		$this->assertEquals($document['type'], CouchDBTest::kDefaultType);
		$this->assertNotNull($document['_rev']);
		$this->assertNotNull($document['label']);
		
		$this->assertNotNull($document['creationDate']);
		
		$document['type']  = CouchDBTest::kUpdateType;
		$document['email'] = CouchDBTest::kEmailAddress;
		
		$result            = $this->couchdb->put($document, CouchDBTest::$id);
		
		$this->assertEquals($result['ok'], true);
		$this->assertEquals($result['id'], CouchDBTest::$id);
		
		$document = $this->couchdb->document(CouchDBTest::$id);
		
		$this->assertEquals($document['type'], CouchDBTest::kUpdateType);
		$this->assertEquals($document['email'], CouchDBTest::kEmailAddress);
	}
	
	/* ATTACHMENTS */
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentGetAttachmentWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentDeleteAttachmentWithNoDatabaseOption()
	{
		$couchdb = new CouchDB();
		$couchdb->delete_attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentPutAttachmentWithNoDatabaseOption()
	{
		$attachment       = new stdClass();
		$attachment->name = CouchDBTest::kAttachmentName1;
		$attachment->path = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		
		$couchdb = new CouchDB();
		$couchdb->put_attachment($attachment);
	}

	public function testDocumentUpdateWithAttachmentAsStdClassPNG()
	{
		$attachment       = new stdClass();
		$attachment->name = CouchDBTest::kAttachmentName1;
		$attachment->path = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		$result = $this->couchdb->put_attachment($attachment, CouchDBTest::$id);
		$this->assertEquals($result['ok'], true);
	}
	
	public function testDocumentUpdateWithAttachmentAsStdClassPNGNoName()
	{
		$attachment       = new stdClass();
		$attachment->path = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		$result = $this->couchdb->put_attachment($attachment);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$document = $this->couchdb->document($id);
		$this->assertArrayHasKey(basename($attachment->path), $document['_attachments']);
	}
	
	public function testDocumentGetAttachmentPNG()
	{
		$original_path   = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		$attachment      = $this->couchdb->attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName1);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testDocumentUpdateWithAdditionalAttachmentAsArrayPNG()
	{
		$attachment         = array();
		$attachment['name'] = CouchDBTest::kAttachmentName2;
		$attachment['path'] = $this->pathForResource(CouchDBTest::kAttachmentFilename2);
		$result = $this->couchdb->put_attachment($attachment, CouchDBTest::$id);
		$this->assertEquals($result['ok'], true);
	}
	
	public function testDocumentUpdateWithAttachmentAsArrayPNGNoName()
	{
		$attachment         = array();
		$attachment['path'] = $this->pathForResource(CouchDBTest::kAttachmentFilename2);
		$result = $this->couchdb->put_attachment($attachment);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$document = $this->couchdb->document($id);
		$this->assertArrayHasKey(basename($attachment['path']), $document['_attachments']);
	}
	
	public function testDocumentGetAdditionalAttachmentPNG()
	{
		$original_path   = $this->pathForResource(CouchDBTest::kAttachmentFilename2);
		$attachment      = $this->couchdb->attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName2);
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
		$result = $this->couchdb->delete_attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName2);
		$this->assertEquals($result['ok'], true);
		
		$document   = $this->couchdb->document(CouchDBTest::$id);
		$this->assertArrayHasKey(CouchDBTest::kAttachmentName1, $document['_attachments']);
		$this->assertArrayNotHasKey(CouchDBTest::kAttachmentName2, $document['_attachments']);
	}
	
	public function testDocumentCreateWithAttachmentPNG()
	{
		
		$attachment       = new stdClass();
		$attachment->name = CouchDBTest::kAttachmentName1;
		$attachment->path = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		
		$result = $this->couchdb->put_attachment($attachment);
		$this->assertEquals($result['ok'], true);
		
		$id = $result['id'];
		
		$document = $this->couchdb->document($id);
		$this->assertArrayHasKey(CouchDBTest::kAttachmentName1, $document['_attachments']);
		
		
		$original_path = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		$data1         = hash('md5', $this->couchdb->attachment($id, CouchDBTest::kAttachmentName1));
		$data2         = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testDocumentCreateWithAttachmentHTML()
	{
		
		$key              = CouchDBTest::kAttachmentNameHTML;
		$filename         = CouchDBTest::kAttachmentFilenameHTML;
		$content_type     = 'text/html';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentXML()
	{
		
		$key              = CouchDBTest::kAttachmentNameXML;
		$filename         = CouchDBTest::kAttachmentFilenameXML;
		$content_type     = 'text/xml';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentRSS()
	{
		
		$key              = CouchDBTest::kAttachmentNameRSS;
		$filename         = CouchDBTest::kAttachmentFilenameRSS;
		$content_type     = 'application/rss+xml';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentATOM()
	{
		
		$key              = CouchDBTest::kAttachmentNameATOM;
		$filename         = CouchDBTest::kAttachmentFilenameATOM;
		$content_type     = 'application/atom+xml';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentPY()
	{
		
		$key              = CouchDBTest::kAttachmentNamePY;
		$filename         = CouchDBTest::kAttachmentFilenamePY;
		$content_type     = 'text/plain';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentPHP()
	{
		
		$key              = CouchDBTest::kAttachmentNamePHP;
		$filename         = CouchDBTest::kAttachmentFilenamePHP;
		$content_type     = 'text/plain';
		
		$result = $this->putAttachment($key, $filename);
		$this->assertEquals($result['ok'], true);
		$id = $result['id'];
		
		$this->verifyAttachmentForDocument($key, $filename, $id, $content_type);
	}
	
	public function testDocumentCreateWithAttachmentPDF()
	{
		
		$key              = CouchDBTest::kAttachmentNamePDF;
		$filename         = CouchDBTest::kAttachmentFilenamePDF;
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
		$attachment->name             = CouchDBTest::kAttachmentNameXML;
		$attachment->path             = $this->pathForResource(CouchDBTest::kAttachmentFilenameXML);
		$attachment->content_type     = CouchDBTest::kCustomContentType;
		
		$result = $this->couchdb->put_attachment($attachment, CouchDBTest::$id);
		$this->assertEquals($result['ok'], true);
		
		$id = $result['id'];
		$document = $this->couchdb->document($id);
		
		$this->assertArrayHasKey(CouchDBTest::kAttachmentNameXML, $document['_attachments']);
		$this->assertEquals($document['_attachments'][CouchDBTest::kAttachmentNameXML]['content_type'], CouchDBTest::kCustomContentType);
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
		$type = CouchDBTest::kDefaultType;
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
		$type = CouchDBTest::kDefaultType;
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
		$type = CouchDBTest::kUpdateType;
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
		$default = CouchDBTest::kDefaultType;
		$update  = CouchDBTest::kUpdateType;
		
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
		$update  = CouchDBTest::kUpdateType;
		
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
		$default = CouchDBTest::kDefaultType;
		$update  = CouchDBTest::kUpdateType;
		
		$map = <<<FUNCTION
		function(doc) 
		{ 
			if(doc.type == '$default' || doc.type == '$update')
			{ 
				emit(doc.email, doc);
			}
		}
FUNCTION;

		$result = $this->couchdb->temp_view($map);
		$records = count($result);
		$this->assertEquals(1, $records);
	}
	
	public function testTempViewWithReduce()
	{
		$default = CouchDBTest::kDefaultType;
		$update  = CouchDBTest::kUpdateType;
		
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
		$default = CouchDBTest::kDefaultType;
		$update  = CouchDBTest::kUpdateType;
		
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
		$type = CouchDBTest::kUpdateType;
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
		$response = $this->couchdb->compact(CouchDBTest::kDatabaseName);
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
	
	/* REPLICATION */
	
	public function testReplication()
	{
		$this->couchdb->create_database(CouchDBTest::kAltDatabaseName);
		$this->couchdb->replicate(CouchDBTest::kDatabaseName, CouchDBTest::kAltDatabaseName);
		
		$info1 = $this->couchdb->info(CouchDBTest::kDatabaseName);
		$info2 = $this->couchdb->info(CouchDBTest::kAltDatabaseName);
		
		$this->assertEquals($info1['doc_count'], $info2['doc_count']);
	}
	
	public function testReplicationViewDefault()
	{
		$replicated = new CouchDB( array('database' => CouchDBTest::kAltDatabaseName) );
		$result = $replicated->view('records/default');
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
	}
	
	public function testReplicationViewUpdate()
	{
		$replicated = new CouchDB( array('database' => CouchDBTest::kAltDatabaseName) );
		$result = $replicated->view('records/update');
		$records = count($result);
		$this->assertEquals(1, $records);
	}
	
	public function testReplicationViewDefaultCount()
	{
		$replicated = new CouchDB( array('database' => CouchDBTest::kAltDatabaseName) );
		$result     = $replicated->view('records/default_count');
		$this->assertEquals(2, $result[0]['value']);
	}
	
	public function testReplicationAttachment1()
	{
		$replicated      = new CouchDB( array('database' => CouchDBTest::kAltDatabaseName) );
		$original_path   = $this->pathForResource(CouchDBTest::kAttachmentFilename1);
		$attachment      = $replicated->attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName1);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testReplicationAttachmentPDF()
	{
		$replicated      = new CouchDB( array('database' => CouchDBTest::kAltDatabaseName) );
		
		$original_path   = $this->pathForResource(CouchDBTest::kAttachmentFilenamePDF);
		$attachment      = $replicated->attachment(CouchDBTest::$id_pdf, CouchDBTest::kAttachmentNamePDF);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReplicationAttachment2NotReplicated()
	{
		$replicated      = new CouchDB( array('database' => CouchDBTest::kAltDatabaseName) );
		$original_path   = $this->pathForResource(CouchDBTest::kAttachmentFilename2);
		$attachment      = $replicated->attachment(CouchDBTest::$id, CouchDBTest::kAttachmentName2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReplicationDeleteWithValue()
	{
		$this->couchdb->delete_database(CouchDBTest::kAltDatabaseName);
		$info = $this->couchdb->info(CouchDBTest::kAltDatabaseName);
	}
	
	
	/* CLEANUP */
	
	public function testDocumentDelete()
	{
		$result = $this->couchdb->delete(CouchDBTest::$id);
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);
		$this->assertEquals($result['ok'], true);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testDocumentConfirmDelete()
	{
		$document = $this->couchdb->document(CouchDBTest::$id);
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
	
	/**
	 * @expectedException Exception
	 */
	public function testDatabaseDeleteWithDefaultOptions()
	{
		$this->couchdb->delete_database();
		$info = $this->couchdb->info(CouchDBTest::kDatabaseName);
		
	}
	
	public function testAdminDelete()
	{
		$response = $this->couchdb->admin_delete(CouchDBTest::kAdminUsername);
		$this->assertEquals('200', $response->headers['status']['code']);
		$this->assertEquals('OK', $response->headers['status']['message']);
	}
	

}
?>