<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
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
    }

    /** @test */
    public function canGetIndex()
    {
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
        $moduleService = $this->getMock('KmbPmProxy\Service\ModuleInterface');
        $moduleService->expects($this->any())
            ->method('getAllByEnvironment')
            ->will($this->returnValue([]));
        $serviceManager->setService('KmbPmProxy\Service\Module', $moduleService);

        $this->dispatch('/env/1/puppet/modules');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Modules');
    }
}
