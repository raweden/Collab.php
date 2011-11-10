<?php
class PHP{
	
	public function classes(){
		return get_declared_classes();
	}
	
	public function contants(){
		return get_defined_constants();
	}

}
?>