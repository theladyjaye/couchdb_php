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
 * Responsible for processing and making available he CouchDB Server's Response to Commands 
 *
 * @package Net
 * @author Adam Venturella
 */
class CouchDBResponse
{
	private $_headers;
	private $_data;
	private $_error;
	private $_result;
	
	/**
	 * undocumented function
	 *
	 * @param string $data 
	 * @return void
	 * @author Adam Venturella
	 */
	public static function responseWithData($data)
	{
		$response = new CouchDBResponse();
		$response->setData($data);
		return $response;
	}
	
	public static function responseWithAttachment($data)
	{
		$response = new CouchDBResponse();
		$response->setAttachmentData($data);
		return $response;
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $data 
	 * @return void
	 * @author Adam Venturella
	 */
	private function setAttachmentData($data)
	{
		$this->initializeResponse($data);
		
		if($this->_headers['status']['code'] == 404)
		{
			$this->_result = couchdb_json_decode($this->_data);
			$this->_error = array('error'=>$this->_result['error'], 'reason'=>$this->_result['reason']);
		}
		else
		{
			$this->_result = $this->_data;
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $data 
	 * @return void
	 * @author Adam Venturella
	 */
	private function setData($data)
	{
		$this->initializeResponse($data);
		
		$this->_result = couchdb_json_decode($this->_data);
			
		if(isset($this->_result['error']))
		{
			$this->_error = array('error'=>$this->_result['error'], 'reason'=>$this->_result['reason']);
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $data 
	 * @return void
	 * @author Adam Venturella
	 */
	private function initializeResponse($data)
	{
		list($headers, $data) = explode("\r\n\r\n", $data);
		
		$this->_data = $data;
		$this->processHeaders($headers);
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $headers 
	 * @return void
	 * @author Adam Venturella
	 */
	private function processHeaders($headers)
	{
		
		$headers = explode("\r\n", $headers);
		
		$this->_headers['status'] = array_shift($headers);
		$status = explode(' ', $this->_headers['status']);
		$status = array_map('trim', $status);
		
		list($protocol, $code, $message) = $status;
		
		$this->_headers['status'] = array('protocol'=>$protocol, 'code'=>$code, 'message'=>$message);
		
		foreach($headers as $header)
		{
			list($key, $value) = explode(':', $header);
			$key   = trim($key);
			$value = trim($value);
			$this->_headers[$key] = $value;
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $value 
	 * @return void
	 * @author Adam Venturella
	 */
	public function __get($value)
	{
		switch($value)
		{
			case 'headers':
				return $this->_headers;
				break;
			
			case 'data':
				return $this->_data;
				break;
			
			case 'error':
				return $this->_error;
				break;
			
			case 'result':
				return $this->_result;
				break;
				
		}
	}
}
?>