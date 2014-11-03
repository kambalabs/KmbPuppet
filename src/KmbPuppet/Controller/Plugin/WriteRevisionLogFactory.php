<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppet\Controller\Plugin;

use KmbBase\DateTimeFactoryInterface;
use KmbDomain\Model\RevisionRepositoryInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class WriteRevisionLogFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var PluginManager $serviceLocator */
        /** @var ServiceManager $serviceManager */
        $serviceManager = $serviceLocator->getServiceLocator();

        $writeRevisionLog = new WriteRevisionLog();

        /** @var AuthenticationService $authenticationService */
        $authenticationService = $serviceManager->get('Zend\Authentication\AuthenticationService');
        $writeRevisionLog->setAuthenticationService($authenticationService);

        /** @var RevisionRepositoryInterface $revisionRepository */
        $revisionRepository = $serviceManager->get('RevisionRepository');
        $writeRevisionLog->setRevisionRepository($revisionRepository);

        /** @var DateTimeFactoryInterface $dateTimeFactory */
        $dateTimeFactory = $serviceManager->get('DateTimeFactory');
        $writeRevisionLog->setDateTimeFactory($dateTimeFactory);

        return $writeRevisionLog;
    }
}
