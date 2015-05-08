<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\GroupClass;
use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\Revision;
use KmbPmProxy\Model\PuppetClass;
use KmbPmProxy\Model\PuppetModule;
use KmbPuppetTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class GroupParameterControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');
        $group = new Group('default');
        $group->setId(1);
        $group->setRevision(new Revision());
        $group->setEnvironment($environment);
        $groupClass = new GroupClass();
        $groupClass->setId(1);
        $groupClass->setName('dns');
        $groupClass->setGroup($group);
        $group->addClass($groupClass);
        $groupParameter = new GroupParameter();
        $groupParameter->setId(1);
        $groupParameter->setName('server');
        $groupParameter->setClass($groupClass);
        $environmentRepository = $this->getMock('KmbDomain\Service\EnvironmentRepositoryInterface');
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);

        $serviceManager->setService('RevisionRepository', $this->getMock('KmbDomain\Service\RevisionRepositoryInterface'));

        $groupRepository = $this->getMock('KmbDomain\Service\GroupRepositoryInterface');
        $groupRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($group));
        $serviceManager->setService('GroupRepository', $groupRepository);

        $parameterRepository = $this->getMock('KmbDomain\Service\GroupParameterRepositoryInterface');
        $parameterRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($groupParameter));
        $serviceManager->setService('GroupParameterRepository', $parameterRepository);

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
    public function canUpdate()
    {
        $this->dispatch('/env/1/puppet/group/1/parameter/1/update?selectedClass=dns', 'POST', ['name']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1?selectedClass=dns#parameter1');
    }

    /** @test */
    public function canRemove()
    {
        $this->dispatch('/env/1/puppet/group/1/parameter/1/remove?selectedClass=dns');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1?selectedClass=dns');
    }

    /** @test */
    public function canAddChild()
    {
        $this->dispatch('/env/1/puppet/group/1/parameter/1/add-child?selectedClass=dns', 'POST', ['name' => 'test']);

        echo $this->getResponse()->getContent();
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1?selectedClass=dns');
    }
}
