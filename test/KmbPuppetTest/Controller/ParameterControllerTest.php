<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\Parameter;
use KmbPmProxy\Model\PuppetClass;
use KmbPmProxy\Model\PuppetModule;
use KmbPuppetTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ParameterControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $environment = new Environment();
        $environment->setName('STABLE');
        $group = new Group('default');
        $group->setEnvironment($environment);
        $class = new \KmbDomain\Model\PuppetClass();
        $class->setName('dns');
        $class->setGroup($group);
        $parameter = new Parameter();
        $parameter->setName('server');
        $parameter->setClass($class);
        $environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);

        $parameterRepository = $this->getMock('KmbDomain\Model\ParameterRepositoryInterface');
        $parameterRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($parameter));
        $serviceManager->setService('ParameterRepository', $parameterRepository);

        $puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $puppetModule = new PuppetModule('apache', '2.1.4');
        $puppetModule->setClasses([new PuppetClass('apache::vhost', [], [])]);
        $puppetModuleService->expects($this->any())
            ->method('getAllByEnvironment')
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
        $this->assertRedirectTo('/env/1/puppet/group/1?selectedClass=dns');
    }

    /** @test */
    public function canRemove()
    {
        $this->dispatch('/env/1/puppet/group/1/parameter/1/remove?selectedClass=dns');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1?selectedClass=dns');
    }
}