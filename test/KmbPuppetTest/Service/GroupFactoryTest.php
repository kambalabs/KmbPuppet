<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\Group;
use KmbPuppetTest\Bootstrap;

class GroupFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var Group $service */
        $service = Bootstrap::getServiceManager()->get('KmbPuppet\Service\Group');

        $this->assertInstanceOf('KmbPuppet\Service\Group', $service);
        $this->assertInstanceOf('KmbPuppet\Service\Environment', $service->getEnvironmentService());
    }
}
