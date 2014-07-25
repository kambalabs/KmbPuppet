<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Exception\RuntimeException;
use KmbPuppet\Model\Environment;
use KmbPuppet\Model\PmProxy\EnvironmentHydrator;
use KmbPuppet\Service;
use KmbPuppetTest\Bootstrap;
use Zend\Log\Logger;

class PmProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var Service\PmProxy */
    protected $pmProxyService;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $httpClient;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $httpResponse;

    protected function setUp()
    {
        /** @var Logger $logger */
        $logger = Bootstrap::getServiceManager()->get('Logger');
        $this->httpClient = $this->getMock('Zend\Http\Client');
        $this->httpResponse = $this->getMock('Zend\Http\Response');
        $this->httpClient->expects($this->any())
            ->method('send')
            ->will($this->returnValue($this->httpResponse));
        $this->pmProxyService = new Service\PmProxy();
        $this->pmProxyService->setBaseUri('http://localhost');
        $this->pmProxyService->setEnvironmentHydrator(new EnvironmentHydrator());
        $this->pmProxyService->setLogger($logger);
        $this->pmProxyService->setHttpClient($this->httpClient);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unexpected PmProxy Response: HTTP/1.0 500 Internal Server Error
     */
    public function cannotSaveWhenRequestFails()
    {
        $this->httpResponse->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(false));
        $this->httpResponse->expects($this->any())
            ->method('renderStatusLine')
            ->will($this->returnValue('HTTP/1.0 500 Internal Server Error'));

        $this->pmProxyService->save($this->createEnvironment(1, 'STABLE'));
    }

    /** @test */
    public function canSave()
    {
        $this->httpResponse->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(true));

        $this->pmProxyService->save($this->createEnvironment(1, 'STABLE'));
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unexpected PmProxy Response: HTTP/1.0 500 Internal Server Error
     */
    public function cannotRemoveWhenRequestFails()
    {
        $this->httpResponse->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(false));
        $this->httpResponse->expects($this->any())
            ->method('renderStatusLine')
            ->will($this->returnValue('HTTP/1.0 500 Internal Server Error'));

        $this->pmProxyService->remove($this->createEnvironment(1, 'STABLE'));
    }

    /** @test */
    public function canRemove()
    {
        $this->httpResponse->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(true));

        $this->pmProxyService->save($this->createEnvironment(1, 'STABLE'));
    }

    protected function createEnvironment($id, $name, $parent = null)
    {
        $environment = new Environment();
        $environment->setId($id);
        $environment->setName($name);
        $environment->setParent($parent);
        return $environment;
    }
}
