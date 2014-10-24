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

use KmbDomain\Model\ClassTemplatesHydratorInterface;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbDomain\Model\ParameterFactoryInterface;
use KmbDomain\Model\PuppetClassFactoryInterface;
use KmbDomain\Model\PuppetClassRepositoryInterface;
use KmbPmProxy\Service\PuppetClass;
use KmbPmProxy\Service\PuppetModule as PuppetModuleService;
use KmbPuppet\Service;
use KmbPuppetDb\Exception\RuntimeException;
use KmbPuppetDb\Model\NodeInterface;
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
        }

        /** @var ClassTemplatesHydratorInterface $classTemplatesHydrator */
        $classTemplatesHydrator = $this->serviceLocator->get('classTemplatesHydrator');
        /** @var PuppetModuleService $puppetModuleService */
        $puppetModuleService = $this->serviceLocator->get('pmProxyPuppetModuleService');

        $availableClasses = [];
        foreach ($puppetModuleService->getAllByEnvironment($environment) as $puppetModule) {
            foreach ($puppetModule->getClasses() as $puppetClass) {
                $groupClass = $group->getClassByName($puppetClass->getName());
                if ($groupClass != null) {
                    $classTemplatesHydrator->hydrate($puppetClass->getParametersTemplates(), $groupClass);
                } else {
                    $availableClasses[$puppetModule->getName()][] = $puppetClass;
                }
            }
        }

        $selectedClass = $group->getClassByName($this->params()->fromQuery('selectedClass'));

        return new ViewModel([
            'group' => $group,
            'serversCount' => count($nodes),
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass
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
            return new JsonModel(['error' => $this->translate('An error occured while reaching PuppetDB !')]);
        }

        $servers = [];
        foreach ($nodes as $node) {
            /** @var NodeInterface $node */
            $servers[] = $node->getName();
        }
        return new JsonModel([
            'servers' => $servers,
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

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
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

    public function addClassAction()
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

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        /** @var PuppetClass $puppetClassService */
        $puppetClassService = $this->serviceLocator->get('pmProxyPuppetClassService');

        $className = $this->params()->fromPost('class');
        if ($group->hasClassWithName($className)) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('Group already has the class %s'), $className));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show'], ['id' => $group->getId()], true);
        }

        $pmProxyPuppetClass = $puppetClassService->getByEnvironmentAndName($environment, $className);
        if ($pmProxyPuppetClass === null) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('Unknown class %s'), $className));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show'], ['id' => $group->getId()], true);
        }

        /** @var ParameterFactoryInterface $parameterFactory */
        $parameterFactory = $this->serviceLocator->get('parameterFactory');
        $parameters = $parameterFactory->createRequiredFromTemplates($pmProxyPuppetClass->getParametersTemplates());

        $class = new \KmbDomain\Model\PuppetClass();
        $class->setName($className);
        $class->setGroup($group);
        $class->setParameters($parameters);

        /** @var PuppetClassRepositoryInterface $classRepository */
        $classRepository = $this->getServiceLocator()->get('PuppetClassRepository');
        $classRepository->add($class);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show'], ['id' => $group->getId(), 'query' => ['selectedClass' => $className]], true);
    }

    public function removeClassAction()
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

        /** @var PuppetClassRepositoryInterface $classRepository */
        $classRepository = $this->getServiceLocator()->get('PuppetClassRepository');

        $className = $this->params()->fromRoute('className');
        $class = $group->getClassByName($className);
        if ($class == null) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("Group doesn't have class %s"), $className));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show'], ['id' => $group->getId()], true);
        }

        $classRepository->remove($class);

        $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Class %s has been succesfully removed"), $className));
        return $this->redirect()->toRoute('puppet-group', ['action' => 'show'], ['id' => $group->getId(), 'query' => ['selectedClass' => $className]], true);
    }
}
