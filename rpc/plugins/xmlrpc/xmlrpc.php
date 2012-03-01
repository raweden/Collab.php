<?php
/**
 * 
 * @author Raweden.
 */
class xmlrpc implements IDeserializer, IDeserializedRequestHandler, IExceptionHandler, ISerializer{
	
	const XML_CONTENT_TYPE = "text/xml";
	
	public function __construct(array $config = null){
		$filterManager = FilterManager::getInstance();
		$filterManager->addFilter(Gateway::FILTER_DESERIALIZER, $this, "filterHandler");
		$filterManager->addFilter(Gateway::FILTER_EXCEPTION_HANDLER, $this, "filterHandler");
        $filterManager->addFilter(Gateway::FILTER_SERIALIZER, $this, "filterHandler");
	}
	
	/**
     * If the content type contains the "text/xml" string, return this plugin.
	 * 
     * @param mixed null at call in gateway.
     * @param String $contentType
     * @return this or null
     */
    public function filterHandler($handler, $contentType){
        if(strpos($contentType, self::XML_CONTENT_TYPE) !== false)
            return $this;
		return $handler;
    }
	
	/**
	 * Deserializes the XML-RPC request.
	 * 
	 * @see IDeserializer
	 */
	public function deserialize(array $getData, array $postData, $rawPostData){
		echo($getData);
		/*
		 * @param array $getData typically the $_GET array. 
		 * @param array $postData typically the $_POST array.
		 * @param String $rawPostData
		 * @return mixed the deserialized data. For example an Amf packet.
		*/
	}
	
	/**
     * Retrieve the serviceName, methodName and parameters from the PHP object representing the XML-RPC request.
	 * 
     * @see IDeserializedRequestHandler
     * @return the service call response
	 */
	public function handleDeserializedRequest($deserializedRequest, ServiceRouter $serviceRouter){
		
	}
	
	/**
	 * Generates an Object that encapsulates the information about the exception.
	 * 
	 * @param Exception $exception the exception object to analyze
	 * @return mixed an object describing the error, that will be serialized and sent back to the client
	 */
	public function handleException(Exception $exception){
		
	}
	
	/**
	 * Serializes the data retrived from the service call into valid XMP-RPC response.
	 * 
	 * @param mixed $data the data to serialize.
	 * @return A XML formated string.
	 */
	public function serialize($data){
		
	}
	
}

/* Orginal amfphp 1.8 xml-rpc


function deserializationAction(&$body)
{
	$data = $body->getValue();
	
	//Get the method that is being called
	$description = xmlrpc_parse_method_descriptions($data);
	$target = $description['methodName'];
	
	$baseClassPath = $GLOBALS['amfphp']['classPath'];
	
	$lpos = strrpos($target, '.');
	
	$methodname = substr($target, $lpos + 1);
	$trunced = substr($target, 0, $lpos);
	$lpos = strrpos($trunced, ".");
	if ($lpos === false) {
		$classname = $trunced;
		$uriclasspath = $trunced . ".php";
		$classpath = $baseClassPath . $trunced . ".php";
	} else {
		$classname = substr($trunced, $lpos + 1);
		$classpath = $baseClassPath . str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
		$uriclasspath = str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
	} 
	
	$body->methodName = $methodname;
	$body->className = $classname;
	$body->classPath = $classpath;
	$body->uriClassPath = $uriclasspath;
	$body->packageClassMethodName = $description['methodName'];
}

function executionAction(& $body)
{
	$classConstruct = $body->getClassConstruct();
	$methodName = $body->methodName;
	$className = $body->className;
	
	$xmlrpc_server = xmlrpc_server_create();
	
	$lambdaFunc = 'return adapterMap(call_user_func_array (array(&$userData[0], $userData[1]), $args));';
	$func = create_function('$a,$args,$userData', $lambdaFunc);

	xmlrpc_server_register_method($xmlrpc_server,
		$body->packageClassMethodName,
		$func);
	
	$request_xml = $body->getValue();
	$args = array($xmlrpc_server, $request_xml, array(&$classConstruct, $methodName));
	$nullObj = NULL;
	$response = Executive::doMethodCall($body, $nullObj, 'xmlrpc_server_call_method', $args);
	//$response = xmlrpc_server_call_method();
	
	if($response !== "__amfphp_error")
	{
		$body->setResults($response);
	}
	else
	{
		return false;
	}
}


Debug action
function debugAction(& $body)
{
	if(count(NetDebug::getTraceStack()) != 0)
	{
		$previousResults = $body->getResults();
		$debugInfo = NetDebug::getTraceStack();
		$debugString = "<!-- " . implode("\n", $debugInfo) . "-->";
		$body->setResults($debugString . "\n" . $previousResults);
	}
}

This won't ever be called unless there is an error
function serializationAction(& $body)
{
	$request_xml = $body->getValue();
	$toSerialize = $body->getResults();
	
	$lambdaFunc = 'return $userData;';
	$func = create_function('$a,$b,$userData', $lambdaFunc);
	
	$xmlrpc_server = xmlrpc_server_create();
	
	$request_xml = $body->getValue();
	
	xmlrpc_server_register_method($xmlrpc_server,
		$body->packageClassMethodName,
		$func);

	$response = xmlrpc_server_call_method($xmlrpc_server, $request_xml, $toSerialize);
	
	$body->setResults($response);
}
*/
?>