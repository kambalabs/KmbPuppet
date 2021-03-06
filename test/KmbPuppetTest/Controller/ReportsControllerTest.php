<?php
namespace KmbPuppetTest\Controller;

use KmbBase\FakeDateTimeFactory;
use KmbPuppetDb\Model;
use KmbPuppetDbTest\FakeHttpClient;
use KmbPuppetTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ReportsControllerTest extends AbstractHttpControllerTestCase
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
        $serviceManager->setService('DateTimeFactory', new FakeDateTimeFactory(new \DateTime('2014-03-31')));
        $serviceManager->setService('KmbPuppetDb\Http\Client', new FakeHttpClient('2014-03-31'));
    }

    /** @test */
    public function canGetIndex()
    {
        $this->dispatch('/puppet/reports');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Reports');
        $this->assertQueryContentContains('#reports th', 'Type');
    }

    /** @test */
    public function canGetIndexInJson()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch('/puppet/reports');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Reports');
        $response = (array)Json::decode($this->getResponse()->getContent());
        $this->assertEquals(16, count($response['data']));
        $this->assertContains('node2.local', $response['data'][0][5]);
    }

    /** @test */
    public function canGetIndexWithPagingInJson()
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');

        $this->dispatch('/puppet/reports?draw=1&start=3&length=5');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbPuppet\Controller\Reports');
        $response = (array)Json::decode($this->getResponse()->getContent());
        $this->assertEquals(5, count($response['data']));
        $this->assertEquals(16, $response['recordsTotal']);
        $this->assertEquals(16, $response['recordsFiltered']);
        $this->assertContains('node3.local', $response['data'][0][5]);
    }
}
