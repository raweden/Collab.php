<?php
/**
 * include this to include amf-php
 * note: this list could be generated. In the meantime maintain it manually. 
 * It would be nice to do this alphabetically, It seems however that an interface must be loaded before a class, so do as possible
 *
 * @author Ariel Sommeria-klein
 */

define( 'AMFPHP_ROOTPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// core/common
require_once AMFPHP_ROOTPATH . "core/common/ClassFindInfo.php";
require_once AMFPHP_ROOTPATH . "core/common/IDeserializer.php";
require_once AMFPHP_ROOTPATH . "core/common/IExceptionHandler.php";
require_once AMFPHP_ROOTPATH . "core/common/IDeserializedRequestHandler.php";
require_once AMFPHP_ROOTPATH . "core/common/ISerializer.php";
require_once AMFPHP_ROOTPATH . "core/common/ServiceRouter.php";
require_once AMFPHP_ROOTPATH . "core/common/ServiceCallParameters.php";

// core/amf
require_once AMFPHP_ROOTPATH . "core/amf/Constants.php";
require_once AMFPHP_ROOTPATH . "core/amf/Deserializer.php";
require_once AMFPHP_ROOTPATH . "core/amf/Handler.php";
require_once AMFPHP_ROOTPATH . "core/amf/Header.php";
require_once AMFPHP_ROOTPATH . "core/amf/Message.php";
require_once AMFPHP_ROOTPATH . "core/amf/Packet.php";
require_once AMFPHP_ROOTPATH . "core/amf/Serializer.php";
require_once AMFPHP_ROOTPATH . "core/amf/Util.php";

// core/amf/types
require_once AMFPHP_ROOTPATH . "core/amf/types/ByteArray.php";
require_once AMFPHP_ROOTPATH . "core/amf/types/Undefined.php";
require_once AMFPHP_ROOTPATH . "core/amf/types/Date.php";
require_once AMFPHP_ROOTPATH . "core/amf/types/Xml.php";
require_once AMFPHP_ROOTPATH . "core/amf/types/XmlDocument.php";

// core
require_once AMFPHP_ROOTPATH . "core/Config.php";
require_once AMFPHP_ROOTPATH . "core/Exception.php";
require_once AMFPHP_ROOTPATH . "core/Gateway.php";
require_once AMFPHP_ROOTPATH . "core/FilterManager.php";
require_once AMFPHP_ROOTPATH . "core/HttpRequestGatewayFactory.php";
require_once AMFPHP_ROOTPATH . "core/PluginManager.php";

?>
