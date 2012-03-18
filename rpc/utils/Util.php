<?php
/**
 * Description
 *
 * Copyright 2012, Raweden. All Rights Reserved.
 *
 * @author Raweden
 * @created 
 */
class Util{
	
	private static $contentType = null;
	
	private static function retriveContentType(){
		if(self::$contentType == null){
			// getting the content type of the request.
			$headers = getallheaders();

			// getting the content-type.
			if(isset($headers["Content-Type"])){
				$contentType = $headers["Content-Type"];
				self::$contentType = trim($contentType);	
			}else{
				self::$contentType = false;	
			}
		}
	}
	
	public static function isAmfRequest(){
		self::retriveContentType();
		return self::$contentType == "application/x-amf";
	}

	public static function isJsonRequest(){
		self::retriveContentType();
		return self::$contentType == "application/json";
	}
	
	public static function isXmlRequest(){
		self::retriveContentType();
		return self::$contentType == "text/xml";
	}
}
?>