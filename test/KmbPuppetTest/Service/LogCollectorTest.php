<?php
namespace KmbPuppetTest\Service;

use KmbDomain\Model\Log;
use KmbPuppet\Service\LogCollector;

class LogCollectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var LogCollector */
    protected $logCollector;

    protected function setUp()
    {
        $this->logCollector = new LogCollector();
        $this->logCollector->setLogRepository($this->getLogRepositoryMock());
    }

    /** @test */
    public function canFindAll()
    {
        $collection = $this->logCollector->findAll([
            'start' => 3,
            'length' => 5,
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('Fake comment 5', $collection->get(0)->getComment());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(9, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithSearch()
    {
        $collection = $this->logCollector->findAll([
            'start' => 0,
            'length' => 5,
            'search' => [
                'value' => 'Fake'
            ]
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('Fake comment 2', $collection->get(0)->getComment());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(6, $collection->getFilteredCount());
    }

    /** @test */
    public function canFindAllWithOrdering()
    {
        $collection = $this->logCollector->findAll([
            'start' => 0,
            'length' => 5,
            'order' => [
                [
                    'column' => 'comment',
                    'dir' => 'asc',
                ],
            ]
        ]);

        $this->assertInstanceOf('GtnDataTables\Model\Collection', $collection);
        $this->assertEquals(5, count($collection));
        $this->assertEquals('Fake comment 1', $collection->get(0)->getComment());
        $this->assertEquals(9, $collection->getTotal());
        $this->assertEquals(9, $collection->getFilteredCount());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLogRepositoryMock()
    {
        $logRepository = $this->getMock('KmbDomain\Service\LogRepositoryInterface');
        $logRepository->expects($this->any())
            ->method('getAllPaginated')
            ->will($this->returnCallback(function ($search = null, $offset = null, $limit = null, $orderBy = null) {
                $now = new \DateTime();
                $logs = [
                    new Log($now, 'John DOE', 'Fake comment 3'),
                    new Log($now, 'John DOE', 'Fake comment 2'),
                    new Log($now, 'John DOE', 'Fake comment 1'),
                    new Log($now, 'John DOE', 'Fake comment 5'),
                    new Log($now, 'John DOE', 'Fake comment 4'),
                    new Log($now, 'John DOE', 'Fake comment 6'),
                    new Log($now, 'John DOE', 'Fake comment 8'),
                    new Log($now, 'John DOE', 'Fake comment 9'),
                    new Log($now, 'John DOE', 'Fake comment 7'),
                ];
                if (!empty($orderBy)) {
                    usort($logs, function (Log $a, Log $b) {
                        if ($a->getComment() === $b->getComment()) {
                            return 0;
                        }
                        if ($a->getComment() > $b->getComment()) {
                            return 1;
                        }
                        return -1;
                    });
                }
                if (!empty($search)) {
                    $logs = array_slice($logs, 1, 6);
                }
                $filteredCount = count($logs);
                return [array_slice($logs, $offset, $limit), $filteredCount];
            }));
        $logRepository->expects($this->any())
            ->method('size')
            ->will($this->returnValue(9));
        return $logRepository;
    }
}
