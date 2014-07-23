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
namespace KmbPuppet\Infrastructure\ZendDb;

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistZendDb\Infrastructure\ZendDbRepository;
use KmbPuppet\Model\EnvironmentInterface;
use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\Db\Sql\Select;

class EnvironmentRepository extends ZendDbRepository implements EnvironmentRepositoryInterface
{
    /** @var string */
    protected $pathsTableName;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return \GtnPersistBase\Model\RepositoryInterface
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        parent::add($aggregateRoot);
        return $this->addPaths($aggregateRoot);
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return \GtnPersistBase\Model\RepositoryInterface
     */
    public function update(AggregateRootInterface $aggregateRoot)
    {
        parent::update($aggregateRoot);
        return $this->removePaths($aggregateRoot)->addPaths($aggregateRoot);

    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return \GtnPersistBase\Model\RepositoryInterface
     */
    public function remove(AggregateRootInterface $aggregateRoot)
    {
        parent::remove($aggregateRoot);
        return $this->removePaths($aggregateRoot);
    }

    /**
     * @return array
     */
    public function getAllRoots()
    {
        $select = $this->getSelect()
            ->join(
                ['root' => $this->getPathsTableName()],
                $this->getTableName() . '.id = root.descendant_id',
                [],
                Select::JOIN_LEFT
            )
            ->join(
                ['parent' => $this->getPathsTableName()],
                'root.descendant_id = parent.descendant_id AND parent.ancestor_id <> parent.descendant_id',
                [],
                Select::JOIN_LEFT
            )
            ->where(new IsNull('parent.descendant_id'));
        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return array
     */
    public function getAllChildren(EnvironmentInterface $environment)
    {
        $select = $this->getSelect()
            ->join(
                ['children' => $this->getPathsTableName()],
                $this->getTableName() . '.id = children.descendant_id',
                [],
                Select::JOIN_LEFT
            )
            ->join(
                ['parent' => $this->getPathsTableName()],
                'children.descendant_id = parent.descendant_id',
                ['parent_id' => 'ancestor_id'],
                Select::JOIN_LEFT
            );

        $select
            ->where
            ->equalTo('parent.length', 1)
            ->and
            ->equalTo('children.ancestor_id', $environment->getId())
            ->and
            ->notEqualTo($this->getTableName() . '.id', $environment->getId());

        $result = $this->performRead($select);

        $className = $this->getAggregateRootClass();
        $allChildrenGroupedByParentId = [];
        foreach ($result as $row) {
            $aggregateRoot = new $className;
            $this->getAggregateRootHydrator()->hydrate($row, $aggregateRoot);
            $allChildrenGroupedByParentId[$row['parent_id']][] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
        }

        $children = [];
        if (array_key_exists($environment->getId(), $allChildrenGroupedByParentId)) {
            $children = $allChildrenGroupedByParentId[$environment->getId()];
            foreach ($children as $child) {
                $this->setAllChildren($child, $allChildrenGroupedByParentId);
            }
        }
        return $children;
    }

    /**
     * @param EnvironmentInterface $environment
     * @return EnvironmentInterface
     */
    public function getParent(EnvironmentInterface $environment)
    {
        $select = $this->getSelect()->join(
            ['parent' => $this->getPathsTableName()],
            $this->getTableName() . '.id = parent.ancestor_id',
            [],
            Select::JOIN_LEFT
        );

        $select
            ->where
            ->equalTo('parent.descendant_id', $environment->getId())
            ->and
            ->notEqualTo($this->getTableName() . '.id', $environment->getId());

        $result = $this->performRead($select->order('parent.length'));

        $className = $this->getAggregateRootClass();
        $parents = [];
        foreach ($result as $row) {
            $aggregateRoot = new $className;
            $this->getAggregateRootHydrator()->hydrate($row, $aggregateRoot);
            $parents[] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
        }

        $parent = null;
        if (!empty($parents)) {
            $parent = array_shift($parents);
            $this->setAllParents($parent, $parents);
        }
        return $parent;
    }

    /**
     * Set PathsTableName.
     *
     * @param string $pathsTableName
     * @return EnvironmentRepository
     */
    public function setPathsTableName($pathsTableName)
    {
        $this->pathsTableName = $pathsTableName;
        return $this;
    }

    /**
     * Get PathsTableName.
     *
     * @return string
     */
    public function getPathsTableName()
    {
        return $this->pathsTableName;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return EnvironmentRepository
     */
    protected function addPaths(AggregateRootInterface $aggregateRoot)
    {
        /** @var StatementInterface $statement */
        $statement = $this->getDbAdapter()->query(
            'INSERT INTO ' . $this->getPathsTableName() . ' ' .
            'SELECT ?, ?, 0 UNION ALL ' .
            'SELECT ancestor_id, ?, length+1 FROM ' . $this->getPathsTableName() . ' WHERE descendant_id = ?'
        );

        $id = $aggregateRoot->getId();
        $parentId = $aggregateRoot->hasParent() ? $aggregateRoot->getParent()->getId() : 0;
        $statement->execute([$id, $id, $id, $parentId]);

        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return EnvironmentRepository
     */
    protected function removePaths(AggregateRootInterface $aggregateRoot)
    {
        /** @var StatementInterface $statement */
        $statement = $this->getDbAdapter()->query(
            'DELETE FROM ' . $this->getPathsTableName() . ' ' .
            'WHERE descendant_id IN ' .
            '(SELECT * FROM ' .
            '(SELECT descendant_id FROM ' . $this->getPathsTableName() . ' WHERE ancestor_id = ?)' .
            'AS tmp)'
        );
        $statement->execute([$aggregateRoot->getId()]);

        return $this;
    }

    /**
     * @param EnvironmentInterface $environment
     * @param array                $allChildrenGroupedByParentId
     */
    protected function setAllChildren($environment, $allChildrenGroupedByParentId)
    {
        if (array_key_exists($environment->getId(), $allChildrenGroupedByParentId)) {
            foreach ($allChildrenGroupedByParentId[$environment->getId()] as $child) {
                $this->setAllChildren($child, $allChildrenGroupedByParentId);
                $environment->addChild($child);
            }
        }
    }

    /**
     * @param EnvironmentInterface $environment
     * @param array                $parents
     */
    protected function setAllParents($environment, $parents)
    {
        if (empty($parents)) {
            return;
        }
        $parent = array_shift($parents);
        $environment->setParent($parent);
        $this->setAllParents($parent, $parents);
    }
}
