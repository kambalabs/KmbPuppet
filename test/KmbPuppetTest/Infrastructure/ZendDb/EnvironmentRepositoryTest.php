<?php
namespace KmbPuppetTest\Infrastructure\ZendDb;

use KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository;
use KmbPuppet\Model\Environment;
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
    public function canAdd()
    {
        /** @var EnvironmentInterface $parent */
        $parent = static::$repository->getById(4);
        $environment = new Environment();
        $environment->setName('BETA');
        $environment->setParent($parent);

        static::$repository->add($environment);

        $this->assertEquals(19, intval(static::$connection->query('SELECT count(*) FROM environments')->fetchColumn()));
        $this->assertEquals(
            [
                [19, 19, 0],
                [4, 19, 1],
                [1, 19, 2],
            ],
            static::$connection->query('SELECT * FROM environments_paths WHERE descendant_id = 19 ORDER BY length')->fetchAll(\PDO::FETCH_NUM)
        );
    }

    /** @test */
    public function canAddRoot()
    {
        $environment = new Environment();
        $environment->setName('TESTING');

        static::$repository->add($environment);

        $this->assertEquals(19, intval(static::$connection->query('SELECT count(*) FROM environments')->fetchColumn()));
        $this->assertEquals(45, intval(static::$connection->query('SELECT count(*) FROM environments_paths')->fetchColumn()));
        $this->assertEquals(
            [[20, 20, 0]],
            static::$connection->query('SELECT * FROM environments_paths WHERE descendant_id = 20 ORDER BY length')->fetchAll(\PDO::FETCH_NUM)
        );
    }

    /** @test */
    public function canUpdate()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = static::$repository->getById(4);
        /** @var EnvironmentInterface $newParent */
        $newParent = static::$repository->getById(2);
        $aggregateRoot->setParent($newParent);
        $aggregateRoot->setName('PF4');

        static::$repository->update($aggregateRoot);

        $this->assertEquals('PF4', static::$connection->query('SELECT name FROM environments WHERE id = 4')->fetchColumn());
        $this->assertEquals('UNSTABLE', static::$connection->query('select name from environments join environments_paths on id = ancestor_id where length = 1 and descendant_id = 4')->fetchColumn());
        $this->assertEquals('PF4', static::$connection->query('select name from environments join environments_paths on id = ancestor_id where length = 1 and descendant_id = 7')->fetchColumn());
    }

    /** @test */
    public function canRemove()
    {
        $aggregateRoot = static::$repository->getById(4);

        static::$repository->remove($aggregateRoot);

        $this->assertEquals(17, intval(static::$connection->query('SELECT count(*) FROM environments')->fetchColumn()));
        $this->assertEquals(25, intval(static::$connection->query('SELECT count(*) FROM environments_paths')->fetchColumn()));
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
