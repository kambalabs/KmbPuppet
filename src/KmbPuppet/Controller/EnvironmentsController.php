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
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Service\EnvironmentRepositoryInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\Revision;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\UserInterface;
use KmbDomain\Service\UserRepositoryInterface;
use KmbPmProxy\Model\PuppetModule;
use KmbPmProxy\Service\PuppetModule as PuppetModuleService;
use KmbPmProxy\Exception\ExceptionInterface;
use KmbPmProxy\Exception\NotFoundException;
use KmbPmProxy\Exception\RuntimeException;
use KmbPmProxy\Hydrator\RevisionHydratorInterface;
use Symfony\Component\Yaml\Yaml;
use Zend\Authentication\AuthenticationService;
use Zend\I18n\Validator\Alnum;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Exception;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class EnvironmentsController extends AbstractActionController implements AuthenticatedControllerInterface
{
    /** @var EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var \KmbPmProxy\Service\EnvironmentInterface */
    protected $pmProxyEnvironmentService;

    public function indexAction()
    {
        $data = ['environments' => $this->environmentRepository->getAllRoots()];
        if ($this->environmentRepository->getDefault() === null) {
            $this->globalMessenger()->addDangerMessage($this->translate("<h4>Warning !</h4><p>You should choose a default environment !</p>"));
        }
        return new ViewModel($data);
    }

    public function createAction()
    {
        /** @var EnvironmentInterface $parent */
        $parent = $this->environmentRepository->getById(intval($this->params()->fromPost('parent')));
        if (
            ($parent == null && !$this->isGranted('manageAllEnv')) ||
            ($parent != null && !$this->isGranted('manageEnv', $parent))
        ) {
            throw new UnauthorizedException();
        }

        $aggregateRoot = new Environment();
        $aggregateRoot->setCurrentRevision(new Revision());
        $aggregateRoot->setLastReleasedRevision(new Revision());
        $this->signLastReleasedRevisions($aggregateRoot);

        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $this->environmentRepository->add($aggregateRoot);
            try {
                $this->pmProxyEnvironmentService->save($aggregateRoot);
                $normalizedName = $aggregateRoot->getNormalizedName();
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully created !"), $normalizedName));
                $this->writeLog(sprintf($this->translate("Add environment %s"), $normalizedName));
            } catch (ExceptionInterface $e) {
                $this->environmentRepository->remove($aggregateRoot);
                $this->flashMessenger()->addErrorMessage(
                    sprintf($this->translate("Environment %s could no be created on the puppet master"), $aggregateRoot->getNormalizedName()) .
                    ' : ' . $e->getMessage()
                );
            }
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'environments', 'action' => 'index'], [], true);
    }

    public function duplicateAction()
    {
        /** @var EnvironmentInterface $cloneFrom */
        $cloneFrom = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));
        if ($cloneFrom == null) {
            return $this->notFoundAction();
        }
        $parent = $cloneFrom->getParent();
        if (
            ($parent == null && !$this->isGranted('manageAllEnv')) ||
            ($parent != null && !$this->isGranted('manageEnv', $parent))
        ) {
            throw new UnauthorizedException();
        }

        $aggregateRoot = clone $cloneFrom;
        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $this->signLastReleasedRevisions($aggregateRoot);
            $this->environmentRepository->add($aggregateRoot);
            try {
                $this->pmProxyEnvironmentService->save($aggregateRoot, $cloneFrom);
                $normalizedName = $aggregateRoot->getNormalizedName();
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully created !"), $normalizedName));
                $this->writeLog(sprintf($this->translate("Duplicate environment %s"), $normalizedName));
            } catch (ExceptionInterface $e) {
                $this->environmentRepository->remove($aggregateRoot);
                $this->flashMessenger()->addErrorMessage(
                    sprintf($this->translate("Environment %s could no be created on the puppet master"), $aggregateRoot->getNormalizedName()) .
                    ' : ' . $e->getMessage()
                );
            }
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'environments', 'action' => 'index'], [], true);
    }

    public function updateAction()
    {
        /** @var EnvironmentInterface $parent */
        $parent = $this->environmentRepository->getById($this->params()->fromPost('parent'));
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }
        if (
            ($parent == null && !$this->isGranted('manageAllEnv')) ||
            ($parent != null && !$this->isGranted('manageEnv', $parent))
        ) {
            throw new UnauthorizedException();
        }

        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $aggregateRoot->setDefault($this->params()->fromPost('default', 0) !== 0);
            try {
                $this->environmentRepository->update($aggregateRoot);
                $this->pmProxyEnvironmentService->save($aggregateRoot);
                $normalizedName = $aggregateRoot->getNormalizedName();
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully updated !"), $normalizedName));
                $this->writeLog(sprintf($this->translate("Update environment %s"), $normalizedName));
            } catch (ExceptionInterface $e) {
                $this->flashMessenger()->addErrorMessage(
                    sprintf($this->translate("Environment %s could no be updated on the puppet master"), $aggregateRoot->getName()) .
                    ' : ' . $e->getMessage()
                );
            }
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'environments', 'action' => 'index'], [], true);
    }

    public function removeAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if ($aggregateRoot->hasChildren() || !$this->isGranted('manageEnv', $aggregateRoot)) {
            throw new UnauthorizedException();
        }

        try {
            $normalizedName = $aggregateRoot->getNormalizedName();
            try {
                $this->pmProxyEnvironmentService->remove($aggregateRoot);
            } catch (NotFoundException $e) {
            }
            $this->environmentRepository->remove($aggregateRoot);
            $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully removed !"), $normalizedName));
            $this->writeLog(sprintf($this->translate("Remove environment %s"), $normalizedName));
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                sprintf($this->translate("Environment %s could no be removed on the puppet master"), $aggregateRoot->getName()) .
                ' : ' . $e->getMessage()
            );
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'environments', 'action' => 'index'], [], true);
    }

    public function diffAction()
    {
        /** @var EnvironmentInterface $from */
        $from = $this->environmentRepository->getById(intval($this->params()->fromQuery('from')));
        /** @var EnvironmentInterface $to */
        $to = $this->environmentRepository->getById(intval($this->params()->fromQuery('to')));

        if ($from === null || $to === null) {
            return $this->notFoundAction();
        }

        /** @var RevisionHydratorInterface $revisionHydrator */
        $revisionHydrator = $this->serviceLocator->get('pmProxyRevisionHydrator');
        /** @var PuppetModuleService $puppetModuleService */
        $puppetModuleService = $this->serviceLocator->get('pmProxyPuppetModuleService');
        $fromModules = $puppetModuleService->getAllInstalledByEnvironment($from);
        $toModules = $puppetModuleService->getAllInstalledByEnvironment($to);

        $fromRevision = $from->getCurrentRevision();
        $revisionHydrator->hydrate($fromModules, $fromRevision);
        $toRevision = $to->getCurrentRevision();
        $revisionHydrator->hydrate($toModules, $toRevision);

        return new ViewModel([
            'from' => $from,
            'to' => $to,
            'modulesDiff' => $this->diffModules($fromModules, $toModules),
            'groupsOrderingDiff' => $this->diffOrderingGroupsRevisions($fromRevision, $toRevision),
            'groupsDiffs' => $this->diffsGroupsRevisions($fromRevision, $toRevision),
        ]);
    }

    public function usersAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if (!$this->isGranted('readEnv', $aggregateRoot)) {
            throw new UnauthorizedException();
        }

        $data = [];
        foreach ($aggregateRoot->getUsers() as $user) {
            /** @var UserInterface $user */
            $data[] = [
                $user->getLogin(),
                $user->getName(),
                $user->getRole() . '<button class="btn btn-xs btn-danger remove-user pull-right" data-environment-id="' . $aggregateRoot->getId() . '" data-user-id="' . $user->getId() . '"><span class="glyphicon glyphicon-remove"></span></button>',
            ];
        }

        return new JsonModel([
            'data' => $data
        ]);
    }

    public function availableUsersAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if (!$this->isGranted('readEnv', $aggregateRoot)) {
            throw new UnauthorizedException();
        }

        $availableUsers = [];
        foreach ($this->userRepository->getAllAvailableForEnvironment($aggregateRoot) as $user) {
            /** @var UserInterface $user */
            $availableUsers[] = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
            ];
        }

        return new JsonModel([
            'users' => $availableUsers,
        ]);
    }

    public function addUsersAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));
        $aggregateRoot->getParent(); // Load parent TODO: remove this ASAP

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if (!$this->isGranted('manageEnv', $aggregateRoot)) {
            throw new UnauthorizedException();
        }

        $users = [];
        foreach ($this->params()->fromPost('users', []) as $userId) {
            $user = $this->userRepository->getById(intval($userId));
            if ($user !== null) {
                $users[] = $user;
            }
        }

        $aggregateRoot->addUsers($users);
        $this->environmentRepository->update($aggregateRoot);
        $usersList = implode(', ', array_map(function ($user) { return $user->getName(); }, $users));
        $this->writeLog(sprintf($this->translatePlural("Add user to environment %s (%s)", "Add users to environment %s (%s)", count($usersList)), $aggregateRoot->getNormalizedName(), $usersList));
        return new JsonModel();
    }

    public function removeUserAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById(intval($this->params()->fromRoute('id')));
        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        $aggregateRoot->getParent(); // Load parent TODO: remove this ASAP

        if (!$this->isGranted('manageEnv', $aggregateRoot)) {
            throw new UnauthorizedException();
        }

        $userId = intval($this->params()->fromRoute('userId'));
        /** @var UserInterface $user */
        $user = $this->userRepository->getById($userId);
        if ($user) {
            $aggregateRoot->removeUserById($userId);
            $this->environmentRepository->update($aggregateRoot);
            $this->writeLog(sprintf($this->translate("Remove %s from environment %s"), $user->getName(), $aggregateRoot->getNormalizedName()));
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'environments', 'action' => 'index'], [], true);
    }

    /**
     * Set Repository.
     *
     * @param EnvironmentRepositoryInterface $environmentRepository
     * @return EnvironmentsController
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get Repository.
     *
     * @return EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }

    /**
     * Set UserRepository.
     *
     * @param UserRepositoryInterface $userRepository
     * @return EnvironmentsController
     */
    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    /**
     * Get UserRepository.
     *
     * @return UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * Set PmProxy Environment Service.
     *
     * @param \KmbPmProxy\Service\EnvironmentInterface $pmProxyEnvironmentService
     * @return EnvironmentsController
     */
    public function setPmProxyEnvironmentService($pmProxyEnvironmentService)
    {
        $this->pmProxyEnvironmentService = $pmProxyEnvironmentService;
        return $this;
    }

    /**
     * Get PmProxy Environment Service.
     *
     * @return \KmbPmProxy\Service\EnvironmentInterface
     */
    public function getPmProxyEnvironmentService()
    {
        return $this->pmProxyEnvironmentService;
    }

    /**
     * @param EnvironmentInterface $aggregateRoot
     * @param EnvironmentInterface $parent
     * @return bool
     */
    protected function validate($aggregateRoot, $parent)
    {
        $newName = $this->params()->fromPost('name');

        $validator = new Alnum();
        if (!$validator->isValid($newName)) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("'%s' is not a valid name (alphanumeric only) !"), $newName));
        }

        if ($aggregateRoot->isAncestorOf($parent) || ($parent !== null && $aggregateRoot->getId() == $parent->getId())) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("For obvious reasons, environment %s can't be the parent of %s !"), $parent->getNormalizedName(), $aggregateRoot->getNormalizedName()));
        }

        if ($parent != null) {
            $parentHasChildWithSameName = $parent->hasChildWithName($newName);
            if (
                ($aggregateRoot->hasParent() && $aggregateRoot->getParent()->getId() != $parent->getId() && $parentHasChildWithSameName) ||
                (!$aggregateRoot->hasParent() && $parentHasChildWithSameName)
            ) {
                $this->flashMessenger()->addErrorMessage(sprintf($this->translate("Environment %s has already a child named %s !"), $parent->getName(), $newName));
            }
        } elseif (
            ($aggregateRoot->hasParent() || $aggregateRoot->getName() !== $newName) &&
            $this->environmentRepository->getRootByName($newName) !== null
        ) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("Root environment %s already exists !"), $newName));
        }

        return !$this->flashMessenger()->hasCurrentErrorMessages();
    }

    /**
     * @param EnvironmentInterface $aggregateRoot
     */
    protected function signLastReleasedRevisions($aggregateRoot)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');

        $lastReleasedRevision = $aggregateRoot->getLastReleasedRevision();
        $lastReleasedRevision->setReleasedAt(new \DateTime());
        $lastReleasedRevision->setReleasedBy($authenticationService->getIdentity()->getName());
        $lastReleasedRevision->setComment($this->translate('Initialization'));

        if ($aggregateRoot->hasChildren()) {
            foreach ($aggregateRoot->getChildren() as $child) {
                $this->signLastReleasedRevisions($child);
            }
        }
    }

    /**
     * @param PuppetModule[] $from
     * @param PuppetModule[] $to
     * @return \Diff
     */
    protected function diffModules($from, $to)
    {
        $fromModules = isset($from) ? array_values(array_map(function (PuppetModule $module) {
            return $module->getName() . ' ' . $module->getVersion();
        }, $from)) : [];
        $toModules = isset($to) ? array_values(array_map(function (PuppetModule $module) {
            return $module->getName() . ' ' . $module->getVersion();
        }, $to)) : [];
        $diff = new \Diff($fromModules, $toModules);
        return $diff->render(new \Diff_Renderer_Html_SideBySide());
    }

    /**
     * @param RevisionInterface $from
     * @param RevisionInterface $to
     * @return \Diff
     */
    protected function diffOrderingGroupsRevisions($from, $to)
    {
        $fromGroups = isset($from) ? array_map(function (GroupInterface $group) {
            return $group->getName();
        }, $from->getGroups()) : [];
        $toGroups = isset($to) ? array_map(function (GroupInterface $group) {
            return $group->getName();
        }, $to->getGroups()) : [];
        $diff = new \Diff($fromGroups, $toGroups);
        return $diff->render(new \Diff_Renderer_Html_SideBySide());
    }

    /**
     * @param RevisionInterface $from
     * @param RevisionInterface $to
     * @return \Diff[]
     */
    protected function diffsGroupsRevisions($from, $to)
    {
        $diffs = [];
        if (isset($to) && $to->hasGroups()) {
            foreach ($to->getGroups() as $toGroup) {
                if (isset($from)) {
                    $fromGroup = $from->getGroupByName($toGroup->getName());
                    if (isset($fromGroup)) {
                        $diff = new \Diff($this->dumpGroup($fromGroup), $this->dumpGroup($toGroup));
                    } else {
                        $diff = new \Diff([], $this->dumpGroup($toGroup));
                    }
                    $render = $diff->render(new \Diff_Renderer_Html_SideBySide());
                    if (!empty($render)) {
                        $diffs[$toGroup->getName()] = $render;
                    }
                }
            }
        }
        if (isset($from) && $from->hasGroups()) {
            foreach ($from->getGroups() as $fromGroup) {
                if (isset($to)) {
                    $toGroup = $to->getGroupByName($fromGroup->getName());
                    if (!isset($toGroup)) {
                        $diff = new \Diff($this->dumpGroup($fromGroup), []);
                        $render = $diff->render(new \Diff_Renderer_Html_SideBySide());
                        if (!empty($render)) {
                            $diffs[$fromGroup->getName()] = $render;
                        }
                    }
                }
            }
        }
        return $diffs;
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
}
