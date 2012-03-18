<?php
/**
 * 
 */
class ArgumentError extends Exception{
	
	public function __constructor($text,$code){
		parent::__constructor($text,$code);
	}	
}

/**
 * 
 */
class SecurityError extends Exception{
	
	public function __constructor($text,$code){
		parent::__constructor($text,$code);
	}
	
}

class IOError extends Exception{
	
	public function __constructor($text,$code){
		parent::__constructor($text,$code);
	}
	
}

?>