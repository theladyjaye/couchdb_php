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
 * Responsble for handling the connections to the CouchDB Server
 * and returning the CouchDBResponse
 *
 * @package Net
 * @author Adam Venturella
 */
class CouchDBConnection
{
	private $_database;
	private $_host;
	private $_port;
	private $_transport;
	private $_timeout;
	private $_authorization;
	private $_authorization_session;
	private $_username;
	private $_password;
	
	/**
	 * undocumented function
	 *
	 * @param array $options 
	 * @author Adam Venturella
	 */
	public function __construct($options=null)
	{
		$this->_database               = isset($options['database'])                ? $options['database']              : null;
		$this->_host                   = isset($options['host'])                    ? $options['host']                  : '127.0.0.1';
		$this->_port                   = isset($options['port'])                    ? $options['port']                  : '5984';
		$this->_timeout                = isset($options['timeout'])                 ? $options['timeout']               : 10;
		$this->_transport              = isset($options['transport'])               ? $options['transport']             : 'tcp://';
		$this->_authorization          = isset($options['authorization'])           ? $options['authorization']         : null;
		$this->_authorization_session  = isset($options['authorization_session'])   ? $options['authorization_session'] : null;
		$this->_username               = isset($options['username'])                ? $options['username']              : null;
		$this->_password               = isset($options['password'])                ? $options['password']              : null;
		
	}
	
	/**
	 * undocumented function
	 *
	 * @param CouchDBCommand $command 
	 * @return void
	 * @author Adam Venturella
	 */
	public function execute(CouchDBCommand $command)
	{
		$request = $command->request();
		
		// default to a header that won't mean anything
		$authorization = "X-CouchDB-PHP-Authenticate: None";
		
		switch($this->_authorization)
		{
			case 'basic':
				$authorization = 'Authorization: Basic '.base64_encode($this->_username.':'.$this->_password);
				break;
			
			case 'cookie':
				$session = null;
				
				if(isset($this->_authorization_session))
				{
					$session = $this->_authorization_session;
				}
				else if (isset($_COOKIE['AuthSession']))
				{
					$session = 'AuthSession='.$_COOKIE['AuthSession'];
				}
				
				$authorization  = "X-CouchDB-WWW-Authenticate: Cookie\r\nCookie: ".$session."\r\n";
				break;
		}
		
		$request = str_replace('{host}', $this->_host.':'.$this->_port, $request);
		$request = str_replace('{authorization}', $authorization, $request);
		$data    = $this->connect($request);
		
		
		// maybe change this to not require the class but place a method in the CouchDBCommand 
		// interface $command->rawResponse() returns bool or something like that.
		// dunno if I am comfortable NEEDING that class here.  On the other hand it seems silly
		// to require all of the commands to need that function.  Abstract CouchDBCommand?
		if(is_a($command, 'GetAttachment'))
		{
			$response = CouchDBResponse::responseWithAttachment($data);
		}
		else
		{
			$response = CouchDBResponse::responseWithData($data);
		}
		

		/*
		if($response->headers['status']['code'] == 401) // Unauthorized 
		{
			if(isset($response->headers['WWW-Authenticate']))
			{
				if (strpos($response->headers['WWW-Authenticate'], 'Basic') !== false)
				{
					// need a way to deal with this for n requests per state basic auth, save baiscally
				}
			}
		} 
		*/
		
		/*if($response->error)
		{
			throw new Exception('CouchDBCommand('.$command.') Failed with error '.$response->error['error'].': '.$response->error['reason']);
		}
		else
		{
			return $response;
		}
		*/
		return $response;
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $request 
	 * @return void
	 * @author Adam Venturella
	 */
	private function connect($request)
	{
		$errno    = null;
		$errstr   = null;
		$response = null;

		$socket = $this->_transport.$this->_host.':'.$this->_port;

		$stream = stream_socket_client($socket, $errno, $errstr, $this->_timeout);

		if(!$stream)
		{
			throw new Exception('CouchDB unable to connect to host '.$socket.' : '.$errno.', '.$errstr);
			return;
		}
		else
		{
			fwrite($stream, $request);
			$response = stream_get_contents($stream);
			fclose($stream);
			return $response;
		}

		fclose($stream);
	}
}
?>