<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\Revision;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class GroupControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
        $environment = new Environment();
        $environment->setCurrentRevision(new Revision());
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);

        $serviceManager->setService('PuppetClassRepository', $this->getMock('KmbDomain\Model\PuppetClassRepositoryInterface'));

        $groupRepository = $this->getMock('KmbDomain\Model\GroupRepositoryInterface');
        $group = new Group('dns');
        $group->setEnvironment($environment);
        $groupRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($group));
        $serviceManager->setService('GroupRepository', $groupRepository);

        $nodeService = $this->getMock('KmbPuppet\Service\NodeInterface');
        $nodeService->expects($this->any())
            ->method('getAllByEnvironmentAndPatterns')
            ->will($this->returnValue([new Model\Node('node1.local'), new Model\Node('node3.local')]));
        $serviceManager->setService('KmbPuppet\Service\Node', $nodeService);

        $moduleService = $this->getMock('KmbPmProxy\Service\ModuleInterface');
        $moduleService->expects($this->any())
            ->method('getAllByEnvironment')
            ->will($this->returnValue([]));
        $serviceManager->setService('pmProxyModuleService', $moduleService);
    }

    /** @test */
    public function canShow()
    {
        $this->dispatch('/env/1/puppet/group/1');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Group');
        $this->assertActionName('show');
    }

    /** @test */
    public function canGetServers()
    {
        $this->dispatch('/env/1/puppet/group/1/servers');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Group');
        $this->assertActionName('servers');
    }

    /** @test */
    public function canUpdate()
    {
        $this->dispatch('/env/1/puppet/group/1/update', 'POST', ['name']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1');
    }

    /** @test */
    public function canAddClass()
    {
        $this->dispatch('/env/1/puppet/group/1/add-class', 'POST', ['class' => 'dns']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1');
    }

    /** @test */
    public function canRemoveClass()
    {
        $this->dispatch('/env/1/puppet/group/1/remove-class/dns');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1');
    }
}
