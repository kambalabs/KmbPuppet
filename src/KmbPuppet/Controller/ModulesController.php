<?php
/**
 * @copyright Copyright (c) 2014, 2015 Orange Applications for Business
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
use KmbDomain\Model;
use KmbPmProxy\Validator\PuppetClassValidator;
use KmbPmProxy\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class ModulesController extends AbstractActionController implements AuthenticatedControllerInterface
{
    public function indexAction()
    {
        /** @var Model\EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return new ViewModel();
        }
        if (!$this->isGranted('readEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var Service\PuppetModuleInterface $puppetModuleService */
        $puppetModuleService = $this->getServiceLocator()->get('pmProxyPuppetModuleService');
        $puppetModules = $puppetModuleService->getAllInstalledByEnvironment($environment);
        $parentPuppetModules = $environment->hasParent() ? $puppetModuleService->getAllInstalledByEnvironment($environment->getParent()) : [];
        $inheritedPuppetModules = [];
        foreach ($puppetModules as $index => $module) {
            if ($module->isInherited()) {
                $inheritedPuppetModules[] = $module;
                unset($puppetModules[$index]);
            } elseif (array_key_exists($module->getName(), $parentPuppetModules)) {
                $module->setOverride(true);
            }
        }

        return new ViewModel([
            'environment' => $environment,
            'puppetModules' => $puppetModules,
            'inheritedPuppetModules' => $inheritedPuppetModules
        ]);
    }

    public function showAction()
    {
        /** @var Model\EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return $this->notFoundAction();
        }
        if (!$this->isGranted('readEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var Service\PuppetModuleInterface $puppetModuleService */
        $puppetModuleService = $this->getServiceLocator()->get('pmProxyPuppetModuleService');

        $puppetModule = $puppetModuleService->getInstalledByEnvironmentAndName($environment, $this->params()->fromRoute('moduleName'));
        if ($puppetModule === null) {
            return $this->notFoundAction();
        }

        $classesErrors = [];
        foreach ($puppetModule->getClasses() as $class) {
            /** @var PuppetClassValidator $validator */
            $validator = $this->getServiceLocator()->get('KmbPmProxy\Validator\PuppetClassValidator');
            if (!$validator->isValid($class)) {
                $classesErrors[$class->getName()] = count($validator->getMessages());
            }
        }

        $this->getServiceLocator()->get('breadcrumb')->findBy('id', 'module')->setLabel($puppetModule->getName());

        return new ViewModel([
            'environment' => $environment,
            'module' => $puppetModule,
            'back' => $this->params()->fromQuery('back') ?: $this->url()->fromRoute('puppet', ['controller' => 'modules', 'action' => 'index'], [], true),
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
        if (!$this->isGranted('readEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var Service\PuppetModuleInterface $puppetModuleService */
        $puppetModuleService = $this->getServiceLocator()->get('pmProxyPuppetModuleService');

        $puppetModule = $puppetModuleService->getInstalledByEnvironmentAndName($environment, $this->params()->fromRoute('moduleName'));
        if ($puppetModule === null) {
            return $this->notFoundAction();
        }

        $className = $this->params()->fromRoute('className');
        if (!$puppetModule->hasClass($className)) {
            return $this->notFoundAction();
        }

        $class = $puppetModule->getClass($className);
        /** @var PuppetClassValidator $validator */
        $validator = $this->getServiceLocator()->get('KmbPmProxy\Validator\PuppetClassValidator');
        $parametersErrors = [];
        if (!$validator->isValid($class)) {
            foreach ($validator->getMessages() as $parameter => $message) {
                if (!$class->hasParameterTemplate($parameter)) {
                    $class->addParameterTemplate($class->getParameterDefinition($parameter));
                }
                $parametersErrors[$parameter] = $message;
            }
        }

        $this->getServiceLocator()->get('breadcrumb')->findBy('id', 'module')->setLabel($puppetModule->getName());
        $this->getServiceLocator()->get('breadcrumb')->findBy('id', 'module-class')->setLabel($class->getName());

        if (!empty($parametersErrors)) {
            $this->globalMessenger()->addDangerMessage($this->translate("Parameters are in error, please check the template and the class definition"));
        }
        return new ViewModel([
            'environment' => $environment,
            'class' => $class,
            'back' => $this->params()->fromQuery('back'),
            'parametersErrors' => $parametersErrors,
        ]);
    }
}
