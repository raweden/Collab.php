<?php
/**
 * Description
 *
 * Copyright 2012, Raweden. All Rights Reserved.
 *
 * @author Raweden
 * @created 
 */
class Path{

	/**
	 * 
	 * 
	 * @param $path
	 * 
	 * @return A string representing the absolute path.
	 */
	public static function toNativePath($path){
		$parts = explode("/", $path);
	}

	/**
	 * 
	 * 
	 * @param $name
	 * 
	 * @return A Boolean value that determine whether the name is valid.
	 */
	public static function isValidFilename($name){
		// http://en.wikipedia.org/wiki/Filename
	}
	
	/**
	 * Normalizes a string path, takes care of '..' and '.' parts.
	 * When multiple slashes are found, they're replaces by a single one. trailing slashes are preserved.
	 *  
	 * @param $path 
	 *  
	 * @return 
	 */
	public static function normalize($path){
		
		$abs = substr($path,0,1) == "/";
		$tail = substr($path,-1) == "/";
		
		$path = explode("/",$path);		// bellow: node.js supplies the last argument as !abs
		$path = self::normalizeArray($path,false);
		
		$path = implode("/",$path);
		
		if(!$path && !$abs){
			$path = ".";
		}
		
		if($path && $tail){
			$path .= "/";
		}
		
		return ($abs ? "/" : "") . $path;
	}
	
	/**
	 *  
	 * @param $parts A array
	 * @param $allowAboveRoot A Boolean value 
	 * 
	 * @return A array contaning normalized path components.
	 */
	private static function normalizeArray($parts,$allowAboveRoot = false){
		$up = 0;
		// if the path tries to go above the root, 'up' ends up > 0
		for($i = count($parts)-1; $i >= 0; $i--){
			$last = $parts[$i];
			if($last == "." || $last == ""){
				array_splice($parts, $i, 1);
			}else if($last == ".."){
				array_splice($parts, $i, 1);
				$up++;
			}else if($up){
				array_splice($parts, $i, 1);
				$up--;
			}
		}
		
		// if the path is allowed to go above the root, restore leading ../
		if($allowAboveRoot){
			for(;$up--;$up){
				array_unshift($parts,"..");
			}
		}
		
		return $parts;
	}
	
	/**
	 * Resolves to an absolute path.
	 */
	public static function resolve($from,$to){
		
		/*
		var resolvedPath = '',
	        resolvedAbsolute = false;

	    for (var i = arguments.length - 1; i >= -1 && !resolvedAbsolute; i--) {
	      var path = (i >= 0) ? arguments[i] : process.cwd();

	      // Skip empty and invalid entries
	      if (typeof path !== 'string' || !path) {
	        continue;
	      }

	      resolvedPath = path + '/' + resolvedPath;
	      resolvedAbsolute = path.charAt(0) === '/';
	    }

	    // At this point the path should be resolved to a full absolute path, but
	    // handle relative paths to be safe (might happen when process.cwd() fails)

	    // Normalize the path
	    resolvedPath = normalizeArray(resolvedPath.split('/').filter(function(p) {
	      return !!p;
	    }), !resolvedAbsolute).join('/');

	    return ((resolvedAbsolute ? '/' : '') + resolvedPath) || '.';
	 */
		
	}
	
	/**
	 * Solve the relative path from to.
	 */
	public static function relative($from,$to){
		
		
		
		/*
		from = exports.resolve(from).substr(1);
	    to = exports.resolve(to).substr(1);

	    function trim(arr) {
	      var start = 0;
	      for (; start < arr.length; start++) {
	        if (arr[start] !== '') break;
	      }

	      var end = arr.length - 1;
	      for (; end >= 0; end--) {
	        if (arr[end] !== '') break;
	      }

	      if (start > end) return [];
	      return arr.slice(start, end - start + 1);
	    }

	    var fromParts = trim(from.split('/'));
	    var toParts = trim(to.split('/'));

	    var length = Math.min(fromParts.length, toParts.length);
	    var samePartsLength = length;
	    for (var i = 0; i < length; i++) {
	      if (fromParts[i] !== toParts[i]) {
	        samePartsLength = i;
	        break;
	      }
	    }

	    var outputParts = [];
	    for (var i = samePartsLength; i < fromParts.length; i++) {
	      outputParts.push('..');
	    }

	    outputParts = outputParts.concat(toParts.slice(samePartsLength));

	    return outputParts.join('/');
	  };
	*/
		
	}
	
	
	public static function dirname($path){
		
	}
	
	public static function basename($path){
		
	}
	
	public static function extname($path){
		return array_pop(explode(".", $path));
	}
}
?>