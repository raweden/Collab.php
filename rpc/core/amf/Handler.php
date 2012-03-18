<?php

/**
 * This is the default handler for the gateway. It's job is to handle everything that is specific to Amf for the gateway.
 * 
 * @author Ariel Sommeria-Klein
 */
class AmfHandler implements IDeserializer, IDeserializedRequestHandler, IExceptionHandler, ISerializer{
	
	
	/**
	 * filter called for each amf request header, to give a plugin the chance to handle it.
	 * Unless a plugin handles them, amf headers are ignored
	 * Headers embedded in the serialized requests are regarded to be a Amf specific, so they get their filter in Amf Handler
	 * 
	 * @param Object $handler. null at call. Return if the plugin can handle
	 * @param AmfHeader $header the request header
	 * 
	 * @todo consider an interface for $handler. Maybe overkill here
	 */
	const FILTER_AMF_REQUEST_HEADER_HANDLER = "FILTER_AMF_REQUEST_HEADER_HANDLER";
	
	/**
	 * filter called for each amf request message, to give a plugin the chance to handle it.
	 * This is for the Flex Messaging plugin to be able to intercept the message and say it wants to handle it
	 * 
	 * @param Object $handler. null at call. Return if the plugin can handle
	 * @param AmfMessage $requestMessage the request message
	 * 
	 * @todo consider an interface for $handler. Maybe overkill here
	 */
	const FILTER_AMF_REQUEST_MESSAGE_HANDLER = "FILTER_AMF_REQUEST_MESSAGE_HANDLER";
	
	/**
	 * filter called for exception handling an Amf packet/message, to give a plugin the chance to handle it.
	 * This is for the Flex Messaging plugin to be able to intercept the exception and say it wants to handle it
	 * 
	 * @param Object $handler. null at call. Return if the plugin can handle
	 * 
	 * @todo consider an interface for $handler. Maybe overkill here
	 */
	const FILTER_AMF_EXCEPTION_HANDLER = "FILTER_AMF_EXCEPTION_HANDLER";
	
	/**
	 * Amf specifies that an error message must be aimed at an end point. This stores the last message's response Uri to be able to give this end point
	 * in case of an exception during the handling of the message. The default is "/1", because a response Uri is not always available
	 * @var String
	 */
	private $lastRequestMessageResponseUri;
	
	private $objectEncoding = AmfConstants::AMF0_ENCODING;
	
	public function  __construct() {
		$this->lastRequestMessageResponseUri = "/1";
	}
	
	/**
	 * @see IDeserializer
	 */
	public function deserialize(array $getData, array $postData, $rawPostData){
		$deserializer = new AmfDeserializer($rawPostData);
		$requestPacket = $deserializer->deserialize();
		
		$this->objectEncoding = $requestPacket->amfVersion;
		
		return $requestPacket;
	}
	
	/**
	 * Creates a ServiceCallParamaeters object from an AmfMessage
	 * supported separators in the targetUri are "/" and "."
	 * 
	 * @param AmfMessage $AmfMessage
	 * 
	 * @return ServiceCallParameters
	 */
	private function getServiceCallParameters(AmfMessage $AmfMessage){
		$targetUri = str_replace(".", "/", $AmfMessage->targetUri);
		$split = explode("/", $targetUri);
		$ret = new ServiceCallParameters();
		$ret->methodName = array_pop($split);
		$ret->serviceName = join($split, "/");
		$ret->methodParameters = $AmfMessage->data;
		return $ret;
	}
	
	/**
	 * Process a request and generate a response.
	 * throws an Exception if anything fails, so caller must encapsulate in try/catch
	 *
	 * @param AmfMessage $requestMessage
	 * 
	 * @return AmfMessage the response Message for the request
	 */
	private function handleRequestMessage(AmfMessage $requestMessage, ServiceRouter $serviceRouter){
		$filterManager = FilterManager::getInstance();
		$fromFilters = $filterManager->callFilters(self::FILTER_AMF_REQUEST_MESSAGE_HANDLER, null, $requestMessage);
		if($fromFilters){
			$handler = $fromFilters;
			return $handler->handleRequestMessage($requestMessage, $serviceRouter);
		}
		
		//plugins didn't do any special handling. Assumes this is a simple rpc call
		$serviceCallParameters = $this->getServiceCallParameters($requestMessage);
		$ret = $serviceRouter->executeServiceCall($serviceCallParameters->serviceName, $serviceCallParameters->methodName, $serviceCallParameters->methodParameters);
		$responseMessage = new AmfMessage();
		$responseMessage->data = $ret;
		$responseMessage->targetUri = $requestMessage->responseUri . AmfConstants::CLIENT_SUCCESS_METHOD;
		//not specified
		$responseMessage->responseUri = "null";
		return $responseMessage;
	}
	
	
	/**
	 * @see IDeserializedRequestHandler
	 */
	public function handleDeserializedRequest($deserializedRequest, ServiceRouter $serviceRouter){
		$numHeaders = count($deserializedRequest->headers);
		for($i = 0; $i < $numHeaders; $i++){
			$requestHeader = $deserializedRequest->headers[$i];
			//handle a header. This is a job for plugins, unless comes a header that is so fundamental that it needs to be handled by the core
			$fromFilters = FilterManager::getInstance()->callFilters(self::FILTER_AMF_REQUEST_HEADER_HANDLER, null, $requestHeader);
			if($fromFilters){
				$handler = $fromFilters;
				$handler->handleRequestHeader($requestHeader);
			}
		}

		$numMessages = count($deserializedRequest->messages);
		$rawOutputData = "";
		$responsePacket = new AmfPacket();
		$responsePacket->amfVersion = $deserializedRequest->amfVersion;
		for($i = 0; $i < $numMessages; $i++){
			$requestMessage = $deserializedRequest->messages[$i];
			$this->lastRequestMessageResponseUri = $requestMessage->responseUri;
			$responseMessage = $this->handleRequestMessage($requestMessage, $serviceRouter);
			$responsePacket->messages[] = $responseMessage;
		}
		return $responsePacket;

	}

	/**
	 * @see IExceptionHandler
	 */
	public function handleException(Exception $exception){
		$errorPacket = new AmfPacket();
		$filterManager = FilterManager::getInstance();
		$fromFilters = $filterManager->callFilters(self::FILTER_AMF_EXCEPTION_HANDLER, null);
		if($fromFilters){
			$handler = $fromFilters;
			return $handler->generateErrorResponse($exception);
		}

		//no special handling by plugins. generate a simple error response with information about the exception
		$errorResponseMessage = null;
		$errorResponseMessage = new AmfMessage();
		$errorResponseMessage->targetUri = $this->lastRequestMessageResponseUri . AmfConstants::CLIENT_FAILURE_METHOD;
		//not specified
		$errorResponseMessage->responseUri = "null";
		$errorResponseMessage->data = new stdClass();
		$errorResponseMessage->data->faultCode = $exception->getCode();
		$errorResponseMessage->data->faultString = $exception->getMessage();
		
		$details = $exception->getTraceAsString();
		// localizes the paths of the stack-trace.
		if(defined(WEB_ROOT)){
			$details = str_replace(WEB_ROOT, "", $details);	
		}
		$errorResponseMessage->data->faultDetail = $details;

		$errorPacket->messages[] = $errorResponseMessage;
		return $errorPacket;
		
	}

	/**
	 * @see ISerializer
	 */
	public function serialize($data){
		$data->amfVersion = $this->objectEncoding;

		$serializer = new AmfSerializer($data);
		return $serializer->serialize();

	}

}
?>
