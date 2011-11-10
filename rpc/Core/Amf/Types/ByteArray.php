<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 * @package Amfphp_Core_Amf
 */

/**
 * Amf byte arrays will be converted to and from this class
 *
 * @package Amfphp_Core_Amf_Types
 * @author Ariel Sommeria-klein
 * 
  * TODO: Give This class a more uniform name (ByteArray)
 */

class ByteArray
{
	public $data;

	public function ByteArray($data)
	{
		$this->data = $data;
	}
}
?>
