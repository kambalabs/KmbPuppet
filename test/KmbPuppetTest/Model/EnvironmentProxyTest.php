<?php
namespace KmbPuppetTest\Model;

use KmbPuppet\Model\Environment;
use KmbPuppet\Model\EnvironmentProxy;

class EnvironmentProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var EnvironmentProxy */
    protected $proxy;

    /** @var Environment */
    protected $aggregateRoot;

    /** @var Environment */
    protected $parent;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    protected function setUp()
    {
        $this->proxy = new EnvironmentProxy();
        $this->parent = new Environment();
        $this->parent->setId(1);
        $this->parent->setName('STABLE');
        $this->parent->setNormalizedName('STABLE');
        $this->aggregateRoot = new Environment();
        $this->aggregateRoot->setId(2);
        $this->aggregateRoot->setName('PF1');
        $this->aggregateRoot->setNormalizedName('STABLE_PF1');
        $this->aggregateRoot->setParent($this->parent);
        $this->environmentRepository = $this->getMock('KmbPuppet\Model\EnvironmentRepositoryInterface');
        $this->proxy->setAggregateRoot($this->aggregateRoot);
        $this->proxy->setEnvironmentRepository($this->environmentRepository);
    }

    /** @test */
    public function canSetId()
    {
        $this->proxy->setId(3);

        $this->assertEquals(3, $this->aggregateRoot->getId());
    }

    /** @test */
    public function canGetId()
    {
        $this->assertEquals(2, $this->proxy->getId());
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
    public function canSetNormalizedName()
    {
        $this->proxy->setNormalizedName('STABLE_PF2');

        $this->assertEquals('STABLE_PF2', $this->aggregateRoot->getNormalizedName());
    }

    /** @test */
    public function canGetNormalizedName()
    {
        $this->assertEquals('STABLE_PF1', $this->proxy->getNormalizedName());
    }

    /** @test */
    public function canSetParent()
    {
        $newParent = $this->createEnvironment(3, 'UNSTABLE', 'UNSTABLE');

        $this->proxy->setParent($newParent);

        $this->assertEquals($newParent, $this->aggregateRoot->getParent());
    }

    /** @test */
    public function canGetParent()
    {
        $this->assertEquals($this->parent, $this->proxy->getParent());
    }

    /** @test */
    public function canGetParentFromRepository()
    {
        $this->proxy->setParent(null);
        $this->environmentRepository->expects($this->any())
            ->method('getParent')
            ->with($this->equalTo($this->aggregateRoot))
            ->will($this->returnValue($this->parent));

        $this->assertEquals($this->parent, $this->proxy->getParent());
    }

    /** @test */
    public function canSetChildren()
    {
        $children = $this->getChildren();
        $this->proxy->setChildren($children);

        $this->assertEquals($children, $this->aggregateRoot->getChildren());
    }

    /** @test */
    public function canGetChildren()
    {
        $children = $this->getChildren();
        $this->aggregateRoot->setChildren($children);

        $this->assertEquals($children, $this->proxy->getChildren());
    }

    /** @test */
    public function canGetChildrenFromRepository()
    {
        $children = $this->getChildren();
        $this->environmentRepository->expects($this->any())
            ->method('getAllChildren')
            ->with($this->equalTo($this->aggregateRoot))
            ->will($this->returnValue($children));

        $this->assertEquals($children, $this->proxy->getChildren());
    }

    /** @test */
    public function canCheckWhenEnvironmentHasParent()
    {
        $this->assertTrue($this->proxy->hasParent());
    }

    /** @test */
    public function canCheckWhenEnvironmentHasNoParent()
    {
        $this->aggregateRoot->setParent(null);

        $this->assertFalse($this->proxy->hasParent());
    }

    /** @test */
    public function canCheckWhenEnvironmentHasChildren()
    {
        $this->aggregateRoot->setChildren($this->getChildren());

        $this->assertTrue($this->proxy->hasChildren());
    }

    /** @test */
    public function canCheckWhenEnvironmentHasNoChildren()
    {
        $this->assertFalse($this->proxy->hasChildren());
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        $child1 = $this->createEnvironment(4, 'PRP', 'STABLE_PF1_PRP');
        $child1->setParent($this->aggregateRoot);

        $child2 = $this->createEnvironment(5, 'PROD', 'STABLE_PF1_PROD');
        $child2->setParent($this->aggregateRoot);

        return [$child1, $child2];
    }

    /**
     * @param $id
     * @param $name
     * @param $normalizedName
     * @return Environment
     */
    protected function createEnvironment($id = null, $name = null, $normalizedName = null)
    {
        $environment = new Environment();
        return $environment->setId($id)->setName($name)->setNormalizedName($normalizedName);
    }
}
