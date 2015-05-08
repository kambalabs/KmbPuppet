<?php
namespace KmbPuppetTest\Controller\Plugin;

use KmbPuppet\Controller\Plugin\WriteRevisionLog;
use KmbBase\FakeDateTimeFactory;
use KmbDomain\Model\Revision;
use KmbDomain\Model\RevisionLog;
use KmbDomain\Model\User;

class WriteRevisionLogTest extends \PHPUnit_Framework_TestCase
{
    /** @var  User */
    protected $user;

    /** @var string[] */
    protected $logs = [];

    /** @var  \DateTime */
    protected $fakeDateTime;

    protected function setUp()
    {
        $this->user = new User('jdoe', 'John DOE');
        $this->fakeDateTime = new \DateTime('2014-11-03 21:40:00');
        $this->logs = [];
    }

    /** @test */
    public function canWriteSingleMessage()
    {
        $revision = new Revision();

        $this->writeRevisionLog($revision, 'Test log');

        $this->assertEquals([new RevisionLog($this->fakeDateTime, $this->user->getName(), 'Test log')], $revision->getLogs());
    }

    /** @test */
    public function canWriteMultipleMessages()
    {
        $revision = new Revision();

        $this->writeRevisionLog($revision, ['Test log 1', 'Test log 2']);

        $this->assertEquals(
            [
                new RevisionLog($this->fakeDateTime, $this->user->getName(), 'Test log 1'),
                new RevisionLog($this->fakeDateTime, $this->user->getName(), 'Test log 2'),
            ],
            $revision->getLogs());
    }

    protected function writeRevisionLog($revision, $messages)
    {
        $authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $authenticationService->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($this->user));

        $revisionRepository = $this->getMock('KmbDomain\Service\RevisionRepositoryInterface');
        $revisionRepository->expects($this->once())
            ->method('update');

        $plugin = new WriteRevisionLog();
        $plugin->setAuthenticationService($authenticationService);
        $plugin->setRevisionRepository($revisionRepository);
        $plugin->setDateTimeFactory(new FakeDateTimeFactory($this->fakeDateTime));

        return $plugin($revision, $messages);
    }
}
