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
 * Put Attachment Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class PutAttachment implements CouchDBCommand 
{
	private $database;
	private $attachment;
	private $document;
	private $revision;
	
	// $attachment['name']
	// $attachment['path']
	// $attachment['content-type']
	/**
	 * undocumented function
	 *
	 * @param string $database 
	 * @param string $attachment 
	 * @param string $document 
	 * @param string $revision 
	 * @author Adam Venturella
	 */
	public function __construct($database, $attachment, $document=null, $revision=null)
	{
		$this->database     = $database;
		$this->attachment   = $attachment;
		$this->document     = $document ? $document : couchdb_generate_id();
		$this->revision     = $revision;
	}
	
	public function request()
	{
		$content_length = strlen($this->json);
		
		if($this->document && $this->revision)
		{
			return $this->attachmentForDocument();
		}
		else
		{
			return $this->documentForAttachemnt();
		}
	}
	
	private function contentType()
	{
		$value = null;
		
		if(is_array($this->attachment))
		{
			if(isset($this->attachment['content_type']))
			{
				$value = $this->attachment['content_type'];
			}
		}
		else if(is_object($this->attachment))
		{
			if(!empty($this->attachment->content_type))
			{
				$value = $this->attachment->content_type;
			}
		}
		
		if(!$value) $value = $this->determineContentType();
		
		return $value;
	}
	
	
	private function determineContentType()
	{
		
		// tempted to use Fileinfo, but it's not standard until PHP 5.3
		// this is just a best guess function, it's not very smart at all.
		$contentType = '';
		
		$path     = $this->attachmentPath();
		$filename = basename($path);
		list($name, $extension) = explode('.', $filename);
		
		switch($extension)
		{
			case 'txt':
			case 'php':
			case 'py':
			case 'pl':
			case 'cgi':
			case 'rb':
			case 'erl':
			case 'csv':
				$contentType = 'text/plain';
				break;
			
			case 'html':
			case 'htm':
				$contentType = 'text/html';
				break;
			
			case 'rss':
				$contentType = 'application/rss+xml';
				break;
			
			case 'atom':
				$contentType = 'application/atom+xml';
				break;
			
			case 'rdf':
				$contentType = 'application/rdf+xml';
				break;
			
			case 'xml':
				$contentType = 'text/xml';
				break;
				
			case 'gif':
			case 'jpeg':
			case 'jpg':
			case 'png':
				$contentType = 'image';
				break;
			
			case 'pdf':
				$contentType = 'application/pdf';
				break;
			
			default:
				$contentType = "binary/octet-stream";
				break;
		}
		
		if($contentType == 'image')
		{
			list($width, $height, $type) = getimagesize($this->attachmentPath());
			switch ($type)
			{
				case IMAGETYPE_GIF:
					$contentType = "image/gif";
					break;
				
				case IMAGETYPE_JPEG:
					$contentType = "image/jpeg";
					break;
				
				case IMAGETYPE_PNG:
					$contentType = "image/png";
					break;
			
				default:
					$contentType = "binary/octet-stream";
					break;
			}
		}
		
		return $contentType;
	}
	
	private function attachmentPath()
	{
		$value = null;
		if(is_array($this->attachment))
		{
			$value = $this->attachment['path'];
		}
		else if(is_object($this->attachment))
		{
			$value = $this->attachment->path;
		}
		
		return $value;
	}
	
	private function attachmentName()
	{
		$value = null;
		
		if(is_array($this->attachment))
		{
			if(isset($this->attachment['name']))
			{
				$value = $this->attachment['name'];
			}
		}
		else if(is_object($this->attachment))
		{
			if(!empty($this->attachment->name))
			{
				$value = $this->attachment->name;
			}
		}
		
		if(!$value)
		{
			if(is_array($this->attachment))
			{
				$value = basename($this->attachment['path']);
			}
			else if(is_object($this->attachment))
			{
				$value = basename($this->attachment->path);
			}
		}
		
		return $value;
		
	}
	
	private function attachmentForDocument()
	{
		$name           = $this->attachmentName();
		$content_length = filesize($this->attachmentPath());
		$content_type   = $this->contentType();
		$bytes          = file_get_contents($this->attachmentPath());
		
		return <<<REQUEST
PUT /$this->database/$this->document/$name?rev=$this->revision HTTP/1.0
Host: {host}
Connection: Close
Content-Length: $content_length
Content-Type: $content_type
{authorization}

$bytes
REQUEST;
	}
	
	private function documentForAttachemnt()
	{
		$name           = $this->attachmentName();
		$content_length = filesize($this->attachmentPath());
		$content_type   = $this->contentType();
		
		$bytes          = file_get_contents($this->attachmentPath(), FILE_BINARY);
		
		return <<<REQUEST
PUT /$this->database/$this->document/$name HTTP/1.0
Host: {host}
Connection: Close
Content-Length: $content_length
Content-Type: $content_type
{authorization}

$bytes
REQUEST;
	}
	
	public function __toString()
	{
		return 'PutAttachment';
	}
}
?>