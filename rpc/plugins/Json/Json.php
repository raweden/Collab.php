<?php

/**
 * plugin allowing service calls coming from JavaScript encoded as JSON 
 * strings and returned as JSON strings using POST parameters. 
 * Requires at least PHP 5.2.
 *
 * @package Amfphp_Plugins_Json
 * @author Yannick DOMINGUEZ
 */
class Json implements IDeserializer, IDeserializedRequestHandler, IExceptionHandler, ISerializer {

	/**
	* the content-type string indicating a JSON content
	*/
	const JSON_CONTENT_TYPE = "application/json";
	
	private static $mimetype = null;	// 
	
	/**
	 * constructor. Add filters on the HookManager.
	 * @param array $config optional key/value pairs in an associative array. Used to override default configuration values.
	 */
	public function  __construct(array $config = null) {
		$filterManager = FilterManager::getInstance();
		$filterManager->addFilter(Gateway::FILTER_DESERIALIZER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_DESERIALIZED_REQUEST_HANDLER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_EXCEPTION_HANDLER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_SERIALIZER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_HEADERS, $this, "filterHeaders");
	}

	/**
	 * If the content type contains the "json" string, returns this plugin
	 * @param mixed null at call in gateway.
	 * @param String $contentType
	 * @return this or null
	 */
	public function filterHandler($handler, $contentType){
		if(strpos($contentType, self::JSON_CONTENT_TYPE) !== false)
			return $this;
		return $handler;
	}

	/**
	 * @see IDeserializer
	 */
	public function deserialize(array $getData, array $postData, $rawPostData){
		return json_decode($rawPostData);
	}
	
	private function deserializerLexer($data){
		
	}

	/**
	 * Retrives the command name or serviceName & methodName and arguments.
	 * 
	 * @see IDeserializedRequestHandler
	 * @return the service call response
	 */
	public function handleDeserializedRequest($deserializedRequest, ServiceRouter $serviceRouter){
		
		if( isset($deserializedRequest->command)){
			
			// collab implementation.
			$command = implode(".", $deserializedRequest->command);
			$methodName = array_pop($command);
			$serviceName = implode(".", $command);
		}else{			
			
			// Silexlabs implementation.
			if(isset ($deserializedRequest->serviceName)){
				$serviceName = $deserializedRequest->serviceName;
			}else{
				throw new Exception("Service name field missing in POST parameters \n" . print_r($deserializedRequest, true));
			}
			if(isset ($deserializedRequest->methodName)){
				$methodName = $deserializedRequest->methodName;
			}else{
				throw new Exception("MethodName field missing in POST parameters \n" . print_r($deserializedRequest, true));
			}
		}		
		
		$arguments = array();
		if( isset($deserializedRequest->arguments)){
			
			// collab implementation.
			$arguments = $deserializedRequest->arguments;
		}else if( isset ($deserializedRequest->parameters)){
			
			// silexlabs implementatio.
			$arguments = $deserializedRequest->parameters;
		}
		return $serviceRouter->executeServiceCall($serviceName, $methodName, $arguments);
		
 	}

	/**
	 * @see IExceptionHandler
	 */
	public function handleException(Exception $exception){
		// collab implementation.
		$error = array();
		$error["type"] = get_class($exception);
		$error["code"] = $exception->getCode();
		$error["text"] = $exception->getMessage();
		
		// Skip sending stacktrace, its need to be parsed. TODO: implement a way to parse local file name and line number from strack trace.
		//$details = $exception->getTraceAsString();
		// localizes the paths of the stack-trace.
		//if(defined(WEB_ROOT)){
		//	$details = str_replace(WEB_ROOT, "", $details);	
		//}
		//$error["faultDetails"] = $details;
		
		return $error;
	}
	
	/**
	 * Encode the PHP object returned from the service call into a JSON string
	 * @see ISerializer
	 * @return the encoded JSON string sent to JavaScript
	 */
	public function serialize($data){
				
		if(self::$mimetype == null){
			// TODO: catch error when encoding the json.
			$data = json_encode($data);
			// Reverts some escape characters that don't shoudl be ecaped.
			$data = str_replace("\\/","/",$data);
		}
		
		return $data;

	}
	
	private function serializerLexer(&$data){
		
	}

	public function filterHeaders($headers){
		if(self::$mimetype){
			$headers["Content-Type"] = self::$mimetype;
		}
		
		return $headers;
	}
	
	public static function overrideMimeType($mimetype){
		if(!is_string($mimetype)){
			throw new Exception("expected string");
		}
		self::$mimetype = $mimetype;
	}


}
?>
