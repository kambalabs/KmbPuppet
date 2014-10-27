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
use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupParameterFactoryInterface;
use KmbDomain\Model\GroupParameterRepositoryInterface;
use KmbDomain\Model\GroupParameterType;
use KmbDomain\Model\GroupClassInterface;
use KmbDomain\Model\GroupClassRepositoryInterface;
use KmbPmProxy\Service\PuppetClass as PuppetClassService;
use KmbPuppet\Service;
use Zend\Mvc\Controller\AbstractActionController;
use ZfcRbac\Exception\UnauthorizedException;

class GroupClassController extends AbstractActionController
{
    public function addParameterAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->serviceLocator->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var GroupClassRepositoryInterface $groupClassRepository */
        $groupClassRepository = $this->serviceLocator->get('GroupClassRepository');
        /** @var GroupClassInterface $groupClass */
        $groupClass = $groupClassRepository->getById($this->params()->fromRoute('id'));

        if ($groupClass == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $group = $groupClass->getGroup();
        if ($group == null || $group->getEnvironment()->getId() != $environment->getId()) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        /** @var GroupParameterRepositoryInterface $groupParameterRepository */
        $groupParameterRepository = $this->serviceLocator->get('GroupParameterRepository');

        /** @var PuppetClassService $puppetClassService */
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
        $template = $this->findAssociatedTemplate($name, $pmProxyPuppetClass->getParametersTemplates());
        if ($template == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Unable to find associated template to the parameter'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()]], true);
        }

        /** @var GroupParameterFactoryInterface $groupParameterFactory */
        $groupParameterFactory = $this->serviceLocator->get('groupParameterFactory');

        $groupParameter = $groupParameterFactory->createFromTemplate($template);
        $groupParameter->setClass($groupClass);

        $groupParameterRepository->add($groupParameter);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $groupClass->getName()], 'fragment' => 'parameter' . $groupParameter->getId()], true);
    }

    /**
     * @param array $name
     * @param \stdClass[] $templates
     * @return \stdClass
     */
    protected function findAssociatedTemplate($name, $templates)
    {
        if (!empty($templates)) {
            foreach ($templates as $template) {
                if ($template->name === $name) {
                    return $template;
                }
            }
        }
    }
}
