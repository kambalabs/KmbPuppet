<?php
namespace KmbPuppetTest\Model;

use KmbPuppet\Model\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canGetNormalizedNameForRootEnvironment()
    {
        $root = $this->createEnvironment(1, 'STABLE');

        $this->assertEquals('STABLE', $root->getNormalizedName());
    }

    /** @test */
    public function canGetNormalizedNameForChildEnvironment()
    {
        $root = $this->createEnvironment(1, 'STABLE');
        $child = $this->createEnvironment(2, 'PF1');
        $child->setParent($root);

        $this->assertEquals('STABLE_PF1', $child->getNormalizedName());
    }

    /** @test */
    public function canGetNormalizedNameForGrandChildEnvironment()
    {
        $root = $this->createEnvironment(1, 'STABLE');
        $child = $this->createEnvironment(2, 'PF1');
        $child->setParent($root);
        $grandchild = $this->createEnvironment(3, 'PROD');
        $grandchild->setParent($child);

        $this->assertEquals('STABLE_PF1_PROD', $grandchild->getNormalizedName());
    }

    /** @test */
    public function canCheckIfIsNotAncestorOf()
    {
        $environment1 = $this->createEnvironment(1, 'PF1');
        $environment2 = $this->createEnvironment(2, 'PF2');

        $this->assertFalse($environment1->isAncestorOf($environment2));
    }

    /** @test */
    public function canCheckIfIsAncestorOf()
    {
        $root = $this->createEnvironment(1, 'STABLE');
        $child = $this->createEnvironment(2, 'PF1');
        $child->setParent($root);
        $grandchild = $this->createEnvironment(3, 'PROD');
        $grandchild->setParent($child);

        $this->assertTrue($root->isAncestorOf($grandchild));
    }

    /** @test */
    public function canCheckIfIsNotAncestorOfNull()
    {
        $environment = $this->createEnvironment(1, 'PF1');

        $this->assertFalse($environment->isAncestorOf(null));
    }

    /** @test */
    public function canCheckIfHasNotChildWithName()
    {
        $environment = $this->createEnvironment(1, 'PF1');

        $this->assertFalse($environment->hasChildWithName('ITG'));
    }

    /** @test */
    public function canCheckIfHasChildWithName()
    {
        $environment = $this->createEnvironment(1, 'PF1');
        $child = $this->createEnvironment(2, 'ITG');
        $environment->addChild($child);

        $this->assertTrue($environment->hasChildWithName('ITG'));
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
}
