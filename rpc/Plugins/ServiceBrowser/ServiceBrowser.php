<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 * @package Amfphp_Plugins_ServiceBrowser
 */

/**
 * A simple service browser with html only. Sometimes you don't need the full thing with AMF etc., so use this
 * This plugin should not be deployed on a production server.
 * 
 * call the gateway with the following GET parameters:
 * serviceName: the service name
 * methodName : the method to call on the service
 *
 * pass the parameters as POST data. Each will be JSON decoded to be able to pass complex parameters. This requires PHP 5.2 or higher
 *
 * @package Amfphp_Plugins_ServiceBrowser
 * @author Ariel Sommeria-Klein (modified by raweden).
 */
class ServiceBrowser implements IDeserializer, IDeserializedRequestHandler, IExceptionHandler, ISerializer {
	/**
	 * if content type is not set or content is set to "application/x-www-form-urlencoded", this plugin will handle the request
	 */
	const CONTENT_TYPE = "application/x-www-form-urlencoded";

	private $serviceName;

	private $methodName;

	/**
	 * used for service call
	 * @var array
	 */
	private $parameters;

	/**
	 * associative array of parameters. Used to set the parameters input fields to the same values again after a call.
	 * note: stored encoded because that's the way we need them to show them in the dialog
	 * @var array
	 */
	private $parametersAssoc;

	private $serviceRouter;

	private $showResult;

	/**
	 * constructor.
	 * @param array $config optional key/value pairs in an associative array. Used to override default configuration values.
	 */
	public function __construct(array $config = null) {
		$filterManager = FilterManager::getInstance();
		$filterManager->addFilter(Gateway::FILTER_DESERIALIZER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_DESERIALIZED_REQUEST_HANDLER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_EXCEPTION_HANDLER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_SERIALIZER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_HEADERS, $this, "filterHeaders");
	}

	/**
	 * if no content type, then returns this. 
	 * @param mixed null at call in gateway.
	 * @param String $contentType
	 * @return this or null
	 */
	public function filterHandler($handler, $contentType) {
		if (!$contentType || $contentType == self::CONTENT_TYPE) {
			return $this;
		}
	}

	/**
	 * @see Amfphp_Core_Common_IDeserializer
	 */
	public function deserialize(array $getData, array $postData, $rawPostData) {
		$ret = new stdClass();
		$ret->get = $getData;
		$ret->post = $postData;
		return $ret;
	}

	/**
	 * adds an item to an array if and only if a duplicate is not already in the array
	 * @param array $targetArray
	 * @param <type> $toAdd
	 * @return array
	 */
	private function addToArrayIfUnique(array $targetArray, $toAdd) {
		foreach ($targetArray as $value) {
			if ($value == $toAdd) {
				return $targetArray;
			}
		}
		$targetArray[] = $toAdd;
		return $targetArray;
	}

	/**
	 * returns a list of available services
	 * @return array of service names
	 */
	private function getAvailableServiceNames(array $serviceFolderPaths, array $serviceNames2ClassFindInfo) {
		$ret = array();
		foreach ($serviceFolderPaths as $serviceFolderPath) {
			$folderContent = scandir($serviceFolderPath);

			if ($folderContent){
				foreach ($folderContent as $fileName) {
					//add all .php file names, but removing the .php suffix
					if (strpos($fileName, ".php")) {
						$serviceName = substr($fileName, 0, strlen($fileName) - 4);
						$ret = $this->addToArrayIfUnique($ret, $serviceName);
					}
				}
			}
		}

		foreach ($serviceNames2ClassFindInfo as $key => $value) {
			$ret = $this->addToArrayIfUnique($ret, $key);
		}

		return $ret;
	}

