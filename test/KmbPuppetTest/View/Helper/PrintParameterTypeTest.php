<?php
namespace KmbPuppetTest\View\Helper;

use KmbDomain\Model\GroupParameterType;
use KmbPuppet\View\Helper\PrintParameterType;
use Zend\I18n\Translator\Translator;

class PrintParameterTypeTest extends \PHPUnit_Framework_TestCase
{
    public function printParameterType($boolean)
    {
        $helper = new PrintParameterType();
        $helper->setTranslator(new Translator());

        return $helper($boolean);
    }

    /** @test */
    public function canInvokeWithBoolean()
    {
        $this->assertEquals('boolean', $this->printParameterType(GroupParameterType::BOOLEAN));
    }

    /** @test */
    public function canInvokeWithNull()
    {
        $this->assertEquals('-', $this->printParameterType(null));
    }
}
