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

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistZendDb\Model\AggregateRootProxyInterface;
use GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface;
use KmbPuppet\Model\EnvironmentProxy;
use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\ServiceManager\ServiceManager;

class EnvironmentProxyFactory implements AggregateRootProxyFactoryInterface
{
    /** @var array */
    protected $config;

    /** @var ServiceManager */
    protected $serviceManager;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return AggregateRootProxyInterface
     */
    public function createProxy(AggregateRootInterface $aggregateRoot)
    {
        $proxy = new EnvironmentProxy();
        $proxy->setAggregateRoot($aggregateRoot);
        /** @var EnvironmentRepositoryInterface $environmentRepository */
        $environmentRepository = $this->serviceManager->get('EnvironmentRepository');
        $proxy->setEnvironmentRepository($environmentRepository);
        return $proxy;
    }

    /**
     * @param $config
     * @return AggregateRootProxyFactoryInterface
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
}
