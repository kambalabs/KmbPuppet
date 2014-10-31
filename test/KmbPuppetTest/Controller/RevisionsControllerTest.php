<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Revision;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class RevisionsControllerTest extends AbstractHttpControllerTestCase
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
        $environment->setLastReleasedRevision(new Revision());
        $environment->setReleasedRevisions([new Revision(), new Revision()]);
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/env/1/puppet/revisions');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Revisions');
        $this->assertActionName('index');
    }
}
