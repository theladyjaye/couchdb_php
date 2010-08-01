<?php
class CouchDBReplication extends PHPUnit_Framework_TestCase
{
	/* REPLICATION */
	
	public function testReplication()
	{
		$this->couchdb->create_database(CouchDBTestConstants::kAltDatabaseName);
		$this->couchdb->replicate(CouchDBTestConstants::kDatabaseName, CouchDBTestConstants::kAltDatabaseName);
		
		$info1 = $this->couchdb->info(CouchDBTestConstants::kDatabaseName);
		$info2 = $this->couchdb->info(CouchDBTestConstants::kAltDatabaseName);
		
		$this->assertEquals($info1['doc_count'], $info2['doc_count']);
	}
	
	public function testReplicationViewDefault()
	{
		$replicated = new CouchDB( array('database' => CouchDBTestConstants::kAltDatabaseName) );
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
		$replicated = new CouchDB( array('database' => CouchDBTestConstants::kAltDatabaseName) );
		$result = $replicated->view('records/update');
		$records = count($result);
		$this->assertEquals(1, $records);
	}
	
	public function testReplicationViewDefaultCount()
	{
		$replicated = new CouchDB( array('database' => CouchDBTestConstants::kAltDatabaseName) );
		$result     = $replicated->view('records/default_count');
		$this->assertEquals(2, $result[0]['value']);
	}
	
	public function testReplicationAttachment1()
	{
		$replicated      = new CouchDB( array('database' => CouchDBTestConstants::kAltDatabaseName) );
		$original_path   = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename1);
		$attachment      = $replicated->attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName1);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	public function testReplicationAttachmentPDF()
	{
		$replicated      = new CouchDB( array('database' => CouchDBTestConstants::kAltDatabaseName) );
		
		$original_path   = $this->pathForResource(CouchDBTestConstants::kAttachmentFilenamePDF);
		$attachment      = $replicated->attachment(CouchDBTest::$id_pdf, CouchDBTestConstants::kAttachmentNamePDF);
		$data1           = hash('md5', $attachment);
		$data2           = hash('md5', file_get_contents($original_path));
		
		$this->assertEquals($data1, $data2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReplicationAttachment2NotReplicated()
	{
		$replicated      = new CouchDB( array('database' => CouchDBTestConstants::kAltDatabaseName) );
		$original_path   = $this->pathForResource(CouchDBTestConstants::kAttachmentFilename2);
		$attachment      = $replicated->attachment(CouchDBTest::$id, CouchDBTestConstants::kAttachmentName2);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReplicationDeleteWithValue()
	{
		$this->couchdb->delete_database(CouchDBTestConstants::kAltDatabaseName);
		$info = $this->couchdb->info(CouchDBTestConstants::kAltDatabaseName);
	}
}
?>