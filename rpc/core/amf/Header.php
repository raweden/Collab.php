<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 */

/**
 * Header is a data type that represents a single header passed via Amf
 */
class AMFHeader{
	
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
	 * data is the actual object data of the header key
	 *
	 * @var mixed
	 */
	public $data;

	/**
	 * AMFHeader is the Constructor function for the AMFHeader data type.
	 */
	public function __construct($name = "", $required = false, $data = null)
	{
		$this->name = $name;
		$this->required = $required;
		$this->data = $data;
	}
}

?>