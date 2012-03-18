<?php
/**
 * A built-in amfphp service that allows introspection into services and their methods.
 * Remove from production servers
 */
class DocGen{
	
	/**
	 * Get the list of services
	 * @returns An array of array ready to be bound to a Tree
	 */
	public function getServices(){
		
		$services = self::listServices(AMFPHP_ROOTPATH . "utils/");
		
		return $services;
	}
	
	/**
	 * Describe a service and all its methods.
	 * 
	 * @param $data An object containing 'label' and 'data' keys
	 */
	public function describeService($className){
		
		$methodTable = MethodTable::create(AMFPHP_ROOTPATH . "services/" . $className . '.php', NULL, $classComment);
		return array($methodTable, $classComment);
	}
	
	/**
	 * Returns a mapped object where all commands are documented.
	 */
	public function commands(){
		
		$dir = AMFPHP_ROOTPATH . "services/";
		
		$services = self::listServices($dir);
		$commnads = array();
		
		foreach($services as $bundle){
			
			$methodTable = MethodTable::create($dir . $bundle . '.php', NULL, $classComment);
			
			foreach($methodTable as $name => $command){
				$name = $bundle . "." . $name;
				$commnads[$name] = $command;	
			}
		}
		
		return $commnads;
	}
	
	/**
	 * Utility method that returns the name of all php files in a directory.
	 */
	private static function listServices($directory = ""){
		
		$services = array();
		
		if ($handle = opendir($directory)){
			
			chdir(dirname(__FILE__));
			
			while (false !== ($file = readdir($handle))){
				if($file == "." || $file == ".."){
					continue;
				}
				
				if(is_file($directory . $file)){
					
					$index = strrpos($file, '.');
					$name = substr($file, 0, $index);
					$type = substr($file, $index + 1);
						
					if($type == "php"){
						array_push($services,$name);
					}
				}
			}
		}
		closedir($handle);
		return $services;
	}

}

function strrstr($haystack, $needle){
	return substr($haystack, 0, strpos($haystack.$needle,$needle));
}

function strstrafter($haystack, $needle){
	return substr(strstr($haystack, $needle), strlen($needle));
}

class MethodTable{
	
	/**
	 * Constructor.
	 *
	 * Since this class should only be accessed through the static create() method
	 * this constructor should be made private. Unfortunately, this is not possible
	 * in PHP4.
	 */
	public function __construct(){
		
	}


