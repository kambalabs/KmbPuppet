<?php
namespace KmbPuppetTest\View\Helper;

use KmbBaseTest\View\Helper\FakeEscapeHtmlHelper;
use KmbPuppet\View\Helper\FormatModuleVersion;
use KmbPuppetTest\Bootstrap;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;

class FormatModuleVersionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canFormatTaggedVersion()
    {
        $this->assertEquals('## 1.0.0 ##', $this->formatModuleVersion('1.0.0'));
    }

    /** @test */
    public function canFormatBranchVersion()
    {
        $this->assertEquals('## master ##', $this->formatModuleVersion('1.0.1-master'));
        $this->assertEquals('## master ##', $this->formatModuleVersion('1.0.1-23-master'));
        $this->assertEquals('## master ##', $this->formatModuleVersion('1.0.1-23-a32f341-master'));
    }

    /** @test */
    public function canFormatBranchVersionWithCommit()
    {
        $this->assertEquals('## master ##', $this->formatModuleVersion('1.0.1-master', true));
        $this->assertEquals('## master ##', $this->formatModuleVersion('1.0.1-23-master', true));
        $this->assertEquals('## master (a32f341) ##', $this->formatModuleVersion('1.0.1-23-a32f341-master', true));
    }

    /**
     * @param string $version
     * @param boolean $withCommit
     * @return string
     */
    private function formatModuleVersion($version, $withCommit = false)
    {
        $helpers = new HelperPluginManager();
        $helpers->setService('escapeHtml', new FakeEscapeHtmlHelper());
        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);
        /** @var FormatModuleVersion $helper */
        $helper = Bootstrap::getServiceManager()->get('ViewHelperManager')->get('formatModuleVersion');
        $helper->setView($view);
        return $helper($version, $withCommit);
    }
}
