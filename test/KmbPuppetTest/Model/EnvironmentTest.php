<?php
namespace KmbPuppetTest\Model;

use KmbPuppet\Model\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canGetNormalizedNameForRootEnvironment()
    {
        $root = $this->createEnvironment('STABLE');

        $this->assertEquals('STABLE', $root->getNormalizedName());
    }

    /** @test */
    public function canGetNormalizedNameForChildEnvironment()
    {
        $root = $this->createEnvironment('STABLE');
        $child = $this->createEnvironment('PF1');
        $child->setParent($root);

        $this->assertEquals('STABLE_PF1', $child->getNormalizedName());
    }

    /** @test */
    public function canGetNormalizedNameForGrandChildEnvironment()
    {
        $root = $this->createEnvironment('STABLE');
        $child = $this->createEnvironment('PF1');
        $child->setParent($root);
        $grandchild = $this->createEnvironment('PROD');
        $grandchild->setParent($child);

        $this->assertEquals('STABLE_PF1_PROD', $grandchild->getNormalizedName());
    }

    /**
     * @param $name
     * @return Environment
     */
    protected function createEnvironment($name = null)
    {
        $environment = new Environment();
        return $environment->setName($name);
    }
}
