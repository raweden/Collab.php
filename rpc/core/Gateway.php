<?php

/**
 * where everything comes together in amfphp.
 * The class used for the entry point of a remoting call
 *
 * @package Amfphp_Core
 * @author Ariel Sommeria-klein
 */
class Gateway{

    /**
     * filter called when the serialized request comes in.
     * @todo this filter only allows manipulation of raw post data, and is as such a bit misleading. Maybe rename and do filters for GET and POST
     * @param String $rawData the raw http data
     */
    const FILTER_SERIALIZED_REQUEST = "FILTER_SERIALIZED_REQUEST";

    /**
     * filter called to allow a plugin to override the default amf deserializer.
     * Plugin should return a IDeserializer if it recognizes the content type
     * @param IDeserializer $deserializer the deserializer. null at call in gateway.
     * @param String $contentType
     */
    const FILTER_DESERIALIZER = "FILTER_DESERIALIZER";
    
    /**
     * filter called after the request is deserialized. The callee can modify the data and return it.
     * @param mixed $deserializedRequest
     */
    const FILTER_DESERIALIZED_REQUEST = "FILTER_DESERIALIZED_REQUEST";

    /**
     * filter called to allow a plugin to override the default amf deserialized request handler.
     * Plugin should return a IDeserializedRequestHandler if it recognizes the request
     * @param IDeserializedRequestHandler $deserializedRequestHandler null at call in gateway.
     * @param String $contentType
     */
    const FILTER_DESERIALIZED_REQUEST_HANDLER = "FILTER_DESERIALIZED_REQUEST_HANDLER";

    /**
     * filter called when the response is ready but not yet serialized.  The callee can modify the data and return it.
     * @param $deserializedResponse
     */
    const FILTER_DESERIALIZED_RESPONSE = "FILTER_DESERIALIZED_RESPONSE";

    /**
     * filter called to allow a plugin to override the default amf exception handler.
     * If the plugin takes over the handling of the request message,
     * it must set this to a proper IExceptionHandler
     * @param IExceptionHandler $exceptionHandler. null at call in gateway.
     * @param String $contentType
     */
    const FILTER_EXCEPTION_HANDLER = "FILTER_EXCEPTION_HANDLER";

    /**
     * filter called to allow a plugin to override the default amf serializer.
     * @param ISerializer $serializer the serializer. null at call in gateway.
     * @param String $contentType
     * Plugin sets to a ISerializer if it recognizes the content type
     */
    const FILTER_SERIALIZER = "FILTER_SERIALIZER";

    /**
     * filter called when the packet response is ready and serialized.
     * @param String $rawData the raw http data
     */
    const FILTER_SERIALIZED_RESPONSE = "FILTER_SERIALIZED_RESPONSE";

    /**
     * filter called to get the headers
     * @param array $headers an associative array of headers. For example array("Content-Type" => "application/x-amf")
     * @param String $contentType
     */
    const FILTER_HEADERS = "FILTER_HEADERS";


    /**
     * config.
     * @var Amfphp_Core_Config
     */
    private $config;

    /**
     * typically the $_GET array.
     * @var array
     */
    private $getData;

    /**
     * typically the $_POST array.
     * @var array
     */
    private $postData;

    /**
     * the content type. For example for amf, application/x-amf
     * @var String
     */
    private $contentType;

    /**
     * the serialized request 
     * @var String 
     */
    private $rawInputData;

    /**
     * the serialized response
     * @var String
     */
    private $rawOutputData;

    /**
     * constructor
     * @param array $getData typically the $_GET array.
     * @param array $postData typically the $_POST array.
     * @param String $rawInputData
     * @param String $contentType
     * @param Amfphp_Core_Config $config optional. The default config object will be used if null
     */
    public function  __construct(array $getData, array $postData, $rawInputData, $contentType, Amfphp_Core_Config $config = null) {
        $this->getData = $getData;
        $this->postData = $postData;
        $this->rawInputData = $rawInputData;
        $this->contentType = $contentType;
		// TODO: better implementation of directing content-type to services.
		define("CONTENT_TYPE",$contentType);

        if($config){
            $this->config = $config;
        }else{
            $this->config = new GatewayConfig();
        }

    }
    
