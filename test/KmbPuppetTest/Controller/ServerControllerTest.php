<?php
namespace KmbPuppetTest\Controller;

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
        $serviceManager->setService('KmbPuppetDb\Service\Node', $nodeService);
        $serviceManager->setService('KmbPuppet\Service\Group', $this->getMock('KmbPuppet\Service\Group'));
    }

    /** @test */
    public function canShow()
    {
        $this->dispatch('/puppet/server/node1.local');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Server');
        $this->assertActionName('show');
    }
}
