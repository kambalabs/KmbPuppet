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

use KmbDomain\Service\EnvironmentRepositoryInterface;
use KmbDomain\Model as DomainModel;
use KmbPuppetDb\Model as PuppetDbModel;

class Environment implements EnvironmentInterface
{
    /** @var  EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /**
     * @param PuppetDbModel\NodeInterface $node
     * @return DomainModel\EnvironmentInterface
     */
    public function getByNode(PuppetDbModel\NodeInterface $node)
    {
        $environment = $this->environmentRepository->getByNormalizedName($node->getEnvironment());
        if ($environment == null) {
            $defaultEnvironment = $this->environmentRepository->getDefault();
            $node->setEnvironment($defaultEnvironment->getNormalizedName());
            return $defaultEnvironment;
        }
        return $environment;
    }

    /**
     * Set EnvironmentRepository.
     *
     * @param \KmbDomain\Service\EnvironmentRepositoryInterface $environmentRepository
     * @return Environment
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return \KmbDomain\Service\EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }
}
