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

use KmbDomain\Model;
use KmbPmProxy\Hydrator\RevisionHydratorInterface;
use KmbPmProxy\Service;
use KmbPuppetDb\Model as PuppetDbModel;

class GroupClass implements GroupClassInterface
{
    /** @var  Model\EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /** @var  RevisionHydratorInterface */
    protected $revisionHydrator;

    /** @var  Service\PuppetModuleInterface */
    protected $puppetModuleService;

    /**
     * @param PuppetDbModel\NodeInterface $node
     * @return \KmbDomain\Model\GroupClassInterface[]
     */
    public function getAllReleasedByNode(PuppetDbModel\NodeInterface $node)
    {
        $environment = $this->findEnvironment($node);
        return $environment != null ? $this->findClasses($node, $environment) : [];
    }

    /**
     * @param PuppetDbModel\NodeInterface $node
     * @return \KmbDomain\Model\GroupClassInterface[]
     */
    public function getAllCurrentByNode(PuppetDbModel\NodeInterface $node)
    {
        $environment = $this->findEnvironment($node);
        return $environment != null ? $this->findClasses($node, $environment, false) : [];
    }

    /**
     * @param PuppetDbModel\NodeInterface $node
     * @param Model\EnvironmentInterface  $environment
     * @param bool                        $isForLastReleased
     * @return \KmbDomain\Model\GroupClassInterface[]
     */
    protected function findClasses(PuppetDbModel\NodeInterface $node, Model\EnvironmentInterface $environment, $isForLastReleased = true)
    {
        $revision = $isForLastReleased ? $environment->getLastReleasedRevision() : $environment->getCurrentRevision();
        $modules = $this->puppetModuleService->getAllInstalledByEnvironment($environment);
        $this->revisionHydrator->hydrate($modules, $revision);
        $groups = $revision->getGroupsMatchingHostname($node->getName());
        $classes = [];
        if ($environment->hasParent()) {
            $parentClasses = $this->findClasses($node, $environment->getParent(), $isForLastReleased);
            foreach ($parentClasses as $class) {
                $classes[$class->getName()] = $class;
            }
        }
        foreach ($groups as $group) {
            foreach ($group->getClasses() as $class) {
                $classes[$class->getName()] = $class;
            }
        }
        return array_values($classes);
    }

    /**
     * @param PuppetDbModel\NodeInterface $node
     * @return Model\EnvironmentInterface
     */
    protected function findEnvironment($node)
    {
        $names = explode('_', $node->getEnvironment());
        $environmentRootName = array_shift($names);
        $environmentRoot = $this->environmentRepository->getRootByName($environmentRootName);
        $environment = $environmentRoot != null ? $environmentRoot->getDescendantByNormalizedName($node->getEnvironment()) : null;
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
     * @param \KmbDomain\Model\EnvironmentRepositoryInterface $environmentRepository
     * @return GroupClass
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return \KmbDomain\Model\EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }

    /**
     * Set PuppetModuleService.
     *
     * @param \KmbPmProxy\Service\PuppetModuleInterface $puppetModuleService
     * @return GroupClass
     */
    public function setPuppetModuleService($puppetModuleService)
    {
        $this->puppetModuleService = $puppetModuleService;
        return $this;
    }

    /**
     * Get PuppetModuleService.
     *
     * @return \KmbPmProxy\Service\PuppetModuleInterface
     */
    public function getPuppetModuleService()
    {
        return $this->puppetModuleService;
    }

    /**
     * Set RevisionHydrator.
     *
     * @param \KmbPmProxy\Hydrator\RevisionHydratorInterface $revisionHydrator
     * @return GroupClass
     */
    public function setRevisionHydrator($revisionHydrator)
    {
        $this->revisionHydrator = $revisionHydrator;
        return $this;
    }

    /**
     * Get RevisionHydrator.
     *
     * @return \KmbPmProxy\Hydrator\RevisionHydratorInterface
     */
    public function getRevisionHydrator()
    {
        return $this->revisionHydrator;
    }
}
