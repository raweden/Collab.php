<?php

/**
 * constants relative to the Amf format
 * 
 * @author Ariel Sommeria-klein
 * 
 */
class AmfConstants {
    
    /**
     * The success method name
     */
    const CLIENT_SUCCESS_METHOD = "/onResult";
    
    /**
     * The status method name
     */
    const CLIENT_FAILURE_METHOD = "/onStatus";

    /**
     * Used when there is an error and the request response uri is not available
     */
    const DEFAULT_REQUEST_RESPONSE_URI = "/1";
    
    /**
     * The AMf content type, for use in the headers
     */
    const CONTENT_TYPE = "application/x-amf";
    
    /**
     * This is the field where the class name of an object must be set so that it can be sent as a strongly typed object
     * try to use this where possible, but it can't be everywhere because we would need to use ReflectionClass::hasProperty, and that is only with PHP 5.1
     */
    const FIELD_EXPLICIT_TYPE = "_explicitType";

    /**
     * This is the name of the credentials header. can be used for AS3, but is mostly AS2 only
     */
    const CREDENTIALS_HEADER_NAME = "Credentials";

    /**
     * The user id field in the credentials header
     */
    const CREDENTIALS_FIELD_USERID = "userid";

    /**
     * The password field in the credentials header
     */
    const CREDENTIALS_FIELD_PASSWORD = "password";

    /**
     * amf0 encoding
     */
    const AMF0_ENCODING = 0;

    /**
     * amf3 encoding
     */
    const AMF3_ENCODING = 3;


}
?>
