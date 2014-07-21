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

use GtnPersistZendDb\Infrastructure\ZendDbRepository;
use KmbPuppet\Model\EnvironmentInterface;
use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class EnvironmentRepository extends ZendDbRepository implements EnvironmentRepositoryInterface
{
    /** @var string */
    protected $pathsTableName;

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
        $predicate = new Where();
        $select = $this->getSelect()
            ->join(
                ['p' => $this->getPathsTableName()],
                $this->getTableName() . '.id = p.descendant_id',
                []
            )
            ->where([$predicate->equalTo('p.ancestor_id', $environment->getId()), 'p.length = 1']);
        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return EnvironmentInterface
     */
    public function getParent(EnvironmentInterface $environment)
    {
        $predicate = new Where();
        $select = $this->getSelect()
            ->join(
                ['p' => $this->getPathsTableName()],
                $this->getTableName() . '.id = p.ancestor_id',
                []
            )
            ->where([$predicate->equalTo('p.descendant_id', $environment->getId()), 'p.length = 1']);
        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
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
}
