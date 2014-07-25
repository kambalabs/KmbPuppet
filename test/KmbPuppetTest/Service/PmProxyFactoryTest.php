<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\PmProxy;
use KmbPuppet\Service\PmProxyFactory;
use KmbPuppetTest\Bootstrap;

class PmProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new PmProxyFactory();

        /** @var PmProxy $service */
        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbPuppet\Service\PmProxy', $service);
        $this->assertInstanceOf('Zend\Http\Client', $service->getHttpClient());
        $this->assertEquals('http://localhost:3000', $service->getBaseUri());
        $this->assertInstanceOf('KmbPuppet\Model\PmProxy\EnvironmentHydrator', $service->getEnvironmentHydrator());
        $this->assertInstanceOf('Zend\Log\Logger', $service->getLogger());
    }
}
