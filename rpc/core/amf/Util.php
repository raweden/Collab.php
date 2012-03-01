<?php
/**
 * Utils for Amf handling
 * 
 * @author Ariel Sommeria-klein
 */
class AmfUtil{

	/**
	 * Looks if the system is Big Endain or not
	 * @return <Boolean>
	 */
	static public function isSystemBigEndian() {
		$tmp = pack("d", 1); // determine the multi-byte ordering of this machine temporarily pack 1
		return ($tmp == "\0\0\0\0\0\0\360\77");
	}

	/**
	 * Applies a function to all objects contained by $obj and $obj itself.
	 * iterates on $obj and its sub objects, which can iether be arrays or objects
	 * 
	 * @param mixed $obj the object/array that will be iterated on
	 * @param array $callBack the function to apply to obj and subobjs. must take 1 parameter, and return the modified object
	 * @param int $recursionDepth current recursion depth. The first call should be made with this set 0. default is 0
	 * @param int $maxRecursionDepth. default is 30
	 * @return mixed array or object, depending on type of $obj
	 */
	static public function applyFunctionToContainedObjects($obj, $callBack, $recursionDepth = 0, $maxRecursionDepth = 30) {
		if ($recursionDepth == $maxRecursionDepth) {
			throw new RemotingException("couldn't recurse deeper on object. Probably a looped reference");
		}
		//apply callBack to obj itself
		$obj = call_user_func($callBack, $obj);
		if (!is_array($obj) && !is_object($obj)) {
			return $obj;
		}
		foreach ($obj as $key => $data) { // loop over each element
			$modifiedData = null;
			if (is_object($data) || is_array($data)) {
				//data is complex, so don't apply callback directly, but recurse on it
				$modifiedData = self::applyFunctionToContainedObjects($data, $callBack, $recursionDepth + 1, $maxRecursionDepth);
			} else {
				//data is simple, so apply data
				$modifiedData = call_user_func($callBack, $data);
			}
			//store converted data
			if (is_array($obj)) {
				$obj[$key] = $modifiedData;
			} else {
				$obj->$key = $modifiedData;
			}
		}

		return $obj;
	}

	/**
	 * Determines whether an object is the ActionScript type "undefined"
	 *
	 * @static
	 * @param  $obj
	 * @return bool
	 */
	static public function is_undefined($obj) {
		return is_object($obj) ? get_class($obj) == "Undefined" : false;
	}

	/**
	 * Determines whether an object is the ActionScript type "ByteArray"
	 *
	 * @static
	 * @param  $obj
	 * @return bool
	 */
	static public function is_byteArray($obj) {
		return is_object($obj) ? get_class($obj) == "ByteArray" : false;
	}

	/**
	 * Determines whether an object is the ActionScript type "Date"
	 *
	 * @static
	 * @param  $obj
	 * @return bool
	 */
	static public function is_date($obj) {
		return is_object($obj) ? get_class($obj) == "Date" : false;
	}

	/**
	 * Determines whether an object is the ActionScript type "XML"
	 *
	 * @static
	 * @param  $obj
	 * @return bool
	 */
	static public function is_Xml($obj) {
		return is_object($obj) ? get_class($obj) == "XML" : false;
	}
	
	/**
	 * Determines whether an object is the ActionScript type "XmlDoument"
	 *
	 * @static
	 * @param  $obj
	 * @return bool
	 */
	static public function is_XmlDocument($obj) {
		return is_object($obj) ? get_class($obj) == "XMLDocument" : false;
	}


}

?>