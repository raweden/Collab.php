<?php

class Proxy{
	
	private $session;
	
	private $headers;
	
	private $method = "GET";
	private $url;
	
	private $username;
	private $password;
	
	/**
	 * Constructor.
	 *
	 * Since this class should only be accessed through the static create() method
	 * this constructor should be made private. Unfortunately, this is not possible
	 * in PHP4.
	 */
	public function __construct(){
		$this->method = "GET";
		
		$this->username = null;
		$this->password = null;
	}
	
	/**
	 * Sets the value of an HTTP request header.
	 * 
	 * @param $name The name of the header whose value is to be set.
	 * @param $value The value to set as the body of the header.
	 */
	public function setRequestHeader($header,$value){
		
		if(!is_string($header) || !is_string($value)){
			return;
		}
		
		// TODO: validate header name and value here.
		
		if($this->headers == null){
			$headers = array();
		}
		
		// headers should be encoded like this "Content-type: text/plain"
		array_push($this->headers, $header . ": " . $value);
	}
		
	/**
	 * @param $method 
	 * @param $url
	 * @param $username The optional username to use for authentication purposes.
	 * @param $password The optional password to use for authentication purposes.
	 */
	public function open($method, $url, $username = "", $password = ""){
		
		$this->url = $url;
		
		// Setting user credentials
		if($username != null && $password != ""){
			$this->username = $username;
		}
		
		if($password != null && $password != ""){
			$this->password = $password;
		}
		// setting method of the request.
		$method = strtoupper($method);		
		switch ($method) {
			case "GET":{
				$this->method = "GET";
				break;
			}
			case "POST":{
				$this->method = "POST";
				break;
			}
			default:{
				break;
			}
		}
		
	}
	
	
	/**
	 * Sends the request. The request is made synchronous and the content of the request is return from this method.
	 * 
	 * @param $body The content to be sent with the request.
	 * @return The content of the request.
	 */
	public function send($body = null){
		
		$session = curl_init($this->url);
		$this->session = $session;
		
		// Managing the request body.
		
		if($body != null){
			
			curl_setopt ($session, CURLOPT_POST, true);
			curl_setopt ($session, CURLOPT_POSTFIELDS, $body);
			
		} // Setting request method.
		else if($this->method == "POST"){
			curl_setopt ($session, CURLOPT_POST, true);
		}

		// Setting user-agent, from the request to this server.
		curl_setopt($session, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"] );

		// Setting custom headers if specified.
		if($this->headers){
			curl_setopt($session, CURLOPT_HTTPHEADER,$this->headers);			
		}
		
		// Setting user credentials if specified.
		if($this->username && $this->password){
				
		}
		
		
		// Don"t return HTTP headers. Do return the contents of the call
		curl_setopt($session, CURLOPT_HEADER, false);
		//curl_setopt($session, CURLOPT_HEADER, ($headers == "true") ? true : false);

		curl_setopt($session, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($session, CURLOPT_TIMEOUT, 4); 
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		
		// Other options to consider.
		
		//curl_setopt($session, CURLOPT_LOW_SPEED_TIME, $value)	// lowest allowed speed, in bytes per second.

		// Make the call
		$result = curl_exec($session);
		
		curl_close($session);
		
		return $result;
	}
		
}


?>