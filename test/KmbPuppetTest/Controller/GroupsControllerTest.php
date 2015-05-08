<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\Revision;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class GroupsControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $environmentRepository = $this->getMock('KmbDomain\Service\EnvironmentRepositoryInterface');
        $environment = new Environment();
        $environment->setCurrentRevision(new Revision());
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);

        $serviceManager->setService('RevisionRepository', $this->getMock('KmbDomain\Service\RevisionRepositoryInterface'));

        $groupRepository = $this->getMock('KmbDomain\Service\GroupRepositoryInterface');
        $group = new Group('dns');
        $group->setEnvironment($environment);
        $groupRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($group));
        $groupRepository->expects($this->any())
            ->method('add')
            ->will($this->returnCallback(function ($group) {
                $group->setId(2);
            }));
        $serviceManager->setService('GroupRepository', $groupRepository);

        $nodeService = $this->getMock('KmbPuppet\Service\NodeInterface');
        $nodeService->expects($this->any())
            ->method('getAllByEnvironmentAndPatterns')
            ->will($this->returnValue([new Model\Node('node1.local'), new Model\Node('node3.local')]));
        $serviceManager->setService('KmbPuppet\Service\Node', $nodeService);

        $puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $puppetModuleService->expects($this->any())
            ->method('getAllInstalledByEnvironment')
            ->will($this->returnValue([]));
        $serviceManager->setService('pmProxyPuppetModuleService', $puppetModuleService);
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/env/1/puppet/groups');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Groups');
        $this->assertActionName('index');
    }

    /** @test */
    public function canUpdate()
    {
        $this->dispatch('/env/1/puppet/groups/update', 'POST');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Groups');
        $this->assertActionName('update');
    }

    /** @test */
    public function canCreate()
    {
        $this->dispatch('/env/1/puppet/groups/create', 'POST', ['name' => 'new group']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/2');
    }

    /** @test */
    public function canImport()
    {
        $this->dispatch('/env/1/puppet/groups/import', 'POST', ['confirmFile' => 'kmb_abcd1234.yaml']);

        echo $this->getResponse()->getContent();
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/groups');
    }
}
