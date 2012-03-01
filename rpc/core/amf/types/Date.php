<?php
/**
 * Amf dates will be converted to and from this class. The PHP DateTime class is for PHP >= 5.2.0, and setTimestamp for PHP >= 5.3.0, so it can't be used in amfPHP
 * Of course feel free to use it yourself if your host supports it.
 * 
 * @author Danny Kopping
 */
class Date{
	
	/**
	 * Unix-timestamp, number of ms since 1st Jan 1970.
	 * 
	 * @var integer
	 */
	public $timeStamp;

	public function Date($timeStamp){
		$this->timeStamp = $timeStamp;
	}
	
}
?>