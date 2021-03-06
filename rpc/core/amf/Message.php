<?php
/**
 * AmfMessage is a data type that encapsulates all of the various properties a Message object can have.
 * 
 * @author Ariel Sommeria-klein
 */
class AmfMessage {
	
	/**
	 * inthe case of a request:
	 * parsed to a service name and a function name. supported separators for the targetUri are "." and "/"
	 * The service name can either be just the name of the class (MirrorService) or include a path(package/MirrorService)
	 * example of full targetUri package/MirrorService/mirrorFunction
	 *
	 * in the case of a response:
	 * the request responseUri + OK/KO
	 * for example: /1/onResult or /1/onStatus
	 *
	 * @var String
	 */
	public $targetUri = "";

	/**
	 * in the case of a request:
	 * operation name, for example /1
	 *
	 * in the case of a response:
	 * undefined
	 * 
	 * @var String
	 */
	public $responseUri = "";

	/**
	 *
	 * @var <mixed>
	 */
	public $data;

	public function  __construct($targetUri = "", $responseUri = "", $data = null) {
		$this->targetUri = $targetUri;
		$this->responseUri = $responseUri;
		$this->data = $data;
	}
	
}
?>
