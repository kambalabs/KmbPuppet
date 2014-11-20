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
namespace KmbPuppet\Service;

use KmbDomain\Model\EnvironmentRepositoryInterface;
use KmbPmProxy\Hydrator\RevisionHydratorInterface;
use KmbPmProxy\Service\PuppetModuleInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GroupClassFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new GroupClass();

        /** @var EnvironmentRepositoryInterface $environmentRepository */
        $environmentRepository = $serviceLocator->get('EnvironmentRepository');
        $service->setEnvironmentRepository($environmentRepository);

        /** @var PuppetModuleInterface $moduleService */
        $moduleService = $serviceLocator->get('pmProxyPuppetModuleService');
        $service->setPuppetModuleService($moduleService);

        /** @var RevisionHydratorInterface $revisionHydrator */
        $revisionHydrator = $serviceLocator->get('pmProxyRevisionHydrator');
        $service->setRevisionHydrator($revisionHydrator);

        return $service;
    }
}
