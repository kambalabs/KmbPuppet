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
namespace KmbPuppet\View\Helper;

use KmbDomain\Model\EnvironmentRepositoryInterface;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Helper\AbstractHelper;

class EnvironmentSelect extends AbstractHelper
{
    /** @var EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /** @var RouteMatch */
    protected $routeMatch;

    public function __invoke($permission = 'manageEnvChildren')
    {
        $environments = $this->environmentRepository->getAllRoots();
        return $this->getView()->partial('kmb-puppet/environments/environments-options', [
            'environments' => $environments,
            'permission' => $permission,
            'envId' => $this->routeMatch !== null ? $this->routeMatch->getParam('envId', null) : null,
        ]);
    }

    /**
     * Set EnvironmentRepository.
     *
     * @param EnvironmentRepositoryInterface $environmentRepository
     * @return EnvironmentSelect
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }

    /**
     * Set RouteMatch.
     *
     * @param RouteMatch $routeMatch
     * @return EnvironmentSelect
     */
    public function setRouteMatch($routeMatch)
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }

    /**
     * Get RouteMatch.
     *
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }
}
