<?php
namespace KmbPuppetTest\View\Helper;

use KmbPmProxy\Model\Module;
use KmbPmProxy\Model\PuppetClass;
use KmbPuppet\View\Helper\ModuleLabelClass;
use KmbPuppetTest\Bootstrap;

class ModuleLabelClassTest extends \PHPUnit_Framework_TestCase
{
    protected $serviceManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $puppetClassValidator;

    protected function setUp()
    {
        $this->puppetClassValidator = $this->getMock('KmbPmProxy\Model\PuppetClassValidator');
        Bootstrap::getServiceManager()
            ->setAllowOverride(true)
            ->setService('KmbPmProxy\Model\PuppetClassValidator', $this->puppetClassValidator);
        $this->serviceManager = Bootstrap::getServiceManager()->get('ViewHelperManager');
    }

    /** @test */
    public function canGetSuccess()
    {
        $this->puppetClassValidator->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));
        $module = new Module('apache', '1.0.0');
        $module->setClasses([new PuppetClass()]);
        $helper = new ModuleLabelClass();
        $helper->setServiceLocator($this->serviceManager);

        $this->assertEquals('label-success', $helper($module));
    }

    /** @test */
    public function canGetDanger()
    {
        $this->puppetClassValidator->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(false));
        $module = new Module('apache', '1.0.0');
        $module->setClasses([new PuppetClass()]);
        $helper = new ModuleLabelClass();
        $helper->setServiceLocator($this->serviceManager);

        $this->assertEquals('label-danger', $helper($module));
    }
}
