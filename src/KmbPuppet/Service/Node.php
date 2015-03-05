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

use KmbDomain\Model as DomainModel;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\Query;
use KmbPuppetDb\Query\QueryBuilderInterface;
use Zend\Stdlib\ArrayUtils;

class Node implements NodeInterface
{
    /** @var  \KmbPuppetDb\Service\NodeInterface */
    protected $nodeService;

    /** @var  QueryBuilderInterface */
    protected $nodesEnvironmentsQueryBuilder;

    /** @var  QueryBuilderInterface */
    protected $nodesNamesQueryBuilder;

    /** @var  array */
    protected $config;

    /** @var  GroupClass */
    protected $groupClassService;

    /**
     * @param DomainModel\EnvironmentInterface $environment
     * @param string               $include
     * @param string               $exclude
     * @return array
     */
    public function getAllByEnvironmentAndPatterns(DomainModel\EnvironmentInterface $environment, $include, $exclude)
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
     * @param Model\NodeInterface $node
     * @return array
     */
    public function getActivePuppetConfiguration(Model\NodeInterface $node)
    {
        return $this->dump($this->groupClassService->getAllReleasedByNode($node), $node->getEnvironment());
    }

    /**
     * @param Model\NodeInterface $node
     * @return array
     */
    public function getCurrentPuppetConfiguration(Model\NodeInterface $node)
    {
        return $this->dump($this->groupClassService->getAllCurrentByNode($node), $node->getEnvironment());
    }

    /**
     * @param DomainModel\GroupClassInterface[] $classes
     * @param string $environment
     * @return array
     */
    protected function dump($classes, $environment)
    {
        $dump = [];
        if (!empty($classes)) {
            foreach ($classes as $class) {
                $dump = ArrayUtils::merge($dump, [$class->getName() => $class->dump()]);
            }
        }
        ksort($dump, SORT_STRING);
        $dump = [
            'classes' => $dump,
            'parameters' => [
                'enc_id' => isset($this->config['puppet']['enc_id']) ? $this->config['puppet']['enc_id'] : 'production',
            ],
            'environment' => $environment,
        ];
        return $dump;
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

    /**
     * Set Config.
     *
     * @param array $config
     * @return Node
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get Config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set GroupClassService.
     *
     * @param \KmbPuppet\Service\GroupClass $groupClassService
     * @return Node
     */
    public function setGroupClassService($groupClassService)
    {
        $this->groupClassService = $groupClassService;
        return $this;
    }

    /**
     * Get GroupClassService.
     *
     * @return \KmbPuppet\Service\GroupClass
     */
    public function getGroupClassService()
    {
        return $this->groupClassService;
    }
}
