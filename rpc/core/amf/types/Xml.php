<?php

/**
 * XML (not the AS3/AMF3 XMLDocument) will be converted to and from this class.
 * PHP has many libs to manipulate XML, and it is not up to amfPHP to choose which one to use. Furthermore, AS3 has 2 XML types, XML and and XMLDocument.
 * So amfPHP just wraps the string data in these types, and iut is up to the user to parse the contained string data
 * 
 * @author Ariel Sommeria-klein
 */
class XML{
	
	public $data;

	public function XML($data){
		$this->data = $data;
	}
	
}
?>
