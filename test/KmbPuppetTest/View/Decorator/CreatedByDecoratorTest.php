<?php
namespace KmbPuppetTest\View\Decorator;

use KmbBaseTest\View\Decorator\AbstractDecoratorTestCase;
use KmbDomain\Model\Log;
use KmbPuppet\View\Decorator\CreatedByDecorator;
use KmbPuppetTest\Bootstrap;

class CreatedByDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new CreatedByDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ User __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $this->assertEquals('<pre>## Fake user ##</pre>', $this->decorator->decorateValue(new Log(null, 'Fake user')));
    }
}
