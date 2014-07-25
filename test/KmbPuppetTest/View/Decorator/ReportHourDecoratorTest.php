<?php
namespace KmbPuppetTest\View\Decorator;

use KmbBaseTest\View\Decorator\AbstractDecoratorTestCase;
use KmbPuppet\View\Decorator\ReportHourDecorator;
use KmbPuppetDb\Model\Report;
use KmbPuppetTest\Bootstrap;

class ReportHourDecoratorTest extends AbstractDecoratorTestCase
{
    protected function setUp()
    {
        $this->decorator = new ReportHourDecorator();
        $this->decorator->setViewHelperManager($this->getViewHelperManager(Bootstrap::getServiceManager()));
    }

    /** @test */
    public function canDecorateTitle()
    {
        $this->assertEquals('__ Hour __', $this->decorator->decorateTitle());
    }

    /** @test */
    public function canDecorateValue()
    {
        $report = new Report();
        $report->setCreatedAt(new \DateTime('2014-03-31T13:38:32'));
        \Locale::setDefault('fr_FR');

        $this->assertEquals('13:38:32', $this->decorator->decorateValue($report));
    }
}
