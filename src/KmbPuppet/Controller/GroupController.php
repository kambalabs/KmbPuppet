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

class GroupController extends AbstractActionController
{
    public function showAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('id'));

        if ($group == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        if ($group->getEnvironment() != $environment) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var Service\Node $nodeService */
        $nodeService = $this->serviceLocator->get('KmbPuppet\Service\Node');
        $error = null;
        try {
            $nodes = $nodeService->getAllByEnvironmentAndPatterns($environment, $group->getIncludePattern(), $group->getExcludePattern());
        } catch (RuntimeException $exception) {
            $nodes = [];
            $error = $this->translate('Invalid inclusion or exclusion pattern !');
        }

        return new ViewModel([
            'group' => $group,
            'serversCount' => count($nodes),
            'error' => $error,
        ]);
    }

    public function serversAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->serviceLocator->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->serviceLocator->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('id'));

        if ($group == null) {
            return $this->notFoundAction();
        }

        if ($group->getEnvironment() != $environment) {
            return $this->notFoundAction();
        }

        /** @var Service\Node $nodeService */
        $nodeService = $this->serviceLocator->get('KmbPuppet\Service\Node');
        $include = $this->params()->fromQuery('include') ? : $group->getIncludePattern();
        $exclude = $this->params()->fromQuery('exclude') ? : $group->getExcludePattern();
        try {
            $nodes = $nodeService->getAllByEnvironmentAndPatterns($environment, $include, $exclude);
        } catch (RuntimeException $exception) {
            return new JsonModel(['error' => $this->translate('Invalid inclusion or exclusion pattern !')]);
        }

        return new JsonModel([
            'nodes' => array_map(function (Node $node) {
                return $node->getName();
            }, $nodes),
        ]);
    }

    public function updateAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('id'));

        if ($group == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        if ($group->getEnvironment() != $environment) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $name = $this->params()->fromPost('name');
        if (isset($name)) {
            $group->setName($name);
        }
        $include = $this->params()->fromPost('include');
        if (isset($include)) {
            $group->setIncludePattern($include);
        }
        $exclude = $this->params()->fromPost('exclude');
        if (isset($exclude)) {
            $group->setExcludePattern($exclude);
        }
        $groupRepository->update($group);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show'], ['id' => $group->getId()], true);
    }
}
