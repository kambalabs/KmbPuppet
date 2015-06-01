<?php
namespace KmbPuppetTest\Controller\Plugin;

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistBase\Model\RepositoryInterface;
use KmbBase\FakeDateTimeFactory;
use KmbDomain\Model\Log;
use KmbDomain\Model\LogInterface;
use KmbDomain\Model\User;
use KmbDomain\Service\LogRepositoryInterface;
use KmbPuppet\Controller\Plugin\WriteLog;

class WriteLogTest extends \PHPUnit_Framework_TestCase
{
    /** @var  User */
    protected $user;

    /** @var string[] */
    protected $logs = [];

    /** @var  \DateTime */
    protected $fakeDateTime;

    /** @var  LogRepositoryInterface */
    protected $logRepository;

    protected function setUp()
    {
        $this->user = new User('jdoe', 'John DOE');
        $this->fakeDateTime = new \DateTime('2014-11-03 21:40:00');
        $this->logs = [];
        $this->logRepository = new FakeLogRepository();
    }

    /** @test */
    public function canWriteSingleMessage()
    {
        $log = new Log($this->fakeDateTime, $this->user->getName(), 'Test log');
        $log->setId(1);

        $this->writeLog('Test log');

        $this->assertEquals([$log], $this->logRepository->getAll());
    }

    /** @test */
    public function canWriteMultipleMessages()
    {
        $log1 = new Log($this->fakeDateTime, $this->user->getName(), 'Test log 1');
        $log1->setId(1);
        $log2 = new Log($this->fakeDateTime, $this->user->getName(), 'Test log 2');
        $log2->setId(2);

        $this->writeLog(['Test log 1', 'Test log 2']);

        $this->assertEquals(
            [
                $log1,
                $log2,
            ],
            $this->logRepository->getAll()
        );
    }

    protected function writeLog($messages)
    {
        $authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $authenticationService->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($this->user));

        $plugin = new WriteLog();
        $plugin->setAuthenticationService($authenticationService);
        $plugin->setLogRepository($this->logRepository);
        $plugin->setDateTimeFactory(new FakeDateTimeFactory($this->fakeDateTime));

        return $plugin($messages);
    }
}

class FakeLogRepository implements LogRepositoryInterface
{
    protected $idSequence = 1;
    protected $logs = [];

    /**
     * @param string $search
     * @param int    $offset
     * @param int    $limit
     * @param array  $orderBy
     * @return LogInterface[] $logs, int $filteredCount
     */
    public function getAllPaginated($search, $offset, $limit, $orderBy)
    {
        return $this->logs;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        $aggregateRoot->setId($this->idSequence++);
        $this->logs[] = $aggregateRoot;
        return $this;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->logs;
    }

    /**
     * @return int
     */
    public function size()
    {
        return count($this->logs);
    }

    /**
     * @param $id
     * @return AggregateRootInterface
     */
    public function getById($id)
    {
        return isset($this->logs[$id]) ? $this->logs[$id] : null;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function update(AggregateRootInterface $aggregateRoot)
    {
        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function remove(AggregateRootInterface $aggregateRoot)
    {
        if (isset($this->logs[$aggregateRoot->getId()])) {
            unset($this->logs[$aggregateRoot->getId()]);
        }
        return $this;
    }

    /**
     * @param array $aggregateRoots
     * @return RepositoryInterface
     */
    public function removeAll(array $aggregateRoots = null)
    {
        $this->logs = [];
        return $this;
    }
}
