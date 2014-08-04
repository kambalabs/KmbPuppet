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
use KmbDomain\Model\EnvironmentInterface;
use KmbPuppetDb\Service;

class ReportCollector implements CollectorInterface
{
    /** @var Service\Report */
    protected $reportService;

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

        $queryEnvironment = null;
        if (isset($params['environment'])) {
            /** @var EnvironmentInterface $environment */
            $environment = $params['environment'];
            $queryEnvironment = ['=', 'environment', $environment->getNormalizedName()];
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
}
