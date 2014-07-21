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
namespace KmbPuppet\Model;

class Environment implements EnvironmentInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $normalizedName;

    /** @var Environment */
    protected $parent;

    /** @var array */
    protected $children;

    /**
     * @param int $id
     * @return Environment
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Name.
     *
     * @param string $name
     * @return Environment
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set NormalizedName.
     *
     * @param string $normalizedName
     * @return Environment
     */
    public function setNormalizedName($normalizedName)
    {
        $this->normalizedName = $normalizedName;
        return $this;
    }

    /**
     * Get NormalizedName.
     *
     * @return string
     */
    public function getNormalizedName()
    {
        return $this->normalizedName;
    }

    /**
     * Set Parent.
     *
     * @param \KmbPuppet\Model\EnvironmentInterface $parent
     * @return Environment
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get Parent.
     *
     * @return \KmbPuppet\Model\Environment
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return $this->getParent() !== null;
    }

    /**
     * Set Children.
     *
     * @param array $children
     * @return Environment
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get Children.
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        $children = $this->getChildren();
        return !empty($children);
    }
}
