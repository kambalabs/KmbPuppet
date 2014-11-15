<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Revision;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class RevisionControllerTest extends AbstractHttpControllerTestCase
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

        $revisionRepository = $this->getMock('KmbDomain\Model\RevisionRepositoryInterface');
        $revisionRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue(new Revision($environment)));
        $serviceManager->setService('RevisionRepository', $revisionRepository);

        $serviceManager->setService('revisionService', $this->getMock('KmbDomain\Model\RevisionServiceInterface'));
    }

    /** @test */
    public function canRelease()
    {
        $this->dispatch('/env/1/puppet/revision/1/release');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/revisions');
    }

    /** @test */
    public function canRemove()
    {
        $this->dispatch('/env/1/puppet/revision/1/remove');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/env/1/puppet/revisions');
    }

    /** @test */
    public function canExport()
    {
        $this->dispatch('/env/1/puppet/revision/1/export');

        echo $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Revision');
        $this->assertActionName('export');
    }
}
