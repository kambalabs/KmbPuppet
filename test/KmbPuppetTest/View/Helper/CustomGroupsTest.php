<?php
namespace KmbPuppetTest\View\Helper;

use KmbBase\Widget\AbstractWidgetAction;
use KmbPuppet\View\Helper\CustomGroups;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

class CustomGroupsTest extends \PHPUnit_Framework_TestCase
{
    /** @var  CustomGroups */
    protected $customGroups;

    protected function setUp()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['partial', 'viewModel'], [], '', false);
        $view->expects($this->any())
            ->method('partial')
            ->will($this->returnCallback(function ($model = null) {
                $params = '';
                foreach ($model->getVariables() as $key => $value) {
                    $params .= $key . ':' . $value . ' ';
                }
                return 'partial ' . $model->getTemplate() . ' ' . $params;
            }));
        $viewModelHelper = $this->getMock('Zend\View\Helper\ViewModel');
        $viewModelHelper->expects($this->any())->method('getCurrent')->will($this->returnValue(new ViewModel()));
        $view->expects($this->any())->method('viewModel')->will($this->returnValue($viewModelHelper));
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('FakeWidgetAction', new FakeWidgetAction());
        $this->customGroups = new CustomGroups();
        $this->customGroups->setServiceLocator($serviceLocator);
        $this->customGroups->setView($view);
    }

    /** @test */
    public function canRenderEmptyWidget()
    {
        $content = $this->customGroups()->render('empty');

        $this->assertEmpty($content);
    }

    /** @test */
    public function canRender()
    {
        $this->customGroups->setConfig([
            'fake' => [
                'label' => 'Add a fake group',
                'widget' => [
                    'action' => 'FakeWidgetAction',
                    'template' => 'fake',
                ],
            ],
        ]);

        $content = $this->customGroups()->render('fake');

        $this->assertEquals("partial fake foo:bar ", $content);
    }

    /** @test */
    public function canHasOnlyDefault()
    {
        $this->customGroups->setConfig([
            'default' => [
                'label' => 'Add a group',
            ],
        ]);

        $this->assertTrue($this->customGroups()->hasOnlyDefault());
    }

    /** @test */
    public function cannotHasOnlyDefault()
    {
        $this->customGroups->setConfig([
            'default' => [
                'label' => 'Add a group',
            ],
            'fake' => [
                'label' => 'Add a fake group',
            ],
        ]);

        $this->assertFalse($this->customGroups()->hasOnlyDefault());
    }

    /**
     * @return CustomGroups
     */
    protected function customGroups()
    {
        $helper = $this->customGroups;
        return $helper();
    }
}

class FakeWidgetAction extends AbstractWidgetAction
{
    /**
     * @param ViewModel $model
     * @return ViewModel
     */
    public function call(ViewModel $model = null)
    {
        return $model->setVariable('foo', 'bar');
    }
}
