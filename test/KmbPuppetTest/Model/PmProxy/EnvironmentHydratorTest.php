<?php
namespace KmbPuppetTest\Model\PmProxy;

use KmbPuppet\Model\Environment;
use KmbPuppet\Model\PmProxy\EnvironmentHydrator;

class EnvironmentHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $environment = new Environment();
        $environment->setName('STABLE');
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals(['name' => 'STABLE'], $hydrator->extract($environment));
    }

    /** @test */
    public function canExtractWithParent()
    {
        $parent = new Environment();
        $parent->setId(1);
        $environment = new Environment();
        $environment->setName('STABLE');
        $environment->setParent($parent);
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals(['name' => 'STABLE', 'parent' => 1], $hydrator->extract($environment));
    }
}
