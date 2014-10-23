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
use KmbDomain\Model\Parameter;
use KmbDomain\Model\ParameterFactoryInterface;
use KmbDomain\Model\ParameterInterface;
use KmbDomain\Model\ParameterRepositoryInterface;
use KmbDomain\Model\ParameterType;
use KmbPmProxy\Service\PuppetClass;
use KmbPuppet\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;

class ParameterController extends AbstractActionController
{
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
        $group = $groupRepository->getById($this->params()->fromRoute('groupId'));

        if ($group == null || $group->getEnvironment() != $environment) {
            $this->flashMessenger()->addErrorMessage('Unknown group');
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $selectedClass = $this->params()->fromQuery('selectedClass');

        /** @var ParameterRepositoryInterface $parameterRepository */
        $parameterRepository = $this->getServiceLocator()->get('ParameterRepository');
        /** @var ParameterInterface $parameter */
        $parameter = $parameterRepository->getById($this->params()->fromRoute('id'));

        if ($parameter == null) {
            $this->flashMessenger()->addErrorMessage('Unknown parameter');
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $selectedClass]], true);
        }

        $name = $this->params()->fromPost('name');
        if (!empty($name)) {
            $parameter->setName($name);
        }
        $values = $this->params()->fromPost('values');
        if (!empty($values)) {
            $parameter->setValues($values);
        }
        $parameterRepository->update($parameter);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $selectedClass], 'fragment' => 'parameter' . $parameter->getId()], true);
    }

    public function removeAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('groupId'));

        if ($group == null || $group->getEnvironment() != $environment) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var ParameterRepositoryInterface $parameterRepository */
        $parameterRepository = $this->getServiceLocator()->get('ParameterRepository');
        /** @var ParameterInterface $parameter */
        $parameter = $parameterRepository->getById($this->params()->fromRoute('id'));

        if ($parameter == null || $parameter->getClass() == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $class = $parameter->getClass();
        if (!$group->hasClassWithName($class->getName())) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $anchor = $parameter->hasParent() ? 'parameter' . $parameter->getParent()->getId() : '';

        $parameterRepository->remove($parameter);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()], 'fragment' => $anchor], true);
    }

    public function addChildAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var GroupInterface $group */
        $group = $groupRepository->getById($this->params()->fromRoute('groupId'));

        if ($group == null || $group->getEnvironment() != $environment) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var ParameterRepositoryInterface $parameterRepository */
        $parameterRepository = $this->getServiceLocator()->get('ParameterRepository');
        /** @var ParameterInterface $parameter */
        $parameter = $parameterRepository->getById($this->params()->fromRoute('id'));

        if ($parameter == null || $parameter->getClass() == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $class = $parameter->getClass();
        if (!$group->hasClassWithName($class->getName())) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var PuppetClass $puppetClassService */
        $puppetClassService = $this->serviceLocator->get('pmProxyPuppetClassService');

        $pmProxyPuppetClass = $puppetClassService->getByEnvironmentAndName($environment, $class->getName());
        if ($pmProxyPuppetClass === null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Class template not found'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        $name = $this->params()->fromPost('name');
        if (empty($name)) {
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        /** @var \stdClass $template */
        $template = $this->findAssociatedTemplate(ArrayUtils::merge($parameter->getAncestorsNames(), [$name]), $pmProxyPuppetClass->getParametersTemplates());
        if ($template == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Parameter template not found'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        /** @var ParameterFactoryInterface $parameterFactory */
        $parameterFactory = $this->serviceLocator->get('parameterFactory');

        if ($template->type == ParameterType::EDITABLE_HASHTABLE) {
            $child = new Parameter();
            $child->setName($name);
            if (isset($template->entries)) {
                $child->setChildren($parameterFactory->createRequiredFromTemplates($template->entries));
            }
        } else {
            $child = $parameterFactory->createFromTemplate($template);
        }
        $child->setClass($class);
        $child->setParent($parameter);

        $parameterRepository->add($child);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()], 'fragment' => 'parameter' . $child->getId()], true);
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
                if ($template->name === $name) {
                    if ($template->type == ParameterType::EDITABLE_HASHTABLE) {
                        array_shift($ancestorsNames); // Ignore keys of editable hashtables
                    }
                    if (empty($ancestorsNames)) {
                        return $template;
                    } elseif ($template->type == ParameterType::HASHTABLE || $template->type == ParameterType::EDITABLE_HASHTABLE) {
                        return $this->findAssociatedTemplate($ancestorsNames, $template->entries);
                    }
                }
            }
        }
    }
}
