<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Revision;
use KmbPuppet\Service\Group;
use KmbPuppetDb\Model\Node;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentService;

    /** @var  Group */
    protected $groupService;

    /** @var  Environment */
    protected $rootEnvironment;

    /** @var  Environment */
    protected $environment;

    protected function setUp()
    {
        $this->environmentService = $this->getMock('KmbPuppet\Service\EnvironmentInterface');
        $this->groupService = new Group();
        $this->groupService->setEnvironmentService($this->environmentService);
        $this->rootEnvironment = new Environment('STABLE');
        $this->environment = new Environment('PF1');
        $this->environment->setParent($this->rootEnvironment);
    }

    /** @test */
    public function canGetAllByNode()
    {
        $this->environmentService->expects($this->any())
            ->method('getByNode')
            ->will($this->returnValue($this->rootEnvironment));
        $rootRevision = new Revision($this->rootEnvironment);
        $defaultGroup = new \KmbDomain\Model\Group('default', '.*');
        $rootRevision->addGroup($defaultGroup);
        $rootRevision->addGroup(new \KmbDomain\Model\Group('empty'));
        $this->rootEnvironment->setCurrentRevision($rootRevision);
        $node = new Node('node1.local');
        $node->setEnvironment('STABLE');

        $groups = $this->groupService->getAllByNode($node);

        $this->assertEquals(['STABLE' => [ $defaultGroup ]], $groups);
    }

    /** @test */
    public function canGetAllByNodeWithParentEnvironment()
    {
        $this->environmentService->expects($this->any())
            ->method('getByNode')
            ->will($this->returnValue($this->environment));
        $defaultGroup = new \KmbDomain\Model\Group('default', '.*');
        $rootRevision = new Revision($this->rootEnvironment);
        $rootRevision->addGroup($defaultGroup);
        $rootRevision->addGroup(new \KmbDomain\Model\Group('empty'));
        $this->rootEnvironment->setCurrentRevision($rootRevision);
        $localGroup = new \KmbDomain\Model\Group('local', '.*\.local');
        $node1Group = new \KmbDomain\Model\Group('node1', 'node1.local');
        $revision = new Revision($this->environment);
        $revision->addGroup($localGroup);
        $revision->addGroup($node1Group);
        $this->environment->setCurrentRevision($revision);
        $node = new Node('node1.local');
        $node->setEnvironment('STABLE_PF1');

        $groups = $this->groupService->getAllByNode($node);

        $this->assertEquals([
            'STABLE_PF1' => [ $localGroup, $node1Group ],
            'STABLE' => [ $defaultGroup ],
        ], $groups);
    }
}
