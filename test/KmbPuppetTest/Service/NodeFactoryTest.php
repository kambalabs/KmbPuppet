<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\Node;
use KmbPuppetTest\Bootstrap;

class NodeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var Node $service */
        $service = Bootstrap::getServiceManager()->get('KmbPuppet\Service\Node');

        $this->assertInstanceOf('KmbPuppet\Service\Node', $service);
        $this->assertInstanceOf('KmbPuppetDb\Service\Node', $service->getNodeService());
        $this->assertInstanceOf('KmbPuppetDb\Query\QueryBuilderInterface', $service->getNodesEnvironmentsQueryBuilder());
        $this->assertInstanceOf('KmbPuppetDb\Query\QueryBuilderInterface', $service->getNodesNamesQueryBuilder());
        $this->assertInstanceOf('KmbPuppet\Service\GroupClassInterface', $service->getGroupClassService());
        $this->assertInternalType('array', $service->getConfig());
    }
}
