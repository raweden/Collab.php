<?php

require_once dirname(__FILE__) . '/../../../amfphp/core/PluginManager.php';

/**
 * Test class for core_PluginManager.
 * Generated by PHPUnit on 2011-01-13 at 15:50:51.
 */
class PluginManagerTest extends PHPUnit_Framework_TestCase {

    /**
     * the testPlugins folder must be scanned and in it found the class DummyPlugin, which contains an instanication counter.
     * It is included and instanciated by the plugin manager, and the test looks at the instanciation counter to check that an instance was created
     */
    public function testSimple(){
        $pluginManager = core_PluginManager::getInstance();
        $pluginManager->loadPlugins(dirname(__FILE__) . "/../../testData/testPlugins/");
        $this->assertEquals(1, DummyPlugin::$instanciationCounter);
    }

}

?>
