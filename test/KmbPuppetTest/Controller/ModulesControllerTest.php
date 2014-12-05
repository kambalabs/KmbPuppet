<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbPmProxy\Model\PuppetModule;
use KmbPmProxy\Model\PuppetClass;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ModulesControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue(new Environment()));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);
        $puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $puppetModule = new PuppetModule('apache', '2.1.4');
        $puppetModule->setClasses([new PuppetClass('apache::vhost', [], [])]);
        $puppetModuleService->expects($this->any())
            ->method('getAllInstalledByEnvironment')
            ->will($this->returnValue([
                'apache' => $puppetModule,
                'ntp' => new PuppetModule('ntp', '1.1.0'),
            ]));
        $puppetModuleService->expects($this->any())
            ->method('getByEnvironmentAndName')
            ->will($this->returnValue($puppetModule));
        $serviceManager->setService('pmProxyPuppetModuleService', $puppetModuleService);
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/env/1/puppet/modules');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Modules');
        $this->assertActionName('index');
    }

    /** @test */
    public function canShow()
    {
        $this->dispatch('/env/1/puppet/module/apache');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Modules');
        $this->assertActionName('show');
    }

    /** @test */
    public function canShowClass()
    {
        $this->dispatch('/env/1/puppet/module/apache/class/apache::vhost');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Modules');
        $this->assertActionName('show-class');
    }
}
