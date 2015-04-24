<?php
namespace KmbPuppetTest\Controller\Plugin;

use KmbPuppet\Controller\Plugin\CustomGroups;

class CustomGroupsTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canHasType()
    {
        $this->assertTrue($this->customGroups()->hasType('fake'));
    }

    /** @test */
    public function cannotHasUnknownType()
    {
        $this->assertFalse($this->customGroups()->hasType('unknown'));
    }

    /** @test */
    public function canGetDefaultClasses()
    {
        $this->assertEquals(['fake-module::config', 'fake-module::service'], $this->customGroups()->defaultClasses('fake'));
    }

    /** @test */
    public function cannotGetDefaultClassesWhenMissing()
    {
        $this->assertEquals([], $this->customGroups()->defaultClasses('test'));
    }

    protected function customGroups()
    {
        $plugin = new CustomGroups();
        $plugin->setConfig([
            'fake' => [
                'label' => 'fake label',
                'description' => 'fake description',
                'required_modules' => ['fake-module'],
                'default_classes' => ['fake-module::config', 'fake-module::config', 'fake-module::service'],
                'widget' => [
                    'action' => 'Fake\Widget\FakeWidgetAction',
                    'template' => 'fake.phtml',
                ],
            ],
            'test' => [
                'label' => 'test label',
                'description' => 'test description',
                'widget' => [
                    'template' => 'test.phtml',
                ],
            ],
        ]);
        return $plugin;
    }
}
