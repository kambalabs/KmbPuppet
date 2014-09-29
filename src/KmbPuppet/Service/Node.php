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
use KmbPuppetDb\Query\Query;
use KmbPuppetDb\Query\QueryBuilderInterface;

class Node implements NodeInterface
{
    /** @var  \KmbPuppetDb\Service\NodeInterface */
    protected $nodeService;

    /** @var  QueryBuilderInterface */
    protected $nodesEnvironmentsQueryBuilder;

    /** @var  QueryBuilderInterface */
    protected $nodesNamesQueryBuilder;

    /**
     * @param EnvironmentInterface $environment
     * @param string               $include
     * @param string               $exclude
     * @return array
     */
    public function getAllByEnvironmentAndPatterns(EnvironmentInterface $environment, $include, $exclude)
    {
        if (empty($include)) {
            return [];
        }

        $environments = $environment->getDescendants();
        array_unshift($environments, $environment);
        $environmentQuery = $this->nodesEnvironmentsQueryBuilder->build($environments);

        $includeQuery = $this->nodesNamesQueryBuilder->build([$include], '~');
        $excludeQuery = $this->nodesNamesQueryBuilder->build([$exclude], '~');
        $patternQuery = $excludeQuery == null ? $includeQuery : new Query(['AND', $includeQuery, new Query(['NOT', $excludeQuery])]);

        return $this->nodeService->getAll(new Query(['AND', $environmentQuery, $patternQuery]));
    }

    /**
     * Set NodeService.
     *
     * @param \KmbPuppetDb\Service\NodeInterface $nodeService
     * @return Node
     */
    public function setNodeService($nodeService)
    {
        $this->nodeService = $nodeService;
        return $this;
    }

    /**
     * Get NodeService.
     *
     * @return \KmbPuppetDb\Service\NodeInterface
     */
    public function getNodeService()
    {
        return $this->nodeService;
    }

    /**
     * Set EnvironmentsQueryBuilder.
     *
     * @param \KmbPuppetDb\Query\QueryBuilderInterface $nodesEnvironmentsQueryBuilder
     * @return Node
     */
    public function setNodesEnvironmentsQueryBuilder($nodesEnvironmentsQueryBuilder)
    {
        $this->nodesEnvironmentsQueryBuilder = $nodesEnvironmentsQueryBuilder;
        return $this;
    }

    /**
     * Get EnvironmentsQueryBuilder.
     *
     * @return \KmbPuppetDb\Query\QueryBuilderInterface
     */
    public function getNodesEnvironmentsQueryBuilder()
    {
        return $this->nodesEnvironmentsQueryBuilder;
    }

    /**
     * Set NodesNamesQueryBuilder.
     *
     * @param \KmbPuppetDb\Query\QueryBuilderInterface $nodesNamesQueryBuilder
     * @return Node
     */
    public function setNodesNamesQueryBuilder($nodesNamesQueryBuilder)
    {
        $this->nodesNamesQueryBuilder = $nodesNamesQueryBuilder;
        return $this;
    }

    /**
     * Get NodesNamesQueryBuilder.
     *
     * @return \KmbPuppetDb\Query\QueryBuilderInterface
     */
    public function getNodesNamesQueryBuilder()
    {
        return $this->nodesNamesQueryBuilder;
    }
}
