<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Controller\EnvironmentsController;
use KmbPuppetTest\Bootstrap;

class EnvironmentsControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $controllerManager = Bootstrap::getServiceManager()->get('ControllerManager');
        /** @var EnvironmentsController $controller */
        $controller = $controllerManager->get('KmbPuppet\Controller\Environments');

        $this->assertInstanceOf('KmbPuppet\Controller\EnvironmentsController', $controller);
        $this->assertInstanceOf('KmbDomain\Service\EnvironmentRepositoryInterface', $controller->getEnvironmentRepository());
        $this->assertInstanceOf('KmbDomain\Service\UserRepositoryInterface', $controller->getUserRepository());
        $this->assertInstanceOf('KmbPmProxy\Service\Environment', $controller->getPmProxyEnvironmentService());
    }
}
