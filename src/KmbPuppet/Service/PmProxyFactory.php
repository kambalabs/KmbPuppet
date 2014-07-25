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

use Zend\Http;
use Zend\Log\Logger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class PmProxyFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $globalConfig = $serviceLocator->get('Config');
        $config = isset($globalConfig['pmproxy']) ? $globalConfig['pmproxy'] : array();

        $service = new PmProxy();

        /** @var Http\Client $httpClient */
        $httpClient = $serviceLocator->get('KmbPuppet\Http\Client');
        if (isset($config['http_options'])) {
            $httpClient->setOptions($config['http_options']);
        }
        $service->setHttpClient($httpClient);

        $service->setBaseUri($config['base_uri']);

        /** @var HydratorInterface $environmentHydrator */
        $environmentHydrator = $serviceLocator->get('KmbPuppet\Model\PmProxy\EnvironmentHydrator');
        $service->setEnvironmentHydrator($environmentHydrator);

        /** @var Logger $logger */
        $logger = $serviceLocator->get('Logger');
        $service->setLogger($logger);

        return $service;
    }
}
