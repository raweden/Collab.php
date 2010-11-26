<?php

require_once dirname(__FILE__) . '/../../../amfphp/services/MirrorService.php';

/**
 * Test class for MirrorService.
 * Generated by PHPUnit on 2010-11-26 at 16:56:24.
 */
class MirrorServiceTest extends PHPUnit_Framework_TestCase {

    /**
     * @var MirrorService
     */
    protected $mirrorService;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->mirrorService = new MirrorService;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @todo Implement testMirror().
     */
    public function testMirror() {
        // Remove the following lines when you implement this test.
        $ret = $this->mirrorService->mirror("a", "b", "c");
        $this->assertEquals($ret, array("a", "b", "c"));

    }

}

?>
