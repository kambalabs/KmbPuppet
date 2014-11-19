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

use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\EnvironmentRepositoryInterface;
use KmbPuppetDb\Model;
use Zend\Stdlib\ArrayUtils;

class Group implements GroupInterface
{
    /** @var  EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /**
     * @param Model\NodeInterface $node
     * @return \KmbDomain\Model\GroupInterface[]
     */
    public function getAllReleasedByNode(Model\NodeInterface $node)
    {
        $environment = $this->retrieveEnvironment($node);
        return isset($environment) ? $this->retrieveAllGroups($environment, $node) : [];
    }

    /**
     * @param Model\NodeInterface $node
     * @return \KmbDomain\Model\GroupInterface[]
     */
    public function getAllCurrentByNode(Model\NodeInterface $node)
    {
        $environment = $this->retrieveEnvironment($node);
        return isset($environment) ? $this->retrieveAllGroups($environment, $node, false) : [];
    }

    /**
     * @param EnvironmentInterface $environment
     * @param Model\NodeInterface $node
     * @param bool $released
     * @return \KmbDomain\Model\GroupInterface[]
     */
    protected function retrieveAllGroups(EnvironmentInterface $environment, Model\NodeInterface $node, $released = true)
    {
        $revision = $released ? $environment->getLastReleasedRevision() : $environment->getCurrentRevision();
        if ($revision == null) {
            return null;
        }
        $groups = $revision->getGroupsMatchingHostname($node->getName());
        if ($environment->hasParent()) {
            return ArrayUtils::merge($this->retrieveAllGroups($environment->getParent(), $node, $released), $groups);
        }
        return $groups;
    }

    /**
     * @param Model\NodeInterface $node
     * @return EnvironmentInterface
     */
    protected function retrieveEnvironment(Model\NodeInterface $node)
    {
        $names = explode('_', $node->getEnvironment());
        $environmentRootName = array_shift($names);
        $environmentRoot = $this->environmentRepository->getRootByName($environmentRootName);
        if ($environmentRoot == null) {
            return null;
        }
        return $environmentRoot->getDescendantByNormalizedName($node->getEnvironment());
    }

    /**
     * Set EnvironmentRepository.
     *
     * @param \KmbDomain\Model\EnvironmentRepositoryInterface $environmentRepository
     * @return Group
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
}
