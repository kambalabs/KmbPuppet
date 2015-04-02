<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ServerControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $nodeService = $this->getMock('KmbPuppetDb\Service\Node');
        $nodeService->expects($this->any())
            ->method('getByName')
            ->will($this->returnValue(new Model\Node('node1.local')));
        $nodeService->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue([new Model\Node('node1.local')]));
        $serviceManager->setService('KmbPuppetDb\Service\Node', $nodeService);
        $serviceManager->setService('KmbPuppet\Service\GroupClass', $this->getMock('KmbPuppet\Service\GroupClass'));
        $environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
        $environmentRepository->expects($this->any())
            ->method('getDefault')
            ->will($this->returnValue(new Environment('DEFAULT')));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);
    }

    /** @test */
    public function canGet()
    {
        $this->dispatch('/api/puppet/server/node1.local');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Server');
        $this->assertActionName('get');
    }

    /** @test */
    public function canGetList()
    {
        $this->dispatch('/api/puppet/servers');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Server');
        $this->assertActionName('getList');
    }

    /** @test */
    public function canUpdate()
    {
        $this->dispatch('/api/puppet/server/node1.local', 'PUT', []);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Server');
        $this->assertActionName('update');
    }
}
