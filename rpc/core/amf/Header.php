<?php

/**
 * Header is a data type that represents a single header passed via Amf
 */
class AmfHeader{
	
	/**
	 * Name is the string name of the header key
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Required is a boolean determining whether the remote system
	 * must understand this header in order to operate.  If the system
	 * does not understand the header then it should not execute the
	 * method call.
	 *
	 * @var boolean
	 */
	public $required;

	/**
	 * Data is the actual object data of the header key
	 *
	 * @var mixed
	 */
	public $data;

	/**
	 * AmfHeader is the Constructor function for the AmfHeader data type.
	 */
	public function __construct($name = "", $required = false, $data = null){
		$this->name = $name;
		$this->required = $required;
		$this->data = $data;
	}
}

?>