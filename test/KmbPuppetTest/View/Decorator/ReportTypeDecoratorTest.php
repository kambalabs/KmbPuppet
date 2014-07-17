<?php
namespace KmbPuppetTest\View\Decorator;

use KmbBaseTest\View\Decorator\AbstractDecoratorTestCase;
use KmbPuppet\View\Decorator\ReportTypeDecorator;
use KmbPuppetDb\Model\Report;
use KmbPuppetTest\Bootstrap;

class ReportTypeDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new ReportTypeDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ Type __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $report = new Report('success');
        $report->setType('File');

        $this->assertEquals('<span class="label label-uniform-large label-success">## File ##</span>', $this->decorator->decorateValue($report));
    }
}
