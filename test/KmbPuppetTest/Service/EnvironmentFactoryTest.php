<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\Environment;
use KmbPuppetTest\Bootstrap;

class EnvironmentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var Environment $service */
        $service = Bootstrap::getServiceManager()->get('KmbPuppet\Service\Environment');

        $this->assertInstanceOf('KmbPuppet\Service\Environment', $service);
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentRepositoryInterface', $service->getEnvironmentRepository());
    }
}
