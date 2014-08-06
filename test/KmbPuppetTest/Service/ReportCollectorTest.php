<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPuppet\Service\ReportCollector;
use KmbPuppetDb\Model\Report;
use KmbPuppetDb\Model\ReportsCollection;
use KmbPuppetDb\Query\ReportsV4EnvironmentsQueryBuilder;

class ReportCollectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $permissionEnvironmentService;

    /** @var ReportCollector */
    protected $reportCollector;

    /** @var EnvironmentInterface */
    protected $environment;

    protected function setUp()
    {
        $parent = new Environment();
        $parent->setName('STABLE');
        $this->environment = new Environment();
        $this->environment->setName('PF1');
        $this->environment->setParent($parent);

        $this->reportCollector = new ReportCollector();

        $reportsEnvironmentsQueryBuilder = new ReportsV4EnvironmentsQueryBuilder();
        $this->reportCollector->setReportsEnvironmentsQueryBuilder($reportsEnvironmentsQueryBuilder);

        $this->permissionEnvironmentService = $this->getMock('KmbPermission\Service\Environment');
        $this->reportCollector->setPermissionEnvironmentService($this->permissionEnvironmentService);

        $this->reportCollector->setReportService($this->getReportServiceMock());
    }

    /** @test */
    public function canFindAllForRoot()
    {
        $this->permissionEnvironmentService->expects($this->any())
            ->method('getAllReadable')
            ->will($this->returnValue([]));
        $collection = $this->reportCollector->findAll([
            'start' => 3,
            'length' => 5,
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('node4.local', $collection->get(0)->getNodeName());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(9, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllForNonRoot()
    {
        $this->permissionEnvironmentService->expects($this->any())
            ->method('getAllReadable')
            ->will($this->returnValue([$this->environment]));
        $collection = $this->reportCollector->findAll([
            'start' => 0,
            'length' => 5,
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(2, count($collection->getData()));
        $this->assertEquals(2, $collection->getTotal());
        $this->assertEquals(2, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithSearch()
    {
        $collection = $this->reportCollector->findAll([
            'start' => 0,
            'length' => 5,
            'search' => [
                'value' => 'File'
            ]
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('node2.local', $collection->get(0)->getNodeName());
        $this->assertEquals(7, $collection->getTotal());
        $this->assertEquals(7, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithSearchAndEnvironment()
    {
        $this->permissionEnvironmentService->expects($this->any())
            ->method('getAllReadable')
            ->will($this->returnValue([$this->environment]));
        $collection = $this->reportCollector->findAll([
            'start' => 0,
            'length' => 5,
            'search' => [
                'value' => 'File'
            ],
            'environment' => $this->environment,
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(4, count($collection));
        $this->assertEquals('node4.local', $collection->get(0)->getNodeName());
        $this->assertEquals(4, $collection->getTotal());
        $this->assertEquals(4, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithOrdering()
    {
        $collection = $this->reportCollector->findAll([
            'start' => 0,
            'length' => 5,
            'order' => [
                [
                    'column' => 'certname',
                    'dir' => 'desc',
                ],
            ]
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('node9.local', $collection->get(0)->getNodeName());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(9, $collection->getFilteredCount());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getReportServiceMock()
    {
        $reportService = $this->getMock('KmbPuppetDb\Service\Report');
        $reportService->expects($this->any())
            ->method('getAllForToday')
            ->will($this->returnCallback(function ($query = null, $offset = null, $limit = null, $orderBy = null) {
                $reports = [
                    new Report('success', 'node1.local'),
                    new Report('success', 'node2.local'),
                    new Report('success', 'node3.local'),
                    new Report('success', 'node4.local'),
                    new Report('success', 'node5.local'),
                    new Report('success', 'node6.local'),
                    new Report('success', 'node7.local'),
                    new Report('success', 'node8.local'),
                    new Report('success', 'node9.local'),
                ];
                if ($query == [
                        'or',
                        ['~', 'resource-type', 'File'],
                        ['~', 'resource-title', 'File'],
                        ['~', 'message', 'File'],
                        ['~', 'containing-class', 'File'],
                        ['~', 'certname', 'File'],
                    ]
                ) {
                    $reports = array_slice($reports, 1, 7);
                } elseif ($query == ['=', 'environment', 'STABLE_PF1']) {
                    $reports = array_slice($reports, 3, 2);
                } elseif ($query == [
                        'and',
                        [
                            'or',
                            ['~', 'resource-type', 'File'],
                            ['~', 'resource-title', 'File'],
                            ['~', 'message', 'File'],
                            ['~', 'containing-class', 'File'],
                            ['~', 'certname', 'File'],
                        ],
                        ['=', 'environment', 'STABLE_PF1'],
                    ]
                ) {
                    $reports = array_slice($reports, 3, 4);
                }
                if ($orderBy == [['field' => 'certname', 'order' => 'desc']]) {
                    usort($reports, function (Report $a, Report $b) {
                        if ($a->getNodeName() === $b->getNodeName()) {
                            return 0;
                        }
                        if ($a->getNodeName() > $b->getNodeName()) {
                            return -1;
                        }
                        return 1;
                    });
                }
                $total = count($reports);
                return ReportsCollection::factory(
                    array_slice($reports, $offset, $limit),
                    $total,
                    $total
                );
            }));
        return $reportService;
    }
}
