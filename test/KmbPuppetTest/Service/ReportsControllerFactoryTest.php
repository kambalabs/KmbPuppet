<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Controller\EnvironmentsController;
use KmbPuppetTest\Bootstrap;

class ReportsControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $controllerManager = Bootstrap::getServiceManager()->get('ControllerManager');
        /** @var EnvironmentsController $controller */
        $controller = $controllerManager->get('KmbPuppet\Controller\Reports');

        $this->assertInstanceOf('KmbPuppet\Controller\ReportsController', $controller);
        $this->assertInstanceOf('KmbDomain\Service\EnvironmentRepositoryInterface', $controller->getEnvironmentRepository());
    }
}
