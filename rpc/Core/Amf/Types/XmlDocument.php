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
 * AS3 XMLDocument type. 
 * @see Amfphp_Core_Amf_Types_Xml
 *
 * @package Amfphp_Core_Amf_Types
 * @author Ariel Sommeria-klein
 * 
 * TODO: Give This class a more uniform name (XMLDocument)
 */

class XMLDocument
{
	public $data;

	public function XMLDocument($data)
	{
		$this->data = $data;
	}
}
?>
