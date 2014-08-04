<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Environment;
use KmbPuppet\Service\ReportCollector;
use KmbPuppetDb\Model\Report;
use KmbPuppetDb\Model\ReportsCollection;

class ReportCollectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ReportCollector */
    protected $reportCollector;

    protected function setUp()
    {
        $this->reportCollector = new ReportCollector();
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
                }
                elseif ($query == [
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
        $this->reportCollector->setReportService($reportService);
    }

    /** @test */
    public function canFindAll()
    {
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
        $parent = new Environment();
        $parent->setName('STABLE');
        $environment = new Environment();
        $environment->setName('PF1');
        $environment->setParent($parent);
        $collection = $this->reportCollector->findAll([
            'start' => 0,
            'length' => 5,
            'search' => [
                'value' => 'File'
            ],
            'environment' => $environment,
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
}
