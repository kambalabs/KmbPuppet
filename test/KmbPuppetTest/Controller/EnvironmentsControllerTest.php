<?php
namespace KmbPuppetTest\Controller;

use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class EnvironmentsControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    /** @var \PDO */
    protected $connection;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();

        /** @var $dbAdapter AdapterInterface */
        $dbAdapter = $this->getApplicationServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->connection = $dbAdapter->getDriver()->getConnection()->getResource();
        $this->connection->exec(file_get_contents(Bootstrap::rootPath() . '/data/migrations/sqlite/schema.sql'));
        \PHPUnit_Extensions_Database_Operation_Factory::INSERT()->execute(
            new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($this->connection),
            new \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(Bootstrap::rootPath() . '/test/data/fixtures.xml')
        );
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/puppet/environments');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
    }

    /** @test */
    public function canRemove()
    {
        $this->dispatch('/puppet/environments/10/remove');

        $this->assertRedirectTo('/puppet/environments');
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $this->assertEquals(17, intval($this->connection->query('SELECT COUNT(*) FROM environments')->fetchColumn()));
    }

    /** @test */
    public function cannotRemoveUnknown()
    {
        $this->dispatch('/puppet/environments/99999/remove');

        $this->assertResponseStatusCode(404);
    }

    /** @test */
    public function cannotRemoveEnvironmentWithChildren()
    {
        $this->dispatch('/puppet/environments/1/remove');

        $this->assertApplicationException('ZfcRbac\Exception\UnauthorizedException');
    }
}
