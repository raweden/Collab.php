<?php
/**
 * content holder for an Amf Packet.
 * 
 * @author Ariel Sommeria-klein
 */
class AmfPacket {
	/**
	 * The place to keep the headers data
	 *
	 * @var <array>
	 */
	public $headers;

	/**
	 * The place to keep the Message elements
	 *
	 * @var <array>
	 */
	public $messages;

	/**
	 * Determines the amf format version, value is either 0 or 3. 
	 * This is stored here when deserializing, because the serializer needs it.
	 * 
	 * @var <int>
	 */
	public $amfVersion;


	/**
	 * The constructor function for a new Amf object.
	 *
	 * All the constructor does is initialize the headers and Messages containers
	 */
	public function __construct() {
		$this->headers = array();
		$this->messages = array();
		$this->headerTable = array();
		$this->amfVersion = AMFConstants::AMF0_ENCODING;
	}

	

}
?>
