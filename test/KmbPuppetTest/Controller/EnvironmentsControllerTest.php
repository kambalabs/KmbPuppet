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
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('pmProxyPuppetModuleService', $this->getMock('KmbPmProxy\Service\PuppetModuleInterface'));
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/puppet/environments');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $this->assertActionName('index');
    }

    /** @test */
    public function canGetDiff()
    {
        $this->dispatch('/puppet/environments/diff', 'GET', ['from' => 1, 'to' => 2]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $this->assertActionName('diff');
    }

    /** @test */
    public function canRemove()
    {
        $this->dispatch('/puppet/environment/10/remove');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/puppet/environments');
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $this->assertEquals(17, intval($this->connection->query('SELECT COUNT(*) FROM environments')->fetchColumn()));
    }

    /** @test */
    public function cannotRemoveUnknown()
    {
        $this->dispatch('/puppet/environment/99999/remove');

        $this->assertResponseStatusCode(404);
    }

    /** @test */
    public function cannotRemoveEnvironmentWithChildren()
    {
        $this->dispatch('/puppet/environment/1/remove');

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
    public function canDuplicate()
    {
        $this->dispatch('/puppet/environment/1/duplicate', 'POST', ['name' => 'STABLE2']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/puppet/environments');
        $this->assertEquals(31, intval($this->connection->query('SELECT COUNT(*) FROM environments')->fetchColumn()));
    }

    /** @test */
    public function canUpdate()
    {
        $this->dispatch('/puppet/environment/4/update', 'POST', ['parent' => 2, 'name' => 'PF4']);

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/puppet/environments');
        $this->assertEquals('PF4', $this->connection->query('SELECT name FROM environments WHERE id = 4')->fetchColumn());
        $this->assertEquals('UNSTABLE', $this->connection->query('select name from environments join environments_paths on id = ancestor_id where length = 1 and descendant_id = 4')->fetchColumn());
    }

    /** @test */
    public function cannotGetUsersOfUnknownEnvironment()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environment/99999/users');

        $this->assertResponseStatusCode(404);
    }

    /** @test */
    public function canGetUsers()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environment/4/users');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $response = (array)Json::decode($this->getResponse()->getContent());
        $this->assertEquals(2, count($response['data']));
        $firstUser = $response['data'][0];
        $this->assertEquals('psmith', $firstUser[0]);
        $this->assertEquals('Paul SMITH', $firstUser[1]);
        $this->assertContains(UserInterface::ROLE_ADMIN, $firstUser[2]);
    }

    /** @test */
    public function cannotGetAvailableUsersForUnknownEnvironment()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environment/99999/available-users');

        $this->assertResponseStatusCode(404);
    }

    /** @test */
    public function canGetAvailableUsers()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/environment/4/available-users');

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

    /** @test */
    public function canAddUsers()
    {
        $this->dispatch('/puppet/environment/4/add-users', 'POST', ['users' => [4, 6, 7, 18]]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Environments');
        $this->assertEquals([[3], [4], [6], [7]], $this->connection->query('SELECT user_id FROM environments_users WHERE environment_id = 4')->fetchAll(\PDO::FETCH_NUM));
    }

    /** @test */
    public function canRemoveUser()
    {
        $this->dispatch('/puppet/environment/4/user/3/remove');

        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/puppet/environments');
        $this->assertEquals([[4]], $this->connection->query('SELECT user_id FROM environments_users WHERE environment_id = 4')->fetchAll(\PDO::FETCH_NUM));
    }
}
