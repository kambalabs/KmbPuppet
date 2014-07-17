<?php
namespace KmbPuppetTest\View\Decorator;

use KmbCoreTest\View\Decorator\AbstractDecoratorTestCase;
use KmbPuppet\View\Decorator\ReportMessageDecorator;
use KmbPuppetDb\Model\Report;
use KmbPuppetTest\Bootstrap;

class ReportMessageDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new ReportMessageDecorator();
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
        $report = new Report();
        $report->setMessage("content changed '{md5}9139c' to '{md5}ba9521'");

        $this->assertEquals('<pre>## content changed ##</pre>', $this->decorator->decorateValue($report));
    }
}
