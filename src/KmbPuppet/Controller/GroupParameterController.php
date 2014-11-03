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
use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupParameterFactoryInterface;
use KmbDomain\Model\GroupParameterInterface;
use KmbDomain\Model\GroupParameterRepositoryInterface;
use KmbDomain\Model\GroupParameterType;
use KmbPmProxy\Service\PuppetClass;
use KmbPuppet\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use ZfcRbac\Exception\UnauthorizedException;

class GroupParameterController extends AbstractActionController
{
    public function updateAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('groupId'));

        if ($group == null || $group->getEnvironment()->getId() != $environment->getId()) {
            $this->flashMessenger()->addErrorMessage('Unknown group');
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $selectedClass = $this->params()->fromQuery('selectedClass');

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $newRevision = $environment->getCurrentRevision();
            if ($newRevision == null) {
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            $newGroup = $newRevision->getGroupByName($group->getName());
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $newGroup->getId()], ['query' => ['selectedClass' => $selectedClass]], true);
        }

        /** @var GroupParameterRepositoryInterface $groupParameterRepository */
        $groupParameterRepository = $this->getServiceLocator()->get('GroupParameterRepository');
        /** @var GroupParameterInterface $groupParameter */
        $groupParameter = $groupParameterRepository->getById($this->params()->fromRoute('id'));

        if ($groupParameter == null) {
            $this->flashMessenger()->addErrorMessage('Unknown parameter');
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $selectedClass]], true);
        }

        $name = rtrim($this->params()->fromPost('name'), '*');
        if (!empty($name)) {
            $groupParameter->setName($name);
        }
        $values = $this->params()->fromPost('values');
        if (!empty($values)) {
            $groupParameter->setValues($values);
        }
        $groupParameterRepository->update($groupParameter);
        $this->writeRevisionLog($revision, sprintf($this->translate('Update parameter %s of class %s on group %s'), $groupParameter->getName(), $groupParameter->getClass()->getName(), $group->getName()));

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $selectedClass], 'fragment' => 'parameter' . $groupParameter->getId()], true);
    }

    public function removeAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('groupId'));

        if ($group == null || $group->getEnvironment()->getId() != $environment->getId()) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var GroupParameterRepositoryInterface $groupParameterRepository */
        $groupParameterRepository = $this->getServiceLocator()->get('GroupParameterRepository');
        /** @var GroupParameterInterface $groupParameter */
        $groupParameter = $groupParameterRepository->getById($this->params()->fromRoute('id'));

        if ($groupParameter == null || $groupParameter->getClass() == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $groupClass = $groupParameter->getClass();
        if (!$group->hasClassWithName($groupClass->getName())) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $anchor = $groupParameter->hasParent() ? 'parameter' . $groupParameter->getParent()->getId() : '';

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $newRevision = $environment->getCurrentRevision();
            if ($newRevision == null) {
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            $newGroup = $newRevision->getGroupByName($group->getName());
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $newGroup->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        $groupParameterRepository->remove($groupParameter);
        $this->writeRevisionLog($revision, sprintf($this->translate('Remove parameter %s of class %s on group %s'), $groupParameter->getName(), $groupParameter->getClass()->getName(), $group->getName()));

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()], 'fragment' => $anchor], true);
    }

    public function addChildAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('groupId'));

        if ($group == null || $group->getEnvironment()->getId() != $environment->getId()) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var GroupParameterRepositoryInterface $groupParameterRepository */
        $groupParameterRepository = $this->getServiceLocator()->get('GroupParameterRepository');
        /** @var GroupParameterInterface $groupParameter */
        $groupParameter = $groupParameterRepository->getById($this->params()->fromRoute('id'));

        if ($groupParameter == null || $groupParameter->getClass() == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $groupClass = $groupParameter->getClass();
        if (!$group->hasClassWithName($groupClass->getName())) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $newRevision = $environment->getCurrentRevision();
            if ($newRevision == null) {
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            $newGroup = $newRevision->getGroupByName($group->getName());
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $newGroup->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        /** @var PuppetClass $puppetClassService */
        $puppetClassService = $this->serviceLocator->get('pmProxyPuppetClassService');

        $pmProxyPuppetClass = $puppetClassService->getByEnvironmentAndName($environment, $groupClass->getName());
        if ($pmProxyPuppetClass === null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Unable to find associated template to the class'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        $name = rtrim($this->params()->fromPost('name'), '*');
        if (empty($name)) {
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        /** @var \stdClass $template */
        $template = $this->findAssociatedTemplate(ArrayUtils::merge($groupParameter->getAncestorsNames(), [$name]), $pmProxyPuppetClass->getParametersTemplates());
        if ($template == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Unable to find associated template to the parameter'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        /** @var GroupParameterFactoryInterface $groupParameterFactory */
        $groupParameterFactory = $this->serviceLocator->get('groupParameterFactory');

        if ($template->type == GroupParameterType::EDITABLE_HASHTABLE && $template->isKey) {
            $child = new GroupParameter();
            $child->setName($name);
            if (isset($template->entries)) {
                $child->setChildren($groupParameterFactory->createRequiredFromTemplates($template->entries));
            }
        } else {
            $child = $groupParameterFactory->createFromTemplate($template);
        }
        $child->setClass($groupClass);
        $child->setParent($groupParameter);

        $groupParameterRepository->add($child);
        $this->writeRevisionLog($revision, sprintf($this->translate('Add parameter %s to class %s on group %s'), $name, $groupParameter->getClass()->getName(), $group->getName()));

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()], 'fragment' => 'parameter' . $child->getId()], true);
    }

    /**
     * @param array $ancestorsNames
     * @param \stdClass[] $templates
     * @return \stdClass
     */
    protected function findAssociatedTemplate($ancestorsNames, $templates)
    {
        $name = array_shift($ancestorsNames);
        if (!empty($templates)) {
            foreach ($templates as $template) {
                $template->isKey = false;
                if ($template->name === $name) {
                    if (empty($ancestorsNames)) {
                        return $template;
                    }
                    if ($template->type == GroupParameterType::EDITABLE_HASHTABLE) {
                        array_shift($ancestorsNames); // Ignore keys of editable hashtables
                        if (empty($ancestorsNames)) {
                            $template->isKey = true;
                            return $template;
                        }
                    }
                    if ($template->type == GroupParameterType::HASHTABLE || $template->type == GroupParameterType::EDITABLE_HASHTABLE) {
                        return $this->findAssociatedTemplate($ancestorsNames, $template->entries);
                    }
                }
            }
        }
    }
}
