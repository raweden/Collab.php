<?php

require_once dirname(__FILE__) . '/../../../../amfphp/AMFPHPClassLoader.php';
require_once dirname(__FILE__) . "/../../../testData/TestServicesConfig.php";

/**
 * Test class for DefaultServiceRouter.
 * Generated by PHPUnit on 2010-11-26 at 16:50:22.
 */
class ServiceRouterTest extends PHPUnit_Framework_TestCase {

    /**
     * @var DefaultServiceRouter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $testServiceConfig = new TestServicesConfig();
        $this->object = new core_common_ServiceRouter($testServiceConfig->serviceFolderPaths, $testServiceConfig->serviceNames2ClassFindInfo);
    }

    public function testExecuteMirrorServiceCall(){
        //return one param
        $testParamsArray = array("a");
        $mirrored = $this->object->executeServiceCall("MirrorService", "returnOneParam", $testParamsArray);
        $this->assertEquals($mirrored, "a");

        // return sum
        $testParamsArray = array(1, 2);
        $mirrored = $this->object->executeServiceCall("MirrorService", "returnSum", $testParamsArray);
        $this->assertEquals($mirrored, 3);
    }

    public function testFindDummyServiceInFolder(){
        $ret = $this->object->executeServiceCall("DummyService", "returnNull", array());
        $this->assertEquals($ret, null);
    }

     /**
     * @expectedException Exception
     */
    public function testNoServiceException()
    {
        $ret = $this->object->executeServiceCall("NoService", "noFunction", array());
    }

     /**
     * @expectedException Exception
     */
    public function testNoFunctionException()
    {
        $ret = $this->object->executeServiceCall("DummyService", "noFunction", array());
        $this->assertEquals($ret, null);
    }
}

?>
