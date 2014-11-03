<?php
namespace KmbPuppetTest\Controller\Plugin;

use KmbPuppet\Controller\Plugin\WriteRevisionLog;
use KmbPuppetTest\Bootstrap;

class WriteRevisionLogFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var WriteRevisionLog $service */
        $service = Bootstrap::getServiceManager()->get('ControllerPluginManager')->get('writeRevisionLog');

        $this->assertInstanceOf('KmbPuppet\Controller\Plugin\WriteRevisionLog', $service);
        $this->assertInstanceOf('Zend\Authentication\AuthenticationService', $service->getAuthenticationService());
        $this->assertInstanceOf('KmbDomain\Model\RevisionRepositoryInterface', $service->getRevisionRepository());
        $this->assertInstanceOf('KmbBase\DateTimeFactoryInterface', $service->getDateTimeFactory());
    }
}
