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
use KmbDomain\Model\GroupClass;
use KmbDomain\Service\GroupFactoryInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Service\GroupParameterFactoryInterface;
use KmbDomain\Service\GroupRepositoryInterface;
use KmbPmProxy\Hydrator\GroupHydratorInterface;
use KmbPmProxy\Service\PuppetClassInterface;
use KmbPmProxy\Service\PuppetModuleInterface;
use KmbPuppet\Service;
use KmbPuppet\Validator\GroupClassValidator;
use Symfony\Component\Yaml\Yaml;
use Zend\Form\Factory;
use Zend\Form\Form;
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
                            $errors[$group->getName()] = isset($errors[$group->getName()]) ? $errors[$group->getName()] + 1 : 1;
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
            $this->flashMessenger()->addErrorMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }

        $currentRevision = $environment->getCurrentRevision();
        if ($currentRevision == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('This environment is invalid, it has no current revision. Please contact administrator !'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
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
        $type = $this->params()->fromPost('type');
        if (!empty($type)) {
            if (!$this->customGroups()->hasType($type)) {
                $this->flashMessenger()->addErrorMessage(sprintf($this->translate('Unknown custom group type %s !'), $type));
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            $group->setType($type);
        }

        /** @var PuppetClassInterface $puppetClassService */
        $puppetClassService = $this->serviceLocator->get('pmProxyPuppetClassService');
        /** @var GroupParameterFactoryInterface $groupParameterFactory */
        $groupParameterFactory = $this->serviceLocator->get('groupParameterFactory');
        foreach ($this->customGroups()->defaultClasses($group->getType()) as $className) {
            $pmProxyPuppetClass = $puppetClassService->getByEnvironmentAndName($environment, $className);
            if (isset($pmProxyPuppetClass)) {
                $parameters = $groupParameterFactory->createRequiredFromTemplates($pmProxyPuppetClass->getParametersTemplates());
                $groupClass = new GroupClass();
                $groupClass->setName($className);
                $groupClass->setGroup($group);
                $groupClass->setParameters($parameters);
                $group->addClass($groupClass);
            } else {
                $this->serviceLocator->get('Logger')->warn("Unable to install default class '$className' for " . $group->getType() . " custom group !");
            }
        }

        $groupRepository->add($group);
        $this->writeRevisionLog($currentRevision, sprintf($this->translate('Create%s group %s'), $group->isCustom() ? ' ' . $group->getType() : '', $group->getName()));

        $this->flashMessenger()->addSuccessMessage(sprintf($this->translate('The group %s has been successfully created !'), $name));
        return $this->redirect()->toRoute('puppet-group', ['action' => 'show', 'id' => $group->getId()], [], true);
    }

    public function importAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }
        $currentRevision = $environment->getCurrentRevision();
        if ($currentRevision == null) {
            $this->flashMessenger()->addErrorMessage($this->translate('This environment is invalid, it has no current revision. Please contact administrator !'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        $request = $this->getRequest();

        $tmpdir = $this->getTmpDir();
        $confirmFile = $this->params()->fromPost('confirmFile');
        $confirmFileFullPath = '';
        if ($confirmFile) {
            if (!preg_match('/^[\w\.]+$/', $confirmFile)) {
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            $confirmFileFullPath = $tmpdir . '/' . $confirmFile;
            if (!file_exists($confirmFileFullPath)) {
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            if ($this->params()->fromPost('cancel')) {
                unlink($confirmFileFullPath);
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }
            $content = file_get_contents($confirmFileFullPath);
        } else {
            $formFactory = new Factory();
            /** @var Form $form */
            $form = $formFactory->createForm([
                'elements' => [
                    [
                        'spec' => [
                            'name' => 'file',
                            'type' => 'File',
                        ]
                    ],
                ],
                'input_filter' => [
                    'file' => [
                        'name' => 'file',
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'fileextension',
                                'options' => [
                                    'extension' => 'yaml'
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
            $form->setData(array_merge_recursive($request->getPost()->toArray(), $request->getFiles()->toArray()));

            if (!$form->isValid()) {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
                return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
            }

            $formData = $form->getData();
            $filename = $formData['file']['tmp_name'];
            $content = file_get_contents($filename);
        }
        $data = Yaml::parse($content);

        $revision = $environment->getCurrentRevision();
        /** @var GroupFactoryInterface $groupFactory */
        $groupFactory = $this->serviceLocator->get('groupFactory');
        $group = $groupFactory->createFromImportedData($data);
        $group->setRevision($revision);

        $groupRepository = $this->getServiceLocator()->get('GroupRepository');
        /** @var EnvironmentInterface $environment */
        $existingGroup = $groupRepository->getByNameAndRevision($group->getName(), $revision);
        if (isset($existingGroup)) {
            if (!$confirmFile) {
                $diff = new \Diff($this->dumpGroup($existingGroup), $this->dumpGroup($group));
                $render = $diff->render(new \Diff_Renderer_Html_SideBySide());
                $destFile = uniqid('kmb_', true) . '.yaml';
                move_uploaded_file($_FILES['file']['tmp_name'], $tmpdir . '/' . $destFile);
                return new ViewModel(['group' => $group, 'groupDiff' => $render, 'confirmFile' => $destFile]);
            }
            $groupRepository->remove($existingGroup);
        }
        $groupRepository->add($group);
        $this->writeRevisionLog($currentRevision, sprintf($this->translate('Import%s group %s'), $group->isCustom() ? ' ' . $group->getType() : '', $group->getName()));

        if ($confirmFile) {
            if (file_exists($confirmFileFullPath)) {
                unlink($confirmFileFullPath);
            }
        }
        return $this->redirect()->toRoute('puppet', ['controller' => 'groups', 'action' => 'index'], [], true);
    }

    /**
     * @param GroupInterface $group
     * @return array
     */
    protected function dumpGroup($group)
    {
        $fullDump = [
            $this->translate('include') . ': ' . $group->getIncludePattern(),
            $this->translate('exclude') . ': ' . $group->getExcludePattern(),
            $this->translate('classes') . ': ',
        ];
        return array_merge($fullDump, explode(PHP_EOL, Yaml::dump($group->dump(), 20, 4)));
    }

    /**
     * @return string
     */
    protected function getTmpDir()
    {
        $tmpdir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        if (strlen($tmpdir) <= 1) {
            return '/tmp';
        }
        return rtrim($tmpdir, '/');
    }
}
