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

use KmbPermission\Service\EnvironmentInterface;
use KmbPuppetDb\Query\QueryBuilderInterface;
use KmbPuppetDb\Service;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReportCollectorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new ReportCollector();

        /** @var Service\Report $reportService */
        $reportService = $serviceLocator->get('KmbPuppetDb\Service\Report');
        $service->setReportService($reportService);

        /** @var QueryBuilderInterface $reportsEnvironmentsQueryBuilder */
        $reportsEnvironmentsQueryBuilder = $serviceLocator->get('KmbPuppetDb\Query\ReportsEnvironmentsQueryBuilder');
        $service->setReportsEnvironmentsQueryBuilder($reportsEnvironmentsQueryBuilder);

        /** @var EnvironmentInterface $permissionEnvironmentService */
        $permissionEnvironmentService = $serviceLocator->get('KmbPermission\Service\Environment');
        $service->setPermissionEnvironmentService($permissionEnvironmentService);

        return $service;
    }
}