	/**
	 * @see IDeserializedRequestHandler
	 */
	public function handleDeserializedRequest($deserializedRequest, ServiceRouter $serviceRouter) {
		$this->serviceRouter = $serviceRouter;

		if (isset($deserializedRequest->get["serviceName"])) {
			$this->serviceName = $deserializedRequest->get["serviceName"];
		}

		if (isset($deserializedRequest->get["methodName"])) {
			$this->methodName = $deserializedRequest->get["methodName"];
		}


		//if a method has parameters, they are set in post. If it has no parameters, set noParams in the GET.
		//if neither case is applicable, an error message with a form allowing the user to set the values is shown
		$paramsGiven = false;
		if (isset($deserializedRequest->post) && $deserializedRequest->post != null) {
			$this->parameters = array();
			$this->parametersAssoc = array();
			//try to json decode each parameter, then push it to $thios->parameters
			$numParams = count($deserializedRequest->post);
			foreach($deserializedRequest->post as $key => $value) {
				$this->parametersAssoc[$key] = $value;
				$decodedValue = json_decode($value);
				$valueToUse = $value;
				if($decodedValue){
					$valueToUse = $decodedValue;
				}
				$this->parameters[] = $valueToUse;
			}
			$paramsGiven = true;
		} else if (isset($deserializedRequest->get["noParams"])) {
			$this->parameters = array();
			$paramsGiven = true;
			//note: use $paramsGiven because somehow if $$this->parameters contains an empty array, ($this->parameters == null) is true. 
		}
		
		if($this->serviceName && $this->methodName && $paramsGiven){
			$this->showResult = true;
			return $serviceRouter->executeServiceCall($this->serviceName, $this->methodName, $this->parameters);
		}else{
			$this->showResult = false;
			return null;
		}
	}

	/**
	 * TODO: show stack trace
	 * @see Amfphp_Core_Common_IExceptionHandler
	 */
	public function handleException(Exception $exception) {
		$exceptionInfo = "<strong>Exception thrown\n<br>";
		$exceptionInfo .= "<code>message</code>: " . $exception->getMessage() . "\n<br>";
		$exceptionInfo .= "<code>code</code>: " . $exception->getCode() . "\n<br>";
		$exceptionInfo .= "<code>file</code>: " . $exception->getFile() . "\n<br>";
		$exceptionInfo .= "<code>line</code>: " . $exception->getLine() . "\n<br></strong>";
			//$exceptionInfo .= "trace : " . str_replace("\n", "<br>\n", print_r($exception->getTrace(), true)) . "\n<br>";
		$this->showResult = true;
	   return $exceptionInfo;
	}

