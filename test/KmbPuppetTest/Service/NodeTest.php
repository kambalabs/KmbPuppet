<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Environment;
use KmbPuppet\Service\Node;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\Query;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $puppetDbNodeService;

    /** @var  Node */
    protected $nodeService;

    protected function setUp()
    {
        $this->puppetDbNodeService = $this->getMock('KmbPuppetDb\Service\NodeInterface');
        $this->nodeService = new Node();
        $this->nodeService->setNodeService($this->puppetDbNodeService);
        $environmentsQueryBuilder = $this->getMock('KmbPuppetDb\Query\QueryBuilderInterface');
        $environmentsQueryBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnValue(new Query(['=', 'facts-environment', 'STABLE_PF1'])));
        $this->nodeService->setNodesEnvironmentsQueryBuilder($environmentsQueryBuilder);
        $namesQueryBuilder = $this->getMock('KmbPuppetDb\Query\QueryBuilderInterface');
        $namesQueryBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnValue(new Query(['~', 'name', 'node1'])));
        $this->nodeService->setNodesNamesQueryBuilder($namesQueryBuilder);
    }

    /** @test */
    public function canGetAllByEnvironmentAndPatterns()
    {
        $this->puppetDbNodeService->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue([new Model\Node('node1.local'), new Model\Node('node4.local')]));

        $nodes = $this->nodeService->getAllByEnvironmentAndPatterns(new Environment(), '.*', 'node1.local');

        $this->assertEquals([new Model\Node('node1.local'), new Model\Node('node4.local')], $nodes);
    }

    /** @test */
    public function cannotGetAllByEnvironmentAndPatternsWithoutInclude()
    {
        $this->puppetDbNodeService->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue([new Model\Node('node1.local'), new Model\Node('node4.local')]));

        $nodes = $this->nodeService->getAllByEnvironmentAndPatterns(new Environment(), '', 'node1.local');

        $this->assertEmpty($nodes);
    }
}
