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
use KmbDomain\Model\Parameter;
use KmbDomain\Model\ParameterFactoryInterface;
use KmbDomain\Model\ParameterRepositoryInterface;
use KmbDomain\Model\ParameterType;
use KmbDomain\Model\PuppetClassInterface;
use KmbDomain\Model\PuppetClassRepositoryInterface;
use KmbPmProxy\Service\PuppetClass as PuppetClassService;
use KmbPuppet\Service;
use Zend\Mvc\Controller\AbstractActionController;

class PuppetClassController extends AbstractActionController
{
    public function addParameterAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->serviceLocator->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var PuppetClassRepositoryInterface $classRepository */
        $classRepository = $this->serviceLocator->get('PuppetClassRepository');
        /** @var PuppetClassInterface $class */
        $class = $classRepository->getById($this->params()->fromRoute('id'));

        if ($class == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $group = $class->getGroup();
        if ($group == null || $group->getEnvironment() != $environment) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $revision = $group->getRevision();
        if ($revision->isReleased()) {
            $message = $this->translate('You have been redirected to the last current revision of this group because last changes has been recently saved by <strong>%s</strong>. Please try again !');
            $this->flashMessenger()->addErrorMessage(sprintf($message, $revision->getReleasedBy()));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        /** @var ParameterRepositoryInterface $parameterRepository */
        $parameterRepository = $this->serviceLocator->get('ParameterRepository');

        /** @var PuppetClassService $puppetClassService */
        $puppetClassService = $this->serviceLocator->get('pmProxyPuppetClassService');

        $pmProxyPuppetClass = $puppetClassService->getByEnvironmentAndName($environment, $class->getName());
        if ($pmProxyPuppetClass === null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Unable to find associated template to the class'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        $name = rtrim($this->params()->fromPost('name'), '*');
        if (empty($name)) {
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        /** @var \stdClass $template */
        $template = $this->findAssociatedTemplate($name, $pmProxyPuppetClass->getParametersTemplates());
        if ($template == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('Unable to find associated template to the parameter'));
            return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()]], true);
        }

        /** @var ParameterFactoryInterface $parameterFactory */
        $parameterFactory = $this->serviceLocator->get('parameterFactory');

        if ($template->type == ParameterType::EDITABLE_HASHTABLE) {
            $parameter = new Parameter();
            $parameter->setName($name);
            if (isset($template->entries)) {
                $parameter->setChildren($parameterFactory->createRequiredFromTemplates($template->entries));
            }
        } else {
            $parameter = $parameterFactory->createFromTemplate($template);
        }
        $parameter->setClass($class);

        $parameterRepository->add($parameter);

        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], ['query' => ['selectedClass' => $class->getName()], 'fragment' => 'parameter' . $parameter->getId()], true);
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
