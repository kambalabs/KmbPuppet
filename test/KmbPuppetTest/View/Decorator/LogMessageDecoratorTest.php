<?php
namespace KmbPuppetTest\View\Decorator;

use KmbBaseTest\View\Decorator\AbstractDecoratorTestCase;
use KmbDomain\Model\Log;
use KmbPuppet\View\Decorator\LogMessageDecorator;
use KmbPuppetTest\Bootstrap;

class LogMessageDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new LogMessageDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ Message __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $this->assertEquals('Fake message', $this->decorator->decorateValue(new Log(null, null, 'Fake message')));
    }
}
