<?php
namespace KmbPuppetTest\Service;

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
                $reports = array(
                    new Report('success', 'node1.local'),
                    new Report('success', 'node2.local'),
                    new Report('success', 'node3.local'),
                    new Report('success', 'node4.local'),
                    new Report('success', 'node5.local'),
                    new Report('success', 'node6.local'),
                    new Report('success', 'node7.local'),
                    new Report('success', 'node8.local'),
                    new Report('success', 'node9.local'),
                );
                if ($query == array(
                        'or',
                        array('~', 'resource-type', 'File'),
                        array('~', 'resource-title', 'File'),
                        array('~', 'message', 'File'),
                        array('~', 'containing-class', 'File'),
                        array('~', 'certname', 'File'),
                    )
                ) {
                    $reports = array_slice($reports, 1, 7);
                }
                if ($orderBy == array(array('field' => 'certname', 'order' => 'desc'))) {
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
        $collection = $this->reportCollector->findAll(array(
            'start' => 3,
            'length' => 5,
        ));

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('node4.local', $collection->get(0)->getNodeName());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(9, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithSearch()
    {
        $collection = $this->reportCollector->findAll(array(
            'start' => 0,
            'length' => 5,
            'search' => array(
                'value' => 'File'
            )
        ));

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('node2.local', $collection->get(0)->getNodeName());
        $this->assertEquals(7, $collection->getTotal());
        $this->assertEquals(7, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithOrdering()
    {
        $collection = $this->reportCollector->findAll(array(
            'start' => 0,
            'length' => 5,
            'order' => array(
                array(
                    'column' => 'certname',
                    'dir' => 'desc',
                ),
            )
        ));

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('node9.local', $collection->get(0)->getNodeName());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(9, $collection->getFilteredCount());
    }
}
