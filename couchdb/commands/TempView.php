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
 * Get View Command
 *
 * @package Commands
 * @author Adam Venturella
 */
class TempView implements CouchDBCommand 
{
	private $database;
	private $map;
	private $reduce;
	private $options;
	
	/**
	 * undocumented function
	 *
	 * @param string $database 
	 * @param string $map
	 * @param string $reduce
	 * @param string $options 
	 * @author Adam Venturella
	 */
	public function __construct($database, $map, $reduce=null, $options=null)
	{
		$this->database = $database;
		$this->map      = $map;
		$this->reduce   = $reduce;
		$this->options  = $options;
	}
	
	public function request()
	{
		$location = '/'.$this->database.'/_temp_view';
		
		if(!empty($this->options))
		{
			$location .= '?';
			$this->options = array_map("couchdb_json_encode", $this->options);
			$location .= http_build_query($this->options);
			
		}
		
		$this->map = strtr($this->map, array("\n"=>'', "\t"=>''));
		$map = couchdb_json_encode($this->map);
		
		$function       = "{ \"map\": $map";
		
		if($this->reduce)
		{
			$this->reduce = strtr($this->reduce, array("\n"=>'', "\t"=>''));
			$reduce = couchdb_json_encode($this->reduce);
			$function .=",";
			$function .= "\"reduce\": $reduce";
		}
		
		$function .= "}";
		
		$content_length = strlen($function);
		
		return <<<REQUEST
POST $location HTTP/1.0
Content-Length: $content_length
Content-Type: application/json
{authorization}

$function
REQUEST;
	}
	
	public function __toString()
	{
		return 'TempView';
	}
}
?>