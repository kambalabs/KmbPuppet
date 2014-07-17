<?php
namespace KmbPuppetTest\View\Decorator;

use KmbCoreTest\View\Decorator\AbstractDecoratorTestCase;
use KmbPuppet\View\Decorator\ReportClassNameDecorator;
use KmbPuppetDb\Model\Report;
use KmbPuppetTest\Bootstrap;

class ReportClassNameDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new ReportClassNameDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ Class __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $report = new Report();
        $report->setClassName('Apache');

        $this->assertEquals('## Apache ##', $this->decorator->decorateValue($report));
    }
}
