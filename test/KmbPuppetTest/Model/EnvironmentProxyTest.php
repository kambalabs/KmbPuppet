<?php
namespace KmbPuppetTest\Model;

use KmbPuppet\Model\Environment;
use KmbPuppet\Model\EnvironmentProxy;

class EnvironmentProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var EnvironmentProxy */
    protected $grandpa;

    /** @var EnvironmentProxy */
    protected $parent;

    /** @var EnvironmentProxy */
    protected $proxy;

    /** @var Environment */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    protected function setUp()
    {
        $this->environmentRepository = $this->getMock('KmbPuppet\Model\EnvironmentRepositoryInterface');
        $this->grandpa = $this->createProxy(1, 'ROOT');
        $this->grandpa->setEnvironmentRepository($this->environmentRepository);
        $this->parent = $this->createProxy(2, 'STABLE');
        $this->parent->setEnvironmentRepository($this->environmentRepository);
        $this->proxy = $this->createProxy(3, 'PF1');
        $this->proxy->setEnvironmentRepository($this->environmentRepository);
        $this->aggregateRoot = $this->proxy->getAggregateRoot();
    }

    /** @test */
    public function canSetId()
    {
        $this->proxy->setId(4);

        $this->assertEquals(4, $this->aggregateRoot->getId());
    }

    /** @test */
    public function canGetId()
    {
        $this->assertEquals(3, $this->proxy->getId());
    }

    /** @test */
    public function canSetName()
    {
        $this->proxy->setName('PF2');

        $this->assertEquals('PF2', $this->aggregateRoot->getName());
    }

    /** @test */
    public function canGetName()
    {
        $this->assertEquals('PF1', $this->proxy->getName());
    }

    /** @test */
    public function canGetAncestorsNames()
    {
        $this->parent->setParent($this->grandpa);
        $this->proxy->setParent($this->parent);

        $this->assertEquals(['ROOT', 'STABLE', 'PF1'], $this->proxy->getAncestorsNames());
    }

    /** @test */
    public function canGetNormalizedName()
    {
        $this->proxy->setParent($this->parent);

        $this->assertEquals('STABLE_PF1', $this->proxy->getNormalizedName());
    }

    /** @test */
    public function canGetParentFromRepository()
    {
        $this->environmentRepository->expects($this->any())
            ->method('getParent')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($this->parent));

        $this->assertEquals($this->parent, $this->proxy->getParent());
    }

    /** @test */
    public function canGetChildrenFromRepository()
    {
        $children = $this->getChildren();
        $this->environmentRepository->expects($this->any())
            ->method('getAllChildren')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($children));

        $this->assertEquals($children, $this->proxy->getChildren());
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        $child1 = $this->createProxy(4, 'PRP');
        $child1->setParent($this->proxy);

        $child2 = $this->createProxy(5, 'PROD');
        $child2->setParent($this->proxy);

        return [$child1, $child2];
    }

    /**
     * @param $id
     * @param $name
     * @return Environment
     */
    protected function createEnvironment($id = null, $name = null)
    {
        $environment = new Environment();
        return $environment->setId($id)->setName($name);
    }

    /**
     * @param $id
     * @param $name
     * @return EnvironmentProxy
     */
    protected function createProxy($id = null, $name = null)
    {
        $proxy = new EnvironmentProxy();
        return $proxy->setAggregateRoot($this->createEnvironment($id, $name));
    }
}
