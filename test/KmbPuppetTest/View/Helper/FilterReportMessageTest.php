<?php
namespace KmbPuppetTest\View\Helper;

use KmbPuppet\View\Helper\FilterReportMessage;

class FilterReportMessageTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canFilterNormalReportMessage()
    {
        $helper = new FilterReportMessage();

        $this->assertEquals("mode changed '0644' to '0400'", $helper("mode changed '0644' to '0400'"));
    }

    /** @test */
    public function canFilterContentChangedReportMessage()
    {
        $helper = new FilterReportMessage();

        $this->assertEquals('content changed', $helper("content changed '{md5}11a3e229084349bc25d97e29393ced1d' to '{md5}6ccef1b25ea58fb8be3ca1a1a744ea53'"));
    }

    /** @test */
    public function canFilterDefinedContentReportMessage()
    {
        $helper = new FilterReportMessage();

        $this->assertEquals('defined content', $helper("defined content as '{md5}11a3e229084349bc25d97e29393ced1d'"));
    }

    /** @test */
    public function canFilterLongReportMessage()
    {
        $helper = new FilterReportMessage();

        $this->assertEquals("mode changed '0644' to '0400'", $helper("mode changed '0644' to '0400'"));
    }
}
