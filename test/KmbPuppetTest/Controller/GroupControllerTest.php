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

        $environmentRepository = $this->getMock('KmbDomain\Service\EnvironmentRepositoryInterface');
        $environment = new Environment();
        $environment->setCurrentRevision(new Revision());
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnCallback(function ($id) use ($environment) {
                return $environment->setId($id);
            }));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);

        $serviceManager->setService('GroupClassRepository', $this->getMock('KmbDomain\Service\GroupClassRepositoryInterface'));

        $serviceManager->setService('RevisionRepository', $this->getMock('KmbDomain\Service\RevisionRepositoryInterface'));

        $groupRepository = $this->getMock('KmbDomain\Service\GroupRepositoryInterface');
        $group = new Group('dns');
        $group->setId(1);
        $group->setRevision(new Revision());
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

        $puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $puppetModuleService->expects($this->any())
            ->method('getAllInstalledByEnvironment')
            ->will($this->returnValue([]));
        $serviceManager->setService('pmProxyPuppetModuleService', $puppetModuleService);
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
        $this->dispatch('/env/1/puppet/group/1/update', 'POST', ['name' => 'web']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1');
    }

    /** @test */
    public function canDuplicate()
    {
        $this->dispatch('/env/1/puppet/group/1/duplicate', 'POST', ['targetEnvId' => 2, 'name' => 'web']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/2/puppet/groups');
    }

    /** @test */
    public function canRemove()
    {
        $this->dispatch('/env/1/puppet/group/1/remove');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/groups');
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

    /** @test */
    public function canExport()
    {
        $this->dispatch('/env/1/puppet/group/1/export');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Group');
        $this->assertActionName('export');
    }
}
