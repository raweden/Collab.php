<?php

require_once dirname(__FILE__) . '/../../../amfphp/core/HookManager.php';

/**
 * Test class for core_HookManager.
 * Generated by PHPUnit on 2011-01-13 at 15:51:00.
 */
class HookManagerTest extends PHPUnit_Framework_TestCase {
    public function testSimpleHook() {
        $hookManager = core_HookManager::getInstance();
        $hookManager->addHook("TESTHOOK", array($this, "double"));
        $ret = $hookManager->callHooks("TESTHOOK", array(1));
        $this->assertEquals(array(2), $ret);
    }

    /**
     * note: this function must be public to be called. This is called by hook
     * @param <type> $valueToDouble
     * @return <type>
     */
    public function double($valueToDouble){
        return array($valueToDouble * 2);
    }

}

?>
