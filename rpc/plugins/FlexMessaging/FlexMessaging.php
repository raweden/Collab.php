<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 * @package Amfphp_Plugins_FlexMessaging
 */

/**
*  includes
*  */
require_once dirname(__FILE__) . "/AcknowledgeMessage.php";
require_once dirname(__FILE__) . "/ErrorMessage.php";
/**
 * Support for flex messaging.
 * Flex doesn't use the basic packet system. When using a remote objct, first a CommandMessage is sent, expecting an AcknowledgeMessage in return.
 * Then a RemotingMessage is sent, expecting an AcknowledgeMessage in return.
 * In case of an error, an ErrorMessage is expected
 *
 * @package Amfphp_Plugins_FlexMessaging
 * @author Ariel Sommeria-Klein
 */
class FlexMessaging{
    const FLEX_TYPE_COMMAND_MESSAGE = 'flex.messaging.messages.CommandMessage';
    const FLEX_TYPE_REMOTING_MESSAGE = 'flex.messaging.messages.RemotingMessage';
    const FLEX_TYPE_ACKNOWLEDGE_MESSAGE = 'flex.messaging.messages.AcknowledgeMessage';
    const FLEX_TYPE_ERROR_MESSAGE = 'flex.messaging.messages.ErrorMessage';
    
    const FIELD_MESSAGE_ID = "messageId";

    /**
     * if this is set, special error handling applies
     * @var Boolean
     */
    private $clientUsesFlexMessaging;

    /**
     * the messageId of the last flex message. Used for error generation
     * @var String
     */
    private $lastFlexMessageId;

    /**
     * the response uri of the last flex message. Used for error generation
     * @var String
     */
    private $lastFlexMessageResponseUri;

    /**
     * constructor.
     * @param array $config optional key/value pairs in an associative array. Used to override default configuration values.
     */
    public function  __construct(array $config = null) {
        FilterManager::getInstance()->addFilter(AmfHandler::FILTER_AMF_REQUEST_MESSAGE_HANDLER, $this, "filterAmfRequestMessageHandler");
        FilterManager::getInstance()->addFilter(AmfHandler::FILTER_AMF_EXCEPTION_HANDLER, $this, "filterAmfExceptionHandler");
        $this->clientUsesFlexMessaging = false;
    }

    /**
     *
     * @param Object $handler. null at call. If the plugin takes over the handling of the request message,
     * it must set this to a proper handler for the message, probably itself.
     * @param AmfMessage $requestMessage the request message
     * @return array
     */
    public function filterAmfRequestMessageHandler($handler, AmfMessage $requestMessage){

        //for test purposes
        //throw new RemotingException(print_r($requestMessage->data[0], true));
        if($requestMessage->data == null){
            //all flex messages have data
            return;
        }

        $explicitTypeField = AMFConstants::FIELD_EXPLICIT_TYPE;

        if(!isset ($requestMessage->data[0]) || !isset ($requestMessage->data[0]->$explicitTypeField)){
            //and all flex messages have data containing one object with an explicit type
            return;
        }

        $messageType = $requestMessage->data[0]->$explicitTypeField;
        if($messageType == self::FLEX_TYPE_COMMAND_MESSAGE || $messageType == self::FLEX_TYPE_REMOTING_MESSAGE){
            //recognized message type! This plugin will handle it
            $this->clientUsesFlexMessaging = true;
            return $this;
        }
    }

    /**
     *
     * @param Object $handler. null at call. If the plugin takes over the handling of the request message,
     * it must set this to a proper handler for the message, probably itself.
     * @return array
     */
    public function filterAmfExceptionHandler($handler){
        if($this->clientUsesFlexMessaging){
            return $this;
        }
    }

    /**
     * handle the request message instead of letting the Amf Handler do it.
     * @param AmfMessage $requestMessage
     * @param Amfphp_Core_Common_ServiceRouter $serviceRouter
     * @return AmfMessage
     */
    public function handleRequestMessage(AmfMessage $requestMessage, Amfphp_Core_Common_ServiceRouter $serviceRouter){
        $explicitTypeField = AMFConstants::FIELD_EXPLICIT_TYPE;
        $messageType = $requestMessage->data[0]->$explicitTypeField;
        $messageIdField = self::FIELD_MESSAGE_ID;
        $this->lastFlexMessageId = $requestMessage->data[0]->$messageIdField;
        $this->lastFlexMessageResponseUri = $requestMessage->responseUri;


        if($messageType == self::FLEX_TYPE_COMMAND_MESSAGE){
            //command message. An empty AcknowledgeMessage is expected.
            $acknowledge = new AcknowledgeMessage($requestMessage->data[0]->$messageIdField);
            return new AmfMessage($requestMessage->responseUri . AMFConstants::CLIENT_SUCCESS_METHOD, null, $acknowledge);

        }


        if($messageType == self::FLEX_TYPE_REMOTING_MESSAGE){
            //remoting message. An AcknowledgeMessage with the result of the service call is expected.
            $remoting = $requestMessage->data[0];
            $serviceCallResult = $serviceRouter->executeServiceCall($remoting->source, $remoting->operation, $remoting->body);
            $acknowledge = new AcknowledgeMessage($remoting->$messageIdField);
            $acknowledge->body = $serviceCallResult;
            return new AmfMessage($requestMessage->responseUri . AMFConstants::CLIENT_SUCCESS_METHOD, null, $acknowledge);

        }
        throw new RemotingException("unrecognized flex message");
    }

    /**
     * flex expects error messages formatted in a special way, using the ErrorMessage object.
     * @return AmfPacket
     * @param Exception $exception
     */
    public function generateErrorResponse(Exception $exception){
        $error = new ErrorMessage($this->lastFlexMessageId);
        $error->faultCode = $exception->getCode();
        $error->faultString = $exception->getMessage();
        $error->faultDetail = $exception->getTraceAsString();
        $errorMessage = new AmfMessage($this->lastFlexMessageResponseUri . AMFConstants::CLIENT_FAILURE_METHOD, null, $error);
        $errorPacket = new AmfPacket();
        $errorPacket->messages[] = $errorMessage;
        return $errorPacket;
    }
}

?>
