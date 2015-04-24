<?php
namespace KmbPuppetTest\Controller\Plugin;

use KmbPuppet\Controller\Plugin\CustomGroups;
use KmbPuppetTest\Bootstrap;

class CustomGroupsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreatePlugin()
    {
        /** @var CustomGroups $plugin */
        $plugin = Bootstrap::getServiceManager()->get('ControllerPluginManager')->get('customGroups');

        $this->assertInstanceOf('KmbPuppet\Controller\Plugin\CustomGroups', $plugin);
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
                'required_modules' => ['fake-module'],
                'widget' => [
                    'action' => 'Fake\Widget\FakeWidgetAction',
                    'template' => 'fake.phtml',
                ],
            ],
        ], $plugin->getConfig());
    }
}