    /**
     * The service method runs the gateway application.  It deserializes the raw data passed into the constructor as an AmfPacket, handles the headers,
     * handles the messages as requests to services, and returns the responses from the services
     * It does not however handle output headers, gzip compression, etc. that is the job of the calling script
     *
     * @return <String> the serialized amf packet containg the service responses
     */
    public function service(){
        $filterManager = FilterManager::getInstance();
        $defaultHandler = new AmfHandler();
        $deserializedResponse = null;
        try{
            PluginManager::getInstance()->loadPlugins($this->config->pluginsFolder, $this->config->pluginsConfig, $this->config->disabledPlugins);
            //call filter for filtering serialized incoming packet
            $this->rawInputData = $filterManager->callFilters(self::FILTER_SERIALIZED_REQUEST, $this->rawInputData);

            //call filter to get the deserializer
            $deserializer = $filterManager->callFilters(self::FILTER_DESERIALIZER, $defaultHandler, $this->contentType);
            
            //deserialize
            $deserializedRequest = $deserializer->deserialize($this->getData, $this->postData, $this->rawInputData);

            //call filter for filtering deserialized request
            $deserializedRequest = $filterManager->callFilters(self::FILTER_DESERIALIZED_REQUEST, $deserializedRequest);

            //create service router
            $serviceRouter = new ServiceRouter($this->config->serviceFolderPaths, $this->config->serviceNames2ClassFindInfo);

            //call filter to get the deserialized request handler
            $deserializedRequestHandler = $filterManager->callFilters(self::FILTER_DESERIALIZED_REQUEST_HANDLER, $defaultHandler, $this->contentType);

            //handle request
            $deserializedResponse = $deserializedRequestHandler->handleDeserializedRequest($deserializedRequest, $serviceRouter);

            //call filter for filtering the deserialized response
            $deserializedResponse = $filterManager->callFilters(self::FILTER_DESERIALIZED_RESPONSE, $deserializedResponse);

        }catch(Exception $exception){
            //call filter to get the exception handler
            $exceptionHandler = $filterManager->callFilters(self::FILTER_EXCEPTION_HANDLER, $defaultHandler, $this->contentType);

            //handle exception
            $deserializedResponse = $exceptionHandler->handleException($exception);

        }

        //call filter to get the serializer
        $serializer = $filterManager->callFilters(self::FILTER_SERIALIZER, $defaultHandler, $this->contentType);

        //serialize
        $this->rawOutputData = $serializer->serialize($deserializedResponse);

        //call filter for filtering the serialized response packet
        $this->rawOutputData = $filterManager->callFilters(self::FILTER_SERIALIZED_RESPONSE, $this->rawOutputData);

        return $this->rawOutputData;

    }

    /**
     * get the response headers. Creates an associative array of headers, then filters them, then returns an array of strings
     * @return array
     */
    public function getResponseHeaders(){        
        $filterManager = FilterManager::getInstance();
        $headers = array("Content-Type" => $this->contentType);
        $headers = $filterManager->callFilters(self::FILTER_HEADERS, $headers, $this->contentType);
        $ret = array();
        foreach($headers as $key => $value){
            $ret[] = $key . ": " . $value;
        }
        return $ret;
    }

    /**
     * helper function for sending gateway data to output stream
     */
    public function output(){
        $responseHeaders = $this->getResponseHeaders();
        foreach($responseHeaders as $header){
            header($header);
        }
        echo $this->rawOutputData;
    }

	/**
	 *
	 */
	public function getContentType(){
		return $this->contentType;
	}

}
?>