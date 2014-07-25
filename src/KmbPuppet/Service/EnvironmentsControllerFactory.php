<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/multimediabs/kamba for the canonical source repository
 *
 * This file is part of KmbPuppet.
 *
 * KmbPuppet is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * KmbPuppet is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KmbPuppet.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppet\Service;

use KmbPuppet\Controller\EnvironmentsController;
use KmbPuppet\Service;
use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class EnvironmentsControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceManager $serviceManager */
        $serviceManager = $serviceLocator->getServiceLocator();

        $controller = new EnvironmentsController();

        /** @var EnvironmentRepositoryInterface $repository */
        $repository = $serviceManager->get('EnvironmentRepository');
        $controller->setRepository($repository);

        /** @var PmProxy $pmProxyService */
        $pmProxyService = $serviceManager->get('KmbPuppet\Service\PmProxy');
        $controller->setPmProxyService($pmProxyService);

        return $controller;
    }
}
