<?php
namespace KmbPuppetTest\Widget;

use KmbPuppetTest\Bootstrap;

class ServerTabContentWidgetActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var \KmbPuppet\Widget\ServerTabContentWidgetAction $service */
        $service = Bootstrap::getServiceManager()->get('KmbPuppet\Widget\ServerTabContentWidgetAction');

        $this->assertInstanceOf('KmbPuppet\Widget\ServerTabContentWidgetAction', $service);
        $this->assertInstanceOf('KmbPuppet\Service\Node', $service->getNodeService());
        $this->assertInstanceOf('KmbPuppet\Service\Group', $service->getGroupService());
    }
}
