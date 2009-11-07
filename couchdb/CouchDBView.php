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
 * undocumented class
 *
 * @package Core
 * @author Adam Venturella
 */
class CouchDBView implements Countable, Iterator, ArrayAccess
{
	const kDocContext   = 'doc';
	const kValueContext = 'value';
	
	public $context     = CouchDBView::kValueContext;
	private $result;
	private $position;
	private $count;
	
	/**
	 * undocumented function
	 *
	 * @param string $response 
	 * @return void
	 * @author Adam Venturella
	 */
	public static function viewWithResponse($response)
	{
		list($headers, $json) = explode("\r\n\r\n", $response);
		$view = new CouchDBView();
		$view->setJSON($json);
		return $view;
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $json 
	 * @return void
	 * @author Adam Venturella
	 */
	public static function viewWithJSON($json)
	{
		$view = new CouchDBView();
		$view->setJSON($json);
		return $view;
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $json 
	 * @return void
	 * @author Adam Venturella
	 */
	private function setJSON($json)
	{
		$this->result = couchdb_json_decode($json);
	}
	
	/* Countable */
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	public function count()
	{
		if(!$this->count)
		{
			$this->count = count($this->result['rows']);
		}
		
		return $this->count;
	}
	
	/* Iterator */
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	public function rewind() 
	{
		$this->position = 0;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	public function current() 
	{
		return $this->result['rows'][$this->position][$this->context];
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	public function key() 
	{
		return $this->position;
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	public function next() 
	{
		++$this->position;
	}
	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Adam Venturella
	 */
	public function valid() 
	{
		return isset($this->result['rows'][$this->position][$this->context]);
	}
	
	/* ArrayAccess */
	/**
	 * undocumented function
	 *
	 * @param string $offset 
	 * @return void
	 * @author Adam Venturella
	 */
	public function offsetExists($offset)
	{
		return isset( $this->result['rows'][$offset] );
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $offset 
	 * @return void
	 * @author Adam Venturella
	 */
	public function offsetGet( $offset )
	{
		return $this->result['rows'][$offset];
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $offset 
	 * @param string $value 
	 * @return void
	 * @author Adam Venturella
	 */
	public function offsetSet( $offset, $value)
	{
		//pass the result is read-only
		throw new Exception('CouchDBView is read-only');
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $offset 
	 * @return void
	 * @author Adam Venturella
	 */
	public function offsetUnset( $offset )
	{
		//pass the result is read-only
		throw new Exception('CouchDBView is read-only');
	}
}
?>