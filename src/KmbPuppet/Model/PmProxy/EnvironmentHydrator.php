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
namespace KmbPuppet\Model\PmProxy;

use KmbPuppet\Model\EnvironmentInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class EnvironmentHydrator implements HydratorInterface
{
    /**
     * Extract values from an object
     *
     * @param  EnvironmentInterface $object
     * @return array
     */
    public function extract($object)
    {
        $data = ['name' => $object->getName()];
        if ($object->hasParent()) {
            $data['parent'] = $object->getParent()->getId();
        }
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  EnvironmentInterface $object
     * @return EnvironmentInterface
     */
    public function hydrate(array $data, $object)
    {
        return $object;
    }
}
