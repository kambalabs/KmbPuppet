<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Environment;
use KmbPuppet\Service\Node;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\Query;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $nodeService;

    /** @var  Node */
    protected $node;

    protected function setUp()
    {
        $this->nodeService = $this->getMock('KmbPuppetDb\Service\NodeInterface');
        $this->node = new Node();
        $this->node->setNodeService($this->nodeService);
        $environmentsQueryBuilder = $this->getMock('KmbPuppetDb\Query\QueryBuilderInterface');
        $environmentsQueryBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnValue(new Query(['=', 'facts-environment', 'STABLE_PF1'])));
        $this->node->setNodesEnvironmentsQueryBuilder($environmentsQueryBuilder);
        $namesQueryBuilder = $this->getMock('KmbPuppetDb\Query\QueryBuilderInterface');
        $namesQueryBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnValue(new Query(['~', 'name', 'node1'])));
        $this->node->setNodesNamesQueryBuilder($namesQueryBuilder);
    }

    /** @test */
    public function canGetAllByEnvironmentAndPatterns()
    {
        $this->nodeService->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue([new Model\Node('node1.local'), new Model\Node('node4.local')]));

        $nodes = $this->node->getAllByEnvironmentAndPatterns(new Environment(), '.*', 'node1.local');

        $this->assertEquals([new Model\Node('node1.local'), new Model\Node('node4.local')], $nodes);
    }

    /** @test */
    public function cannotGetAllByEnvironmentAndPatternsWithoutInclude()
    {
        $this->nodeService->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue([new Model\Node('node1.local'), new Model\Node('node4.local')]));

        $nodes = $this->node->getAllByEnvironmentAndPatterns(new Environment(), '', 'node1.local');

        $this->assertEmpty($nodes);
    }
}
