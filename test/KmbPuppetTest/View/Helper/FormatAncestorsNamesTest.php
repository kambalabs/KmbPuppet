<?php
namespace KmbPuppetTest\View\Helper;

use KmbDomain\Model\Environment;
use KmbPuppet\View\Helper\FormatAncestorsNames;

class FormatAncestorsNamesTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canFormatAncestorsNames()
    {
        $parent = new Environment();
        $parent->setName('STABLE');
        $environment = new Environment();
        $environment->setName('PF1');
        $environment->setParent($parent);
        $helper = new FormatAncestorsNames();

        $this->assertEquals('STABLE &rArr; PF1', $helper($environment));
    }
}
