<?php
namespace KmbPuppetTest\Controller;

use KmbDomain\Model\UserInterface;
use KmbPuppetDb\Model;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class EnvironmentsControllerTest extends AbstractHttpControllerTestCase
{
    use DatabaseInitTrait;

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
        static::initSchema($this->connection);
        static::initFixtures($this->connection);
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

        $this->assertResponseStatusCode(302);
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

    /** @test */
    public function canCreate()
    {
        $this->dispatch('/puppet/environments/create', 'POST', ['parent' => 1, 'name' => 'PF4']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/puppet/environments');
        $this->assertEquals(19, intval($this->connection->query('SELECT COUNT(*) FROM environments')->fetchColumn()));
    }

    /** @test */
    public function canUpdate()
    {
        $this->dispatch('/puppet/environments/4/update', 'POST', ['parent' => 2, 'name' => 'PF4', 'users' => [4, 6, 7, 18]]);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/puppet/environments');
        $this->assertEquals('PF4', $this->connection->query('SELECT name FROM environments WHERE id = 4')->fetchColumn());
        $this->assertEquals('UNSTABLE', $this->connection->query('select name from environments join environments_paths on id = ancestor_id where length = 1 and descendant_id = 4')->fetchColumn());
        $this->assertEquals([[3], [4], [6], [7]], $this->connection->query('SELECT user_id FROM environments_users WHERE environment_id = 4')->fetchAll(\PDO::FETCH_NUM));
    }

    /** @test */
    public function cannotGetUsersOfUnknownEnvironment()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environments/99999/users');

        $this->assertResponseStatusCode(404);
    }

    /** @test */
    public function canGetUsers()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environments/4/users');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $response = (array)Json::decode($this->getResponse()->getContent());
        $this->assertEquals(2, count($response['data']));
        $firstUser = $response['data'][0];
        $this->assertEquals('psmith', $firstUser[0]);
        $this->assertEquals('Paul SMITH', $firstUser[1]);
        $this->assertEquals(UserInterface::ROLE_ADMIN, $firstUser[2]);
    }

    /** @test */
    public function cannotGetAvailableUsersForUnknownEnvironment()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environments/99999/available-users');

        $this->assertResponseStatusCode(404);
    }

    /** @test */
    public function canGetAvailableUsers()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environments/4/available-users');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $response = (array)Json::decode($this->getResponse()->getContent());
        $this->assertEquals(3, count($response['users']));
        $firstUser = $response['users'][0];
        $this->assertEquals(5, $firstUser->id);
        $this->assertEquals('madams', $firstUser->login);
        $this->assertEquals('Martin ADAMS', $firstUser->name);
        $this->assertEquals('madams@gmail.com', $firstUser->email);
        $this->assertEquals(UserInterface::ROLE_USER, $firstUser->role);
    }
}
