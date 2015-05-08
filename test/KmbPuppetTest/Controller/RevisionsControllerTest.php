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

        $environmentRepository = $this->getMock('KmbDomain\Service\EnvironmentRepositoryInterface');
        $environment = new Environment();
        $environment->setCurrentRevision($this->createRevision(3));
        $environment->setLastReleasedRevision($this->createRevision(2));
        $environment->setReleasedRevisions([$this->createRevision(1), $this->createRevision(2)]);
        $environmentRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($environment));
        $environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([]));
        $serviceManager->setService('EnvironmentRepository', $environmentRepository);
        $serviceManager->setService('RevisionRepository', $this->getMock('KmbDomain\Service\RevisionRepositoryInterface'));
        $serviceManager->setService('pmProxyPuppetModuleService', $this->getMock('KmbPmProxy\Service\PuppetModuleInterface'));
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/env/1/puppet/revisions');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Revisions');
        $this->assertActionName('index');
    }

    /** @test */
    public function canGetDiff()
    {
        $this->dispatch('/env/1/puppet/revisions/diff?from=1&to=2');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Revisions');
        $this->assertActionName('diff');
    }

    /**
     * @param int $id
     * @return Revision
     */
    protected function createRevision($id)
    {
        $revision = new Revision();
        return $revision->setId($id);
    }
}
