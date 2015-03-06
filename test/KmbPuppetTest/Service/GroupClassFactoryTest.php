<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\GroupClass;
use KmbPuppetTest\Bootstrap;

class GroupClassFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var GroupClass $service */
        $service = Bootstrap::getServiceManager()->get('KmbPuppet\Service\GroupClass');

        $this->assertInstanceOf('KmbPuppet\Service\GroupClass', $service);
        $this->assertInstanceOf('KmbPuppet\Service\Environment', $service->getEnvironmentService());
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModule', $service->getPuppetModuleService());
        $this->assertInstanceOf('KmbPmProxy\Hydrator\RevisionHydrator', $service->getRevisionHydrator());
    }
}
