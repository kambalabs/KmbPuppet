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

use GtnDataTables\Model\Collection;
use GtnDataTables\Service\CollectorInterface;
use KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface;
use KmbPuppetDb\Service;

class ReportCollector implements CollectorInterface
{
    /** @var Service\Report */
    protected $reportService;

    /** @var EnvironmentsQueryBuilderInterface */
    protected $reportsEnvironmentsQueryBuilder;

    /** @var \KmbPermission\Service\EnvironmentInterface */
    protected $permissionEnvironmentService;

    /**
     * @param array $params
     * @return Collection
     */
    public function findAll(array $params = null)
    {
        $offset = isset($params['start']) ? $params['start'] : null;
        $limit = isset($params['length']) ? $params['length'] : null;

        $querySearch = null;
        if (isset($params['search']['value']) && !empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $querySearch = [
                'or',
                ['~', 'resource-type', $search],
                ['~', 'resource-title', $search],
                ['~', 'message', $search],
                ['~', 'containing-class', $search],
                ['~', 'certname', $search],
            ];
        }

        $environments = $this->permissionEnvironmentService->getAllReadable(isset($params['environment']) ? $params['environment'] : null);
        $queryEnvironment = null;
        if (!empty($environments)) {
            $queryEnvironment = $this->reportsEnvironmentsQueryBuilder->build($environments)->getData();
        }

        $query = array_filter([$querySearch, $queryEnvironment]);
        if (count($query) > 1) {
            array_unshift($query, 'and');
        } else {
            $query = array_shift($query);
        }

        $orderBy = [];
        if (isset($params['order'])) {
            foreach ($params['order'] as $clause) {
                $orderBy[] = [
                    'field' => $clause['column'],
                    'order' => $clause['dir'],
                ];
            }
        }

        $reports = $this->getReportService()->getAllForToday($query, $offset, $limit, $orderBy);

        return Collection::factory($reports->getData(), $reports->getTotal(), $reports->getTotal());
    }

    /**
     * Get ReportService.
     *
     * @return \KmbPuppetDb\Service\Report
     */
    public function getReportService()
    {
        return $this->reportService;
    }

    /**
     * Set ReportService.
     *
     * @param \KmbPuppetDb\Service\Report $reportService
     * @return ReportCollector
     */
    public function setReportService($reportService)
    {
        $this->reportService = $reportService;
        return $this;
    }

    /**
     * Set ReportsEnvironmentsQueryBuilder.
     *
     * @param \KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface $reportsEnvironmentsQueryBuilder
     * @return ReportCollector
     */
    public function setReportsEnvironmentsQueryBuilder($reportsEnvironmentsQueryBuilder)
    {
        $this->reportsEnvironmentsQueryBuilder = $reportsEnvironmentsQueryBuilder;
        return $this;
    }

    /**
     * Get ReportsEnvironmentsQueryBuilder.
     *
     * @return \KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface
     */
    public function getReportsEnvironmentsQueryBuilder()
    {
        return $this->reportsEnvironmentsQueryBuilder;
    }

    /**
     * Set PermissionEnvironmentService.
     *
     * @param \KmbPermission\Service\EnvironmentInterface $permissionEnvironmentService
     * @return ReportCollector
     */
    public function setPermissionEnvironmentService($permissionEnvironmentService)
    {
        $this->permissionEnvironmentService = $permissionEnvironmentService;
        return $this;
    }

    /**
     * Get PermissionEnvironmentService.
     *
     * @return \KmbPermission\Service\EnvironmentInterface
     */
    public function getPermissionEnvironmentService()
    {
        return $this->permissionEnvironmentService;
    }
}
