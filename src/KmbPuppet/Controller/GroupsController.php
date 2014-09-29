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
namespace KmbPuppet\Controller;

use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbPuppet\Service;
use KmbPuppetDb\Exception\RuntimeException;
use KmbPuppetDb\Model\Node;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class GroupsController extends AbstractActionController
{
    public function indexAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return new ViewModel(['error' => $this->translate('You have to select an environment first !')]);
        }

        $currentRevision = $environment->getCurrentRevision();
        if ($currentRevision == null) {
            return new ViewModel(['error' => $this->translate('This environment is invalid, it has no current revision. Please contact administrator !')]);
        }

        return new ViewModel([
            'groups' => $currentRevision->getGroups(),
            'environment' => $environment,
        ]);
    }

    public function updateAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return new JsonModel(['error' => $this->translate('You have to select an environment first !')]);
        }

        $currentRevision = $environment->getCurrentRevision();
        if ($currentRevision == null) {
            return new ViewModel(['error' => $this->translate('This environment is invalid, it has no current revision. Please contact administrator !')]);
        }

        $groupsIds = $this->params()->fromPost('groups');
        if (!empty($groupsIds)) {
            /** @var GroupRepositoryInterface $groupRepository */
            $groupRepository = $this->getServiceLocator()->get('GroupRepository');
            $groups = $groupRepository->getAllByIds($groupsIds);
            foreach ($groups as $group) {
                $group->setOrdering(array_search($group->getId(), $groupsIds));
                $groupRepository->update($group);
            }
        }

        return new JsonModel();
    }
}
