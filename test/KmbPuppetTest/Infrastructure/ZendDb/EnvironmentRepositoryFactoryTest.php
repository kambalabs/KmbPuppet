<?php
namespace KmbPuppetTest\Infrastructure\ZendDb;

use KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository;
use KmbPuppet\Infrastructure\ZendDb\EnvironmentRepositoryFactory;
use KmbPuppetTest\Bootstrap;

class EnvironmentRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new EnvironmentRepositoryFactory();
        $factory->setConfig([
            'aggregate_root_class' => 'KmbPuppet\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbPuppet\Service\EnvironmentProxyFactory',
            'table_name' => 'environments',
            'paths_table_name' => 'environments_paths',
            'repository_class' => 'KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository',
        ]);

        /** @var EnvironmentRepository $service */
        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository', $service);
        $this->assertEquals('environments_paths', $service->getPathsTableName());
    }

    /**
     * @test
     * @expectedException \GtnPersistZendDb\Exception\MissingConfigurationException
     * @expectedExceptionMessage paths_table_name is missing in repository configuration
     */
    public function cannotCreateServiceIfNoPathsTableName()
    {
        $factory = new EnvironmentRepositoryFactory();
        $factory->setConfig([
            'aggregate_root_class' => 'KmbPuppet\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbPuppet\Service\EnvironmentProxyFactory',
            'table_name' => 'environments',
            'repository_class' => 'KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository',
        ]);

        $factory->createService(Bootstrap::getServiceManager());
    }
}