	/**
	 * TODO: Don't show private method like constructor in method list.
	 * 
	 * @see Amfphp_Core_Common_ISerializer
	 */
	public function serialize($data) {
		$availableServiceNames = $this->getAvailableServiceNames($this->serviceRouter->serviceFolderPaths, $this->serviceRouter->serviceNames2ClassFindInfo);
		$message = file_get_contents(dirname(__FILE__) . "/Top.html");
		$message .= "\n<ul>";
		// printing service list.
		foreach ($availableServiceNames as $availableServiceName) {
			$message .= "\n<li><a href='?serviceName=$availableServiceName'>$availableServiceName</a></li>";
		}
		$message .= "\n</ul>";
		// Prints the service method list.
		if($this->serviceName){
			$serviceObject = $this->serviceRouter->getServiceObject($this->serviceName);
			$reflectionObj = new ReflectionObject($serviceObject);
			$availablePublicMethods = $reflectionObj->getMethods(ReflectionMethod::IS_PUBLIC);

			$message .= "<h3>Click below to use a method on the $this->serviceName service</h3>";
			$message .= "\n<ul>";
			foreach ($availablePublicMethods as $methodDescriptor){
				$availableMethodName = $methodDescriptor->name;
				// Skips the PHP magic methods, like __construct.
				if(isHiddenMethod($availableMethodName)){
					continue;	
				}
				$message .= "\n<li><a href='?serviceName=$this->serviceName&methodName=$availableMethodName'>$availableMethodName</a></li>";
			}
			$message .= "\n</ul>";
		}
		// Prints the service methods paramters input.
		if($this->methodName){
			$serviceObject = $this->serviceRouter->getServiceObject($this->serviceName);
			$reflectionObj = new ReflectionObject($serviceObject);
			$method = $reflectionObj->getMethod($this->methodName);
			$parameterDescriptors = $method->getParameters();
			if (count($parameterDescriptors) > 0) {
				$message .= "<p><strong>Fill in the parameters below then click to call the <code>$this->methodName()</code> method on <code>$this->serviceName</code> service";
				$message .= "\n<br>Use <code>JSON</code> notation for complex values.</strong>";
				$message .= "\n<form action='?serviceName=$this->serviceName&methodName=$this->methodName' method='POST'>\n<table>";
				foreach ($parameterDescriptors as $parameterDescriptor) {
					$availableParameterName = $parameterDescriptor->name;
					$message .= "\n	 <tr><td>$availableParameterName</td><td><input name='$availableParameterName' ";
					if($this->parametersAssoc){
					   $message .= "value='" . $this->parametersAssoc[$availableParameterName] . "'";
					}
					$message .= "></td></tr>";
				}
				$message .= "\n</table>\n<input type='submit' value='call'></form>";
			} else {
				$message .= "<h3>This method has no parameters. Click to call it.</h3>";
				$message .= "\n<form action='?serviceName=$this->serviceName&methodName=$this->methodName&noParams' method='POST'>\n";
				$message .= "\n<input type='submit' value='call'></form>";
			}
		
		}
		// Prints the rpc result as JSON, which is more readable than the print_r implementation, aslo modified by raweden.
		if($this->showResult){
			$message .= "<h3>Result</h3><strong><pre>";
			$result = json_encode($data);
			$message .= pretty_json($result);
		}
		$message .= "</pre></strong></body></html>";		
		return $message; 
	}

	
	/**
	 * Filter the headers to make sure the content type is set to text/html if the request was handled by the service browser
	 * 
	 * @param array $headers
	 * @return array
	 */
	public function filterHeaders($headers, $contentType){
		if (!$contentType || $contentType == self::CONTENT_TYPE) {
			$headers["Content-Type"] = "text/html";
			return $headers;
		}
	}
			
}

/**
 * Determine whether a method name should be hidden by default.
 * 
 * @return A Boolean value determine whether the method name is by default hidden.
 * @author Raweden.
 */
function isHiddenMethod($methodName){
	return $methodName == "__construct" || $methodName == "__destruct";
}

/**
 * Pretty formating a JSON object string.
 * 
 * @param string $json Json string.
 * @author Raweden.
 */
function pretty_json($json){ 
		$tab = "  "; 
		$pretty = ""; 
		$indent_level = 0; 
		$in_string = false; 
		
		// replaces json escaped charaters.
		$json = str_replace("\\/","/",$json);
		
		$len = strlen($json); 
	
		for($c = 0; $c < $len; $c++){ 
			$char = $json[$c]; 
			switch($char) { 
				case '{': 
				case '[': 
					if(!$in_string){ 
						$pretty .= $char . "\n" . str_repeat($tab, $indent_level+1); 
						$indent_level++; 
					}else{ 
						$pretty .= $char; 
					} 
					break; 
				case '}': 
				case ']': 
					if(!$in_string){ 
						$indent_level--; 
						$pretty .= "\n" . str_repeat($tab, $indent_level) . $char; 
					}else{ 
						$pretty .= $char; 
					} 
					break; 
				case ',': 
					if(!$in_string){ 
						$pretty .= ",\n" . str_repeat($tab, $indent_level); 
					}else{ 
						$pretty .= $char; 
					} 
					break; 
				case ':': 
					if(!$in_string){ 
						$pretty .= ":"; 
					}else{ 
						$pretty .= $char; 
					} 
					break; 
				case '"': 
					if($c > 0 && $json[$c-1] != '\\'){ 
						$in_string = !$in_string; 
					} 
				default: 
					$pretty .= $char; 
					break;					
			} 
		}
		// returns the pretty printed JSON string.
		return $pretty; 
	} 

?>
