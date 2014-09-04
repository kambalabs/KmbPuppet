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

use KmbDomain\Model;
use KmbPmProxy\Model\PuppetClassValidator;
use KmbPmProxy\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ModulesController extends AbstractActionController
{
    public function indexAction()
    {
        /** @var Model\EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return new ViewModel(['error' => $this->translate('You have to select an environment first !')]);
        }

        /** @var Service\ModuleInterface $pmProxyModuleService */
        $pmProxyModuleService = $this->getServiceLocator()->get('pmProxyModuleService');

        return new ViewModel([
            'environment' => $environment,
            'modules' => $pmProxyModuleService->getAllByEnvironment($environment)
        ]);
    }

    public function showAction()
    {
        /** @var Model\EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var Service\ModuleInterface $pmProxyModuleService */
        $pmProxyModuleService = $this->getServiceLocator()->get('pmProxyModuleService');

        $module = $pmProxyModuleService->getByEnvironmentAndName($environment, $this->params()->fromRoute('name'));
        if ($module === null) {
            return $this->notFoundAction();
        }

        $classesErrors = [];
        foreach ($module->getClasses() as $class) {
            /** @var PuppetClassValidator $validator */
            $validator = $this->getServiceLocator()->get('KmbPmProxy\Model\PuppetClassValidator');
            if (!$validator->isValid($class)) {
                $classesErrors[$class->getName()] = count($validator->getMessages());
            }
        }

        return new ViewModel([
            'environment' => $environment,
            'module' => $module,
            'back' => $this->params()->fromQuery('back'),
            'classesErrors' => $classesErrors,
        ]);
    }

    public function showClassAction()
    {
        /** @var Model\EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }

        /** @var Service\ModuleInterface $pmProxyModuleService */
        $pmProxyModuleService = $this->getServiceLocator()->get('pmProxyModuleService');

        $module = $pmProxyModuleService->getByEnvironmentAndName($environment, $this->params()->fromRoute('moduleName'));
        if ($module === null) {
            return $this->notFoundAction();
        }

        $className = $this->params()->fromRoute('className');
        if (!$module->hasClass($className)) {
            return $this->notFoundAction();
        }

        $class = $module->getClass($className);
        /** @var PuppetClassValidator $validator */
        $validator = $this->getServiceLocator()->get('KmbPmProxy\Model\PuppetClassValidator');
        $parametersErrors = [];
        $classErrors = [];
        if (!$validator->isValid($class)) {
            foreach ($validator->getMessages() as $parameter => $message) {
                if ($class->hasParameterTemplate($parameter)) {
                    $parametersErrors[$parameter] = $message;
                } else {
                    $classErrors[] = $message;
                }
            }
        }

        return new ViewModel([
            'environment' => $environment,
            'class' => $class,
            'back' => $this->params()->fromQuery('back'),
            'parametersErrors' => $parametersErrors,
            'classErrors' => $classErrors,
        ]);
    }
}
