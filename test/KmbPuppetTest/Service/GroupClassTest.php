<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Environment;
use KmbDomain\Model;
use KmbPuppet\Service;
use KmbPuppetDb\Model as PuppetDbModel;

class GroupClassTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Service\GroupClassInterface */
    protected $groupClassService;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    /** @var  PuppetDbModel\NodeInterface */
    protected $node;

    protected function setUp()
    {
        $this->node = new PuppetDbModel\Node('node1.local', null, null, [], 'STABLE_PF1');

        $this->environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');

        $puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $puppetModuleService->expects($this->any())
            ->method('getAllByEnvironment')
            ->will($this->returnValue([]));

        $this->groupClassService = new Service\GroupClass();
        $this->groupClassService->setEnvironmentRepository($this->environmentRepository);
        $this->groupClassService->setRevisionHydrator($this->getMock('KmbPmProxy\Hydrator\RevisionHydratorInterface'));
        $this->groupClassService->setPuppetModuleService($puppetModuleService);
    }

    /** @test */
    public function cannotGetAllReleasedByNodeWhenUnknownEnvironment()
    {
        $this->assertEquals([], $this->groupClassService->getAllReleasedByNode($this->node));
    }

    /** @test */
    public function canGetAllReleasedByNode()
    {
        $stable = new Environment('STABLE');
        $pf1 = new Environment('PF1');
        $pf1->setParent($stable);
        $stable->addChild($pf1);
        $stableGroup = new Model\Group('default', '.*');
        $stableGroup->setClasses([new Model\GroupClass('dns'), new Model\GroupClass('ntp')]);
        $stableRevision = new Model\Revision($stable);
        $stableRevision->setGroups([$stableGroup]);
        $stable->setLastReleasedRevision($stableRevision);
        $pf1Group1 = new Model\Group('default', '.*');
        $pf1Group1->setClasses([new Model\GroupClass('dns')]);
        $pf1Group2 = new Model\Group('other', '.*');
        $pf1Group2->setClasses([new Model\GroupClass('dns'), new Model\GroupClass('xymon')]);
        $pf1Revision = new Model\Revision($pf1);
        $pf1Revision->setGroups([$pf1Group1, $pf1Group2]);
        $pf1->setLastReleasedRevision($pf1Revision);
        $this->environmentRepository->expects($this->any())
            ->method('getRootByName')
            ->will($this->returnValue($stable));

        $classes = $this->groupClassService->getAllReleasedByNode($this->node);

        $this->assertEquals([new Model\GroupClass('dns'), new Model\GroupClass('ntp'), new Model\GroupClass('xymon')], $classes);
    }

    /** @test */
    public function canGetAllCurrentByNode()
    {
        $stable = new Environment('STABLE');
        $pf1 = new Environment('PF1');
        $pf1->setParent($stable);
        $stable->addChild($pf1);
        $stableGroup = new Model\Group('default', '.*');
        $stableGroup->setClasses([new Model\GroupClass('dns'), new Model\GroupClass('ntp')]);
        $stableRevision = new Model\Revision($stable);
        $stableRevision->setGroups([$stableGroup]);
        $stable->setCurrentRevision($stableRevision);
        $pf1Group1 = new Model\Group('default', '.*');
        $pf1Group1->setClasses([new Model\GroupClass('dns')]);
        $pf1Group2 = new Model\Group('other', '.*');
        $pf1Group2->setClasses([new Model\GroupClass('dns'), new Model\GroupClass('xymon')]);
        $pf1Revision = new Model\Revision($pf1);
        $pf1Revision->setGroups([$pf1Group1, $pf1Group2]);
        $pf1->setCurrentRevision($pf1Revision);
        $this->environmentRepository->expects($this->any())
            ->method('getRootByName')
            ->will($this->returnValue($stable));

        $classes = $this->groupClassService->getAllCurrentByNode($this->node);

        $this->assertEquals([new Model\GroupClass('dns'), new Model\GroupClass('ntp'), new Model\GroupClass('xymon')], $classes);
    }
}
