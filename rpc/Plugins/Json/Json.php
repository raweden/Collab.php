<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 * @package Amfphp_Plugins_Json
 */

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
	
    /**
     * constructor. Add filters on the HookManager.
     * @param array $config optional key/value pairs in an associative array. Used to override default configuration values.
     */
    public function  __construct(array $config = null) {
        $filterManager = FilterManager::getInstance();
        $filterManager->addFilter(Gateway::FILTER_DESERIALIZER, $this, "filterHandler");
        $filterManager->addFilter(Gateway::FILTER_EXCEPTION_HANDLER, $this, "filterHandler");
        $filterManager->addFilter(Gateway::FILTER_SERIALIZER, $this, "filterHandler");
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

    /**
     * Retrieve the serviceName, methodName and parameters from the PHP object
     * representing the JSON string
     * @see IDeserializedRequestHandler
     * @return the service call response
     */
    public function handleDeserializedRequest($deserializedRequest, ServiceRouter $serviceRouter){
		
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
        $parameters = array();
        if(isset ($deserializedRequest->parameters)){
            $parameters = $deserializedRequest->parameters;
        }
        return $serviceRouter->executeServiceCall($serviceName, $methodName, $parameters);
        
    }

    /**
     * @see IExceptionHandler
     */
    public function handleException(Exception $exception){
        return str_replace("\n", "<br>", $exception->__toString());
        
    }
    
    /**
     * Encode the PHP object returned from the service call into a JSON string
     * @see ISerializer
     * @return the encoded JSON string sent to JavaScript
     */
    public function serialize($data){
        return json_encode($data);

    }


}
?>
