<?php
namespace KmbPuppetTest\Infrastructure\ZendDb;

use KmbPuppet\Infrastructure\ZendDb\EnvironmentHydrator;
use KmbPuppet\Model\Environment;

class EnvironmentHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals([
            'id' => 1,
            'name' => 'STABLE'
        ], $hydrator->extract($environment));
    }

    /** @test */
    public function canHydrate()
    {
        $environment = new Environment();
        $hydrator = new EnvironmentHydrator();

        $hydratedEnvironment = $hydrator->hydrate([
            'id' => 1,
            'name' => 'STABLE',
        ], $environment);

        $this->assertEquals(1, $hydratedEnvironment->getId());
        $this->assertEquals('STABLE', $hydratedEnvironment->getName());
    }
}
