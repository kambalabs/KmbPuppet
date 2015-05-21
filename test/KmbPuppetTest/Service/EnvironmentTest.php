<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model as DomainModel;
use KmbPuppetDb\Model as PuppetDbModel;
use KmbPuppet\Service as PuppetService;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    /** @var  PuppetService\Environment */
    protected $environmentService;

    protected function setUp()
    {
        $this->environmentRepository = $this->getMock('KmbDomain\Service\EnvironmentRepositoryInterface');
        $this->environmentService = new PuppetService\Environment();
        $this->environmentService->setEnvironmentRepository($this->environmentRepository);
    }

    /** @test */
    public function canFindEnvironment()
    {
        $root = new DomainModel\Environment('STABLE');
        $environment = new DomainModel\Environment('PF1');
        $root->addChild($environment);
        $node = new PuppetDbModel\Node('node1.local');
        $node->setEnvironment('STABLE_PF1');
        $this->environmentRepository->expects($this->any())
            ->method('getByNormalizedName')
            ->will($this->returnValue($environment));

        $this->assertEquals($environment, $this->environmentService->getByNode($node));
    }

    /** @test */
    public function canFindDefaultEnvironment()
    {
        $default = new DomainModel\Environment('DEFAULT');
        $node = new PuppetDbModel\Node('node1.local');
        $node->setEnvironment('STABLE_PF1');
        $this->environmentRepository->expects($this->any())
            ->method('getDefault')
            ->will($this->returnValue($default));

        $this->assertEquals($default, $this->environmentService->getByNode($node));
        $this->assertEquals('DEFAULT', $node->getEnvironment());
    }
}
