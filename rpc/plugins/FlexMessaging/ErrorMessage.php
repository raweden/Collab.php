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
 * Used to generate a Flex Error message.
 * part of the FlexMessaging plugin
 *
 * @package Amfphp_Plugins_FlexMessaging
 * @author Ariel Sommeria-Klein
 */



class ErrorMessage
{
	public $_explicitType;
	public $correlationId;
	public $faultCode;
	public $faultDetail;
	public $faultString;

        public function  __construct($correlationId) {
            $explicitTypeField = AmfConstants::FIELD_EXPLICIT_TYPE;
            $this->$explicitTypeField = FlexMessaging::FLEX_TYPE_ERROR_MESSAGE;
	    $this->correlationId = $correlationId;
        }
}
?>
