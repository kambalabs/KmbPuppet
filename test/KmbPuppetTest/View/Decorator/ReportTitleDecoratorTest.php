<?php
namespace KmbPuppetTest\View\Decorator;

use KmbCoreTest\View\Decorator\AbstractDecoratorTestCase;
use KmbPuppet\View\Decorator\ReportTitleDecorator;
use KmbPuppetDb\Model\Report;
use KmbPuppetTest\Bootstrap;

class ReportTitleDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new ReportTitleDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ Title __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $report = new Report();
        $report->setTitle('/etc/hosts');

        $this->assertEquals('<span class="label label-default">## /etc/hosts ##</span>', $this->decorator->decorateValue($report));
    }
}
