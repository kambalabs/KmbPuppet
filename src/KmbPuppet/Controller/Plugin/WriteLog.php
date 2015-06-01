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
use KmbDomain\Model\Log;
use KmbDomain\Service\LogRepositoryInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class WriteLog extends AbstractPlugin
{
    /** @var  AuthenticationService */
    protected $authenticationService;

    /** @var  LogRepositoryInterface */
    protected $logRepository;

    /** @var  DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /**
     * @param string|string[] $messages
     * @param string          $user
     * @return WriteRevisionLog
     */
    public function __invoke($messages, $user = null)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            $log = new Log($this->dateTimeFactory->now(), $user ?: $this->authenticationService->getIdentity()->getName(), $message);
            $this->logRepository->add($log);
        }
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
     * @param \KmbDomain\Service\LogRepositoryInterface $logRepository
     * @return WriteLog
     */
    public function setLogRepository($logRepository)
    {
        $this->logRepository = $logRepository;
        return $this;
    }

    /**
     * Get RevisionRepository.
     *
     * @return \KmbDomain\Service\LogRepositoryInterface
     */
    public function getLogRepository()
    {
        return $this->logRepository;
    }

    /**
     * Set DateTimeFactory.
     *
     * @param \KmbBase\DateTimeFactoryInterface $dateTimeFactory
     * @return WriteLog
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
