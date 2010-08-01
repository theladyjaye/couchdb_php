<?php
/**
 *    CouchDB_PHP
 * 
 *    Copyright (C) 2010 Adam Venturella
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
 * Get View Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class CDBList implements CouchDBCommand 
{
	private $database;
	private $list;
	private $view;
	private $options;
	
	/**
	 * undocumented function
	 *
	 * @param string $database name of the database
	 * @param string $list name of the requested list in the format designDoc/list, eg: foo/bar NOT _design/foo/bar.
	 *                     The _design portion should be excluded.  It's considered to be implicit in the request
	 * @param string $view name of the view the list should be applied to.
	 * @param array $options 
	 * @author Adam Venturella
	 */
	public function __construct($database, $list, $view, $options=null)
	{
		$this->database = $database;
		$this->list     = $list;
		$this->view     = $view;
		$this->options  = $options;
	}
	
	public function request()
	{
		list($design, $list) = explode('/', $this->list);
		
		//_design/examples/_list/index-posts/posts-by-tag
		$location = '/'.$this->database.'/_design/'.$design.'/_list/'.$list.'/'.$this->view;
		
		if(!empty($this->options))
		{
			$location .= '?';
			$this->options = array_map("couchdb_json_encode", $this->options);
			$location .= http_build_query($this->options);
		}
		
		return <<<REQUEST
GET $location HTTP/1.0
Host: {host}
Connection: Close
{authorization}


REQUEST;
	}
	
	public function __toString()
	{
		return 'List';
	}
}
?>