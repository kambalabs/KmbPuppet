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

use KmbAuthentication\Controller\AuthenticatedControllerInterface;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\Group;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbPmProxy\Hydrator\GroupHydratorInterface;
use KmbPmProxy\Service\PuppetModuleInterface;
use KmbPuppet\Service;
use KmbPuppet\Validator\GroupClassValidator;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class GroupsController extends AbstractActionController implements AuthenticatedControllerInterface
{
    public function indexAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return new ViewModel();
        }
        if (!$this->isGranted('readEnv', $environment)) {
            throw new UnauthorizedException();
        }

        $currentRevision = $environment->getCurrentRevision();
        if ($currentRevision == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('This environment is invalid, it has no current revision. Please contact administrator !'));
            return new ViewModel();
        }

        /** @var GroupHydratorInterface $groupHydrator */
        $groupHydrator = $this->serviceLocator->get('pmProxyGroupHydrator');
        /** @var PuppetModuleInterface $puppetModuleService */
        $puppetModuleService = $this->serviceLocator->get('pmProxyPuppetModuleService');
        $modules = $puppetModuleService->getAllInstalledByEnvironment($environment);

        $errors = [];
        $groups = $currentRevision->getGroups();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $groupHydrator->hydrate($modules, $group);
                if ($group->hasClasses()) {
                    foreach ($group->getClasses() as $class) {
                        /** @var GroupClassValidator $classValidator */
                        $classValidator = $this->serviceLocator->get('KmbPuppet\Validator\GroupClassValidator');
                        if (!$classValidator->isValid($class)) {
                            $errors[$group->getName()] = isset($errors[$group->getName()]) ? $errors[$group->getName()]+1 : 1;
                        }
                    }
                }
            }
        }

        return new ViewModel([
            'groups' => $groups,
            'environment' => $environment,
            'errors' => $errors,
        ]);
    }

    public function updateAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return new JsonModel(['error' => true]);
        }

        $groupsIds = $this->params()->fromPost('groups');
        if (!empty($groupsIds)) {
            /** @var GroupRepositoryInterface $groupRepository */
            $groupRepository = $this->getServiceLocator()->get('GroupRepository');
            $groups = $groupRepository->getAllByIds($groupsIds);
            $revision = $groups[0]->getRevision();
            if ($revision->isReleased()) {
                return new JsonModel(['error' => true]);
            }
            foreach ($groups as $group) {
                $group->setOrdering(array_search($group->getId(), $groupsIds));
                $groupRepository->update($group);
            }
            $this->writeRevisionLog($revision, sprintf($this->translate('Update groups ordering')));
        }

        return new JsonModel();
    }

    public function createAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $currentRevision = $environment->getCurrentRevision();
        if ($currentRevision == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('This environment is invalid, it has no current revision. Please contact administrator !'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $name = $this->params()->fromPost('name');
        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        if ($groupRepository->getByNameAndRevision($name, $currentRevision)) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('The group %s already exists in this environment !'), $name));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $group = new Group($name);
        $group->setRevision($currentRevision);
        $groupRepository->add($group);
        $this->writeRevisionLog($currentRevision, sprintf($this->translate('Create group %s'), $name));

        $this->flashMessenger()->addSuccessMessage(sprintf($this->translate('The group %s has been successfully created !'), $name));
        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], [], true);
    }
}
