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
use KmbDomain\Service\LogRepositoryInterface;
use KmbPuppetDb\Service;

class LogCollector implements CollectorInterface
{
    /** @var LogRepositoryInterface */
    protected $logRepository;

    /**
     * @param array $params
     * @return Collection
     */
    public function findAll(array $params = null)
    {
        $offset = isset($params['start']) ? $params['start'] : null;
        $limit = isset($params['length']) ? $params['length'] : null;

        $search = isset($params['search']['value']) && !empty($params['search']['value']) ? $params['search']['value'] : null;
        $orderBy = isset($params['order']) ? $params['order'] : null;
        list($logs, $filteredCount) = $this->logRepository->getAllPaginated($search, $offset, $limit, $orderBy);

        return Collection::factory($logs, $this->logRepository->size(), $filteredCount);
    }

    /**
     * Set LogRepository.
     *
     * @param \KmbDomain\Service\LogRepositoryInterface $logRepository
     * @return LogCollector
     */
    public function setLogRepository($logRepository)
    {
        $this->logRepository = $logRepository;
        return $this;
    }

    /**
     * Get LogRepository.
     *
     * @return \KmbDomain\Service\LogRepositoryInterface
     */
    public function getLogRepository()
    {
        return $this->logRepository;
    }
}