	/**
	 * Creates the methodTable for a passed class.
	 * 
	 * @param $className {String} The name of the service class. (may also simply be __FILE__)
	 * @param $servicePath {String} The location of the classes (optional)
	 */
	public static function create($className, $servicePath = NULL, &$classComment){

		$methodTable = array();
		if(file_exists($className)){
			
			$sourcePath = $className;
			$className = str_replace("\\", '/', $className);
			$className = substr($className, strrpos($className, '/') + 1);
			$className = str_replace('.php', '', $className);
		}else{
			
			$className = str_replace('.php', '', $className);
			$fullPath = str_replace('.', '/', $className);
			$className = $fullPath;
			
			if(strpos($fullPath, '/') !== FALSE){
				$className = substr(strrchr($fullPath, '/'), 1);
			}
			// FIX: what does the code bellow?
			if($servicePath == NULL){
				if(isset($GLOBALS['amfphp']['classPath'])){
					$servicePath = $GLOBALS['amfphp']['classPath'];
				}else{
					$servicePath = "../services/";
				}
			}
			$sourcePath = $servicePath . $fullPath . ".php";
		}

		if(!file_exists($sourcePath)){
			trigger_error("The MethodTable class could not find {" . 
				$sourcePath . "}", 
				E_USER_ERROR);
		}
		
		if(!class_exists('ReflectionClass')){
			return null;
		}
		
		$classMethods = MethodTable::getClassMethodsReflection($sourcePath, $className, $classComment);

		foreach ($classMethods as $key => $value){
			if($value["name"][0] == "_" || $value["name"] == "beforeFilter"){
				continue;
			}
			$params = $value['args'];
			$methodName = $value['name'];
			$methodComment = $value['comment'];
			$methodLength = $value['lenght'];

			$description = MethodTable::getMethodDescription($methodComment) . " " . MethodTable::getMethodCommentAttribute($methodComment, "desc");
			$description = trim($description);
			$access = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "access");
			$roles = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "roles");
			$instance = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "instance");
			$returns = MethodTable::getMethodCommentAttributeFirstLine($methodComment, "return");
			$paramsComment = MethodTable::getMethodCommentArguments($methodComment);

			$methodTable[$methodName] = array();
			$methodTable[$methodName]["description"] = ($description == "") ? "No description given." : $description;
			
			$details = MethodTable::getArgumentsDetails($params,$paramsComment);
			$default = $value["defaults"];
			$arguments = array();
			
			foreach ($params as $index => $paramName){
				$argument = array();
				$argument["name"] = $paramName;
				$argument["details"] = $details[$index];
				if(array_key_exists($index, $default)){
					$argument["default"] = $default[$index];	
				}
				$arguments[$index] = $argument;
			}
			
			$methodTable[$methodName]["arguments"] = $arguments;
			
			//$methodTable[$methodName]["access"] = ($access == "") ? "private" : $access; // <- why is this needed?
			$methodTable[$methodName]["lenght"] = $methodLength;

			if($roles != "") $methodTable[$methodName]["roles"] = $roles;
			if($instance != "") $methodTable[$methodName]["instance"] = $instance;
			if($returns != "") $methodTable[$methodName]["return"] = $returns;
		}

		//$classComment = trim(str_replace("\r\n", "\n", MethodTable::getMethodDescription($classComment)));

		return $methodTable;
	}

	/**
	 * Php 5 and newer.
	 */
	private static function getClassMethodsReflection($sourcePath, $className, & $classComment){
		
		//Include the class in question
		$dir = dirname($sourcePath);
		if(!is_dir($dir)){
			return array();
		}

		chdir($dir);

		if(!file_exists($sourcePath)){
			return array();
		}

		//HACK: eAccelerator
		//Check if eAccelator is installed
		if( extension_loaded( "eAccelerator" )){
			//Touch the file so the results of getDocComment will be accurate
			touch($sourcePath);
		}

		$included = include_once($sourcePath);
		if($included === FALSE){
			return array();
		}

		//Verify that the class exists
		if(!class_exists($className)){
			return array();
		}

		$methodTable = array();

		$class = new ReflectionClass($className);

		$classComment = $class->getDocComment();
		$methods = $class->getMethods();


		foreach($methods as $reflectionMethod){
			$methodName = $reflectionMethod->name;
			if($reflectionMethod->isPublic() && $methodName[0] != '_' && $methodName != 'beforeFilter'){
				if($reflectionMethod->isConstructor()){
					$classComment .= $reflectionMethod->getDocComment();
					
				}else{
					
					$reflectionParameter = $reflectionMethod->getParameters();

					$methodTableEntry = array();			
					$params = array();

					$defults = array();

					foreach($reflectionParameter as $parameter){
						$param = $parameter->getName();						
						$index = array_push($params,$param);
						
						if($parameter->isDefaultValueAvailable()){
							$defults[$index-1] = $parameter->getDefaultValue();
						}
					}

					$methodTableEntry['args'] = $params;
					$methodTableEntry['name'] = $reflectionMethod->name;
					$methodTableEntry['comment'] = $reflectionMethod->getDocComment();
					$methodTableEntry['lenght'] = $reflectionMethod->getNumberOfRequiredParameters();
					$methodTableEntry['defaults'] = $defults;

					array_push($methodTable,$methodTableEntry);
				}
			}
		}

		return $methodTable;
	}

	/**
	 * 
	 */
	private static function getMethodCommentArguments($comment){
		
		$params = explode('@param', $comment);
		$args = array();
		
		if(is_array($params) && count($params) > 1){
			array_shift($params);
			
			foreach($params as $param){
				$param = trim($param);
				if(substr($param,0,1) == '$'){
					$param = substr($param,1);
					$param = strrstr($param, '@');
					$param = strrstr($param, '*/');
					$param = str_replace("{","",$param);
					$param = str_replace("}","",$param);
					$param = MethodTable::cleanComment($param);
					array_push($args,$param);	
				}
			}
		}
		
		return $args;
	}

	/**
	 * Returns an array with the arguments of a method.
	 * 
	 * @param $arguments A Array contaning names of the arguments in the method.
	 * @param $comments A Array containing params docs.
	 * 
	 * @return A Array where comments are clean and indexed after arguments.
	 */
	private static function getArgumentsDetails($arguments,$comments){
		
		$len = count($arguments);
		
		if($len < 1){
			return array();
		}
		
		// fills keys in argument-descriptions array to match number of arguments.
		$params = array_fill(0, $len , null);
		
		// cleaning comments and puts them in their slots relative to arguments.
		foreach ($comments as $comment) {
			
			$comment = MethodTable::cleanComment($comment);
			$start = strpos($comment." "," ");
			$param = substr($comment, 0, $start);
			$param = trim($param);
			//echo("\"" . $param . "\"</br>");
			
			$index = array_search($param,$arguments);
			//echo("index: \"" . $index . " slot: " . ($index !== FALSE ? "true" : "false") . "\"</br>");
			if($index !== FALSE){
				$comment = substr($comment,$start);
				$comment = trim($comment);
				if($comment === ""){
					continue;
				}
				$params[$index] = $comment;
			}
		}
		
		return $params; // count($params) > 0 ? $params : null;
	}

	/**
	 * Returns the description from the comment.
	 * The description is(are) the first line(s) in the comment.
	 * 
	 * @param $comment {String} The method's comment.
	 */
	private static function getMethodDescription($comment){
		$comment = MethodTable::cleanComment(strrstr($comment, "@"));
		return trim($comment);
	}


	/**
	 * Returns the value of a comment attribute.
	 * 
	 * @param $comment {String} The method's comment.
	 * @param $attribute {String} The name of the attribute to get its value from.
	 */
	private static function getMethodCommentAttribute($comment, $attribute){
		$pieces = strstrafter($comment, '@' . $attribute);
		if($pieces !== FALSE)
		{
			$pieces = strrstr($pieces, '@');
			$pieces = strrstr($pieces, '*/');
			return MethodTable::cleanComment($pieces);
		}
		return "";
	}

	/**
	 * Returns the value of a comment attribute.
	 *
	 * @param $comment {String} The method's comment.
	 * @param $attribute {String} The name of the attribute to get its value from.
	 */
	private static function getMethodCommentAttributeFirstLine($comment, $attribute){
		$pieces = strstrafter($comment, '@' . $attribute);
		if($pieces !== FALSE)
		{
			$pieces = strrstr($pieces, '@');
			$pieces = strrstr($pieces, "*");
			$pieces = strrstr($pieces, "/");
			$pieces = strrstr($pieces, "-");
			$pieces = strrstr($pieces, "\n");
			$pieces = strrstr($pieces, "\r");
			$pieces = strrstr($pieces, '*/');
			return MethodTable::cleanComment($pieces);
		}
		return "";
	}

	/**
	 * 
	 * 
	 */
	private static function getMethodCommentAttributeFirstWord($comment, $attribute){
		$pieces = strstrafter($comment, '@' . $attribute);
		if($pieces !== FALSE)
		{
			$val = MethodTable::cleanComment($pieces);
			return trim(strrstr($val, ' '));
		}
		return "";
	}

	/**
	 * Cleans the arguments array.
	 * This method removes all whitespaces and the leading "$" sign from each argument
	 * in the array.
	 * 
	 * @param $args {Array} The "dirty" array with arguments.
	 */
	private static function cleanArguments($args, $commentParams){
		$result = array();

		foreach($args as $index => $arg){
			//$arg = strrstr(str_replace('(', '', $arg), '=');
			if(!isset($commentParams[$index])){
				$result[$arg] = null;
			}else{
				$end = trim(str_replace('$', '', $commentParams[$index]));
				/*
				if($end != "" && $start != "" && strpos(strtolower($end), strtolower($start)) === 0){
					$end = substr($end, strlen($start));
				}
				*/
				$result[$arg] = trim($end);
			}
		}
		return $result;
	}


	/**
	 * Cleans the comment string by removing all comment start and end characters.
	 * 
	 * @param $comment {String} The method's comment.
	 */
	private static function cleanComment($comment){
		$comment = str_replace("/**", "", $comment);
		$comment = str_replace("*/", "", $comment);
		$comment = str_replace("*", "", $comment);
		$comment = str_replace("\r", "", trim($comment));
		$comment = preg_replace("{\n[ \t]+}", "\n", trim($comment));
		$comment = str_replace("\n", " ", trim($comment));
		$comment = preg_replace("{[\t ]+}", " ", trim($comment));

		$comment = str_replace("\"", "\\\"", $comment);
		return $comment;
	}
}

?>