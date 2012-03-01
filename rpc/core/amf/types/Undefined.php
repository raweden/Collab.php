<?php
/**
 * Amf Undefined will be converted to and from this class
 * 
 * @author Ariel Sommeria-klein
 */
class Undefined {

	public function exists(){
		return false;
	}

	public function __toString(){
		return 'undefined';
	}
}
?>
