<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Model\Environment;
use KmbPuppet\Model\EnvironmentProxy;
use KmbPuppet\Service\EnvironmentProxyFactory;
use KmbPuppetTest\Bootstrap;

class EnvironmentProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new EnvironmentProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');

        /** @var EnvironmentProxy $proxy */
        $proxy = $factory->createProxy($environment);

        $this->assertInstanceOf('KmbPuppet\Model\EnvironmentProxy', $proxy);
        $this->assertEquals($environment, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository', $proxy->getEnvironmentRepository());
    }
}
