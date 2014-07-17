<?php
namespace KmbPuppetTest\View\Decorator;

use KmbBaseTest\View\Decorator\AbstractDecoratorTestCase;
use KmbPuppet\View\Decorator\ReportNodeNameDecorator;
use KmbPuppetDb\Model\Report;
use KmbPuppetTest\Bootstrap;

class ReportNodeNameDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new ReportNodeNameDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ Server __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $report = new Report();
        $report->setNodeName('node1.local');

        $this->assertEquals('<a href="/servers/node1.local?back=/puppet/reports" class="show-server" data-rel="tooltip" data-placement="left" data-original-title=".. node1.local ..">## node1 ##</a>', $this->decorator->decorateValue($report));
    }
}
