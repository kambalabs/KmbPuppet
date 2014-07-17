<?php
namespace KmbPuppetTest\View\Helper;

use KmbPuppet\View\Helper\ShortHostname;

class ShortHostnameTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canGetShortHostname()
    {
        $helper = new ShortHostname();

        $this->assertEquals('node1', $helper('node1.local'));
    }
}
