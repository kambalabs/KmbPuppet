<?php
namespace KmbPuppetTest\Controller\Plugin;

use KmbPuppet\Controller\Plugin\WriteLog;
use KmbPuppetTest\Bootstrap;

class WriteLogFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var WriteLog $service */
        $service = Bootstrap::getServiceManager()->get('ControllerPluginManager')->get('writeLog');

        $this->assertInstanceOf('KmbPuppet\Controller\Plugin\WriteLog', $service);
        $this->assertInstanceOf('Zend\Authentication\AuthenticationService', $service->getAuthenticationService());
        $this->assertInstanceOf('KmbDomain\Service\LogRepositoryInterface', $service->getLogRepository());
        $this->assertInstanceOf('KmbBase\DateTimeFactoryInterface', $service->getDateTimeFactory());
    }
}
