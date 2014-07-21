<?php
namespace KmbPuppetTest\Controller;

use KmbPuppet\Model\Environment;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class EnvironmentsControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();
        $this->environmentRepository = $this->getMock('KmbPuppet\Model\EnvironmentRepositoryInterface');
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('EnvironmentRepository', $this->environmentRepository);
    }

    /** @test */
    public function canGetIndex()
    {
        $parent1 = $this->createEnvironment(1, 'STABLE', 'STABLE');
        $parent2 = $this->createEnvironment(2, 'UNSTABLE', 'UNSTABLE');
        $child11 = $this->createEnvironment(3, 'PF1', 'STABLE_PF1', $parent1);
        $child12 = $this->createEnvironment(4, 'PF2', 'STABLE_PF2', $parent1);
        $parent1->setChildren([$child11, $child12]);

        $this->environmentRepository->expects($this->any())
            ->method('getAllRoots')
            ->will($this->returnValue([$parent1, $parent2]));

        $this->dispatch('/puppet/environments');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
    }

    /**
     * @param $id
     * @param $name
     * @param $normalizedName
     * @param $parent
     * @param $children
     * @return Environment
     */
    protected function createEnvironment($id = null, $name = null, $normalizedName = null, $parent = null, $children = null)
    {
        $environment = new Environment();
        return $environment->setId($id)->setName($name)->setNormalizedName($normalizedName)->setParent($parent)->setChildren($children);
    }
}
