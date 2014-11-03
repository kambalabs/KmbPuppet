<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/multimediabs/kamba for the canonical source repository
 *
 * This file is part of KmbBase.
 *
 * KmbBase is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * KmbBase is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KmbBase.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppet\Controller\Plugin;

use KmbBase\DateTimeFactoryInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionLog;
use KmbDomain\Model\RevisionRepositoryInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class WriteRevisionLog extends AbstractPlugin
{
    /** @var  AuthenticationService */
    protected $authenticationService;

    /** @var  RevisionRepositoryInterface */
    protected $revisionRepository;

    /** @var  DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /**
     * @param RevisionInterface $revision
     * @param string|string[]   $messages
     * @return WriteRevisionLog
     */
    public function __invoke(RevisionInterface $revision, $messages)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            $log = new RevisionLog($this->dateTimeFactory->now(), $this->authenticationService->getIdentity()->getName(), $message);
            $revision->addLog($log);
        }
        $this->revisionRepository->update($revision);
        return $this;
    }

    /**
     * Set AuthenticationService.
     *
     * @param \Zend\Authentication\AuthenticationService $authenticationService
     * @return WriteRevisionLog
     */
    public function setAuthenticationService($authenticationService)
    {
        $this->authenticationService = $authenticationService;
        return $this;
    }

    /**
     * Get AuthenticationService.
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * Set RevisionRepository.
     *
     * @param \KmbDomain\Model\RevisionRepositoryInterface $revisionRepository
     * @return WriteRevisionLog
     */
    public function setRevisionRepository($revisionRepository)
    {
        $this->revisionRepository = $revisionRepository;
        return $this;
    }

    /**
     * Get RevisionRepository.
     *
     * @return \KmbDomain\Model\RevisionRepositoryInterface
     */
    public function getRevisionRepository()
    {
        return $this->revisionRepository;
    }

    /**
     * Set DateTimeFactory.
     *
     * @param \KmbBase\DateTimeFactoryInterface $dateTimeFactory
     * @return WriteRevisionLog
     */
    public function setDateTimeFactory($dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        return $this;
    }

    /**
     * Get DateTimeFactory.
     *
     * @return \KmbBase\DateTimeFactoryInterface
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }
}
