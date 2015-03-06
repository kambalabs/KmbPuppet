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

use KmbPuppetDb\Model as PuppetDbModel;

class Group implements GroupInterface
{
    /** @var  EnvironmentInterface */
    protected $environmentService;

    /**
     * Get all groups assigned to specified node group by environment name.
     *
     * @param PuppetDbModel\NodeInterface $node
     * @return \KmbDomain\Model\GroupInterface[]
     */
    public function getAllByNode(PuppetDbModel\NodeInterface $node)
    {
        $environment = $this->environmentService->getByNode($node);
        $revision = $environment->getCurrentRevision();
        $groups[$environment->getNormalizedName()] = $revision->getGroupsMatchingHostname($node->getName());
        while ($environment->hasParent()) {
            $parent = $environment->getParent();
            $groups[$parent->getNormalizedName()] = $parent->getCurrentRevision()->getGroupsMatchingHostname($node->getName());
            $environment = $parent;
        }
        return $groups;
    }

    /**
     * Set EnvironmentService.
     *
     * @param EnvironmentInterface $environmentService
     * @return Group
     */
    public function setEnvironmentService($environmentService)
    {
        $this->environmentService = $environmentService;
        return $this;
    }

    /**
     * Get EnvironmentService.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironmentService()
    {
        return $this->environmentService;
    }
}
