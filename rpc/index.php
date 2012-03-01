<?php
// Includes
require_once dirname(__FILE__) . '/ClassLoader.php';

/* 
 * Main entry point (gateway) for service calls. instanciates the gateway class and uses it to handle the call.
 * 
 * @author Ariel Sommeria-klein
 */
$gateway = HttpRequestGatewayFactory::createGateway();

// Use this to change the current folder to the services folder. Be careful of the case.
// This was done in 1.9 and can be used to support relative includes, and should be used when upgrading from 1.9 to 2.0 if you use relative includes
// chdir(dirname(__FILE__) . "/Services");

$gateway->service();
$gateway->output();


?>
