<?php
namespace KmbPuppetTest\Controller;

use KmbPuppet\Controller\ServerShowWidgetAction;
use KmbPuppetTest\Bootstrap;

class ServerShowWidgetActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var ServerShowWidgetAction $service */
        $service = Bootstrap::getServiceManager()->get('KmbPuppet\Controller\ServerShowWidgetAction');

        $this->assertInstanceOf('KmbPuppet\Controller\ServerShowWidgetAction', $service);
        $this->assertInstanceOf('KmbPuppet\Service\Node', $service->getNodeService());
    }
}
