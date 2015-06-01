<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\LogCollectorFactory;
use KmbPuppetTest\Bootstrap;

class LogCollectorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new LogCollectorFactory();

        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbPuppet\Service\LogCollector', $service);
        $this->assertInstanceOf('KmbDomain\Service\LogRepositoryInterface', $service->getLogRepository());
    }
}
