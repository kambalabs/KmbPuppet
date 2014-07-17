<?php
namespace KmbPuppetTest\View\Helper;

use KmbPuppetDb\Model;
use KmbPuppet\View\Helper\ReportLabelClass;

class ReportLabelClassTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canGetSuccessReportLabelClass()
    {
        $report = new Model\Report(Model\ReportInterface::SUCCESS);
        $helper = new ReportLabelClass();

        $this->assertEquals('label-success', $helper($report));
    }

    /** @test */
    public function canGetSkippedReportLabelClass()
    {
        $report = new Model\Report(Model\ReportInterface::SKIPPED);
        $helper = new ReportLabelClass();

        $this->assertEquals('label-warning', $helper($report));
    }

    /** @test */
    public function canGetFailedReportLabelClass()
    {
        $report = new Model\Report(Model\ReportInterface::FAILURE);
        $helper = new ReportLabelClass();

        $this->assertEquals('label-danger', $helper($report));
    }

    /** @test */
    public function canGetNoopReportLabelClass()
    {
        $report = new Model\Report(Model\ReportInterface::NOOP);
        $helper = new ReportLabelClass();

        $this->assertEquals('label-default', $helper($report));
    }
}
