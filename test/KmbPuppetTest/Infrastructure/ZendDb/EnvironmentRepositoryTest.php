<?php
namespace KmbPuppetTest\Infrastructure\ZendDb;

use KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository;
use KmbPuppet\Model\EnvironmentInterface;
use KmbPuppetTest\Bootstrap;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use Zend\Db\Adapter\AdapterInterface;

class EnvironmentRepositoryTest extends \PHPUnit_Extensions_Database_TestCase
{
    /** @var \PDO */
    protected static $connection;

    /** @var EnvironmentRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('EnvironmentRepository');

        /** @var $dbAdapter AdapterInterface */
        $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
        static::$connection = $dbAdapter->getDriver()->getConnection()->getResource();

        static::$connection->exec(file_get_contents(Bootstrap::rootPath() . '/data/migrations/sqlite/schema.sql'));
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection(static::$connection);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(Bootstrap::rootPath() . '/test/data/fixtures.xml');
    }

    /** @test */
    public function canGetAllRoots()
    {
        $environments = static::$repository->getAllRoots();

        $this->assertEquals(3, count($environments));
        /** @var EnvironmentInterface $firstEnvironment */
        $firstEnvironment = $environments[0];
        $this->assertInstanceOf('KmbPuppet\Model\EnvironmentInterface', $firstEnvironment);
        $this->assertEquals('STABLE', $firstEnvironment->getName());
    }

    /** @test */
    public function canGetAllChildren()
    {
        $environment = static::$repository->getById(1);

        $children = static::$repository->getAllChildren($environment);

        $this->assertEquals(3, count($children));
        /** @var EnvironmentInterface $firstChild */
        $firstChild = $children[0];
        $this->assertInstanceOf('KmbPuppet\Model\EnvironmentInterface', $firstChild);
        $this->assertEquals('PF1', $firstChild->getName());
    }

    /** @test */
    public function canGetParent()
    {
        $environment = static::$repository->getById(4);

        $parent = static::$repository->getParent($environment);

        $this->assertInstanceOf('KmbPuppet\Model\EnvironmentInterface', $parent);
        $this->assertEquals(1, $parent->getId());
    }
}
