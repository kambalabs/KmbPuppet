<?php
namespace KmbPuppetTest\View\Helper;

use KmbPuppet\View\Helper\CustomGroups;
use KmbPuppetTest\Bootstrap;

class CustomGroupsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateHelper()
    {
        /** @var CustomGroups $helper */
        $helper = Bootstrap::getServiceManager()->get('ViewHelperManager')->get('customGroups');

        $this->assertInstanceOf('KmbPuppet\View\Helper\CustomGroups', $helper);
        $this->assertInstanceOf('Zend\ServiceManager\ServiceManager', $helper->getServiceLocator());
        $this->assertEquals([
            'default' => [
                'label' => 'Add a group',
                'description' => 'A group allows to configure a set of Puppet classes and assigned them to a list of servers.',
                'widget' => [
                    'template' => 'kmb-puppet/custom-groups/default.group.phtml',
                ],
            ],
            'fake' => [
                'label' => 'fake label',
                'description' => 'fake description',
                'unique' => true,
                'required_modules' => ['fake-module'],
                'widget' => [
                    'action' => 'Fake\Widget\FakeWidgetAction',
                    'template' => 'fake.phtml',
                ],
            ],
        ], $helper->getConfig());
    }
}
