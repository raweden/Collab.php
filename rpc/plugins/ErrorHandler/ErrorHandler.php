<?php
/**
 * sets a custom error handler to catch notices and such and transform them to exceptions.
 * note: this could be enhanced to use filters so that at the end of the gateway execution the error handling is set back to normal
 * This could be useful especially for integration with frameworks.
 * 
 * @author Ariel Sommeria-Klein
 */

class ErrorHandler{
     /**
     * constructor. Add filters on the HookManager.
     * @param array $config optional key/value pairs in an associative array. Used to override default configuration values.
     */
    public function  __construct(array $config = null) {
        set_error_handler("custom_warning_handler");
    }
}

function custom_warning_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    throw new Exception($errstr . "\n<br>file: " . $errfile . "\n<br>line:" . $errline . "\n<br>context: " . print_r($errcontext, true), $errno);
}

?>
