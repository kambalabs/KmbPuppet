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
namespace KmbPuppet\View\Helper;

use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\View\Helper\AbstractHelper;

class EnvironmentSelect extends AbstractHelper
{
    /** @var EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /** @var array */
    protected $environments;

    public function __invoke($id, $name, $default = '-')
    {
        if ($this->environments === null) {
            $this->environments = $this->environmentRepository->getAllRoots();
        }
        return $this->getView()->partial('kmb-puppet/environments/environments-select', [
            'id' => $id,
            'name' => $name,
            'default' => $default,
            'environments' => $this->environments,
        ]);
    }

    /**
     * Set EnvironmentRepository.
     *
     * @param \KmbPuppet\Model\EnvironmentRepositoryInterface $environmentRepository
     * @return EnvironmentSelect
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return \KmbPuppet\Model\EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }
}
