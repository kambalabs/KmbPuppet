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
namespace KmbPuppet\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class CustomGroups extends AbstractPlugin
{
    /** @var  array */
    protected $config;

    /**
     * @return CustomGroups
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param string $type
     * @return boolean
     */
    public function hasType($type)
    {
        return array_key_exists($type, $this->config);
    }

    /**
     * @param string $type
     * @return string[]
     */
    public function defaultClasses($type)
    {
        return isset($this->config[$type]['default_classes']) ? array_values(array_unique($this->config[$type]['default_classes'])) : [];
    }

    /**
     * Set Config.
     *
     * @param array $config
     * @return CustomGroups
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
}
