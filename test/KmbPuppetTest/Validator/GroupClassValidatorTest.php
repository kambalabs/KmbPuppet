<?php
namespace KmbPuppetTest\Validator;

use KmbDomain\Model\GroupClass;
use KmbDomain\Model\GroupParameter;
use KmbPuppet\Validator\GroupClassValidator;

class GroupClassValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canValidateClassWithUnknownParameter()
    {
        $class = new GroupClass('apache::vhost');
        $parameter = new GroupParameter('host', ['node1.local']);
        $class->addParameter($parameter);
        $validator = new GroupClassValidator();

        $this->assertFalse($validator->isValid($class));
        $this->assertEquals(['parameters' => ['host' => [ 'global' => "This parameter does not exists in the class template"]]], $validator->getMessages());
    }

    /** @test */
    public function canValidateClassWithMissingRequiredParameter()
    {
        $class = new GroupClass('apache::vhost');
        $class->setAvailableParameters([(object)['name' => 'host', 'required' => true]]);
        $validator = new GroupClassValidator();

        $this->assertFalse($validator->isValid($class));
        $this->assertEquals(['global' => "Following parameters are required : host"], $validator->getMessages());
    }

    /** @test */
    public function canValidateClassWithHashtableParameterWithUnknownChild()
    {
        $class = new GroupClass('apache::vhost');
        $parameter = new GroupParameter('vhost');
        $parameter->setTemplate((object)['name' => 'vhost']);
        $parameter->addChild(new GroupParameter('ServerName'));
        $class->addParameter($parameter);
        $validator = new GroupClassValidator();

        $this->assertFalse($validator->isValid($class));
        $this->assertEquals([
            'parameters' => [
                'vhost' => [
                    'parameters' => [
                        'ServerName' => [
                            'global' => "This parameter does not exists in the class template",
                        ],
                    ],
                ],
            ],
        ], $validator->getMessages());
    }

    /** @test */
    public function canValidateClassWithHashtableParameterWithMissingRequiredChild()
    {
        $class = new GroupClass('apache::vhost');
        $parameter = new GroupParameter('vhost');
        $parameter->setTemplate((object)['name' => 'vhost']);
        $parameter->setAvailableChildren([(object)['name' => 'ServerName', 'required' => true], (object)['name' => 'Port', 'required' => true]]);
        $class->addParameter($parameter);
        $validator = new GroupClassValidator();

        $this->assertFalse($validator->isValid($class));
        $this->assertEquals([
            'parameters' => [
                'vhost' => [
                    'global' => "Following parameters are required : ServerName, Port",
                ],
            ],
        ], $validator->getMessages());
    }

    /** @test */
    public function canValidateValidClass()
    {
        $class = new GroupClass('apache::vhost');
        $parameter = new GroupParameter('vhost');
        $parameter->setTemplate((object)['name' => 'vhost']);
        $child = new GroupParameter('ServerName', ['node1.local']);
        $child->setTemplate((object)['name' => 'ServerName', 'required' => true]);
        $parameter->addChild($child);
        $class->addParameter($parameter);
        $validator = new GroupClassValidator();

        $this->assertTrue($validator->isValid($class));
    }
}
