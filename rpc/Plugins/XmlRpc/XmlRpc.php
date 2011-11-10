<?php
/**
 * 
 * @author Raweden.
 */
class XmlRpc implements IDeserializer, IDeserializedRequestHandler, IExceptionHandler, ISerializer{
	
	const XML_CONTENT_TYPE = "text/xml";
	
	public function  __construct(array $config = null){
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
?>