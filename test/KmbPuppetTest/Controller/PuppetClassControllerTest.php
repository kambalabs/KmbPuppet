<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\ParameterType;
use KmbDomain\Model\Revision;
use KmbPmProxy\Model\PuppetClass;
use KmbPmProxy\Model\PuppetModule;
use KmbPuppetTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class PuppetClassControllerTest extends AbstractHttpControllerTestCase
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
        $class = new \KmbDomain\Model\PuppetClass();
        $class->setId(1);
        $class->setName('dns');
        $class->setGroup($group);
        $group->addClass($class);
        $environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);

        $puppetClassRepository = $this->getMock('KmbDomain\Model\PuppetClassRepositoryInterface');
        $puppetClassRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($class));
        $serviceManager->setService('PuppetClassRepository', $puppetClassRepository);

        $parameterRepository = $this->getMock('KmbDomain\Model\ParameterRepositoryInterface');
        $parameterRepository->expects($this->any())
            ->method('add')
            ->will($this->returnCallback(function ($parameter) {
                $parameter->setId(1);
            }));
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

        $puppetClassService = $this->getMock('KmbPmProxy\Service\PuppetClassInterface');
        $template = new \stdClass();
        $template->name = 'ServerName';
        $template->required = true;
        $template->multiple_values = false;
        $template->type = ParameterType::STRING;
        $puppetClass = new PuppetClass('apache::vhost', [$template], []);
        $puppetClassService->expects($this->any())
            ->method('getByEnvironmentAndName')
            ->will($this->returnValue($puppetClass));
        $serviceManager->setService('pmProxyPuppetClassService', $puppetClassService);
    }

    /** @test */
    public function canAddParameter()
    {
        $this->dispatch('/env/1/puppet/class/1/add-parameter', 'POST', ['name' => 'ServerName']);

        echo $this->getResponse()->getContent();
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/group/1?selectedClass=dns#parameter1');
    }
}
