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

use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\EnvironmentRepositoryInterface;
use KmbDomain\Model\UserInterface;
use KmbDomain\Model\UserRepositoryInterface;
use KmbPmProxy\Exception\ExceptionInterface;
use KmbPmProxy\Exception\NotFoundException;
use KmbPmProxy\Exception\RuntimeException;
use Zend\I18n\Validator\Alnum;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class EnvironmentsController extends AbstractActionController
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
            $data['message'] = $this->translate("WARNING ! You should choose a default environment !");
        }
        return new ViewModel($data);
    }

    public function createAction()
    {
        /** @var EnvironmentInterface $parent */
        $parent = $this->environmentRepository->getById($this->params()->fromPost('parent'));
        $aggregateRoot = new Environment();

        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $this->environmentRepository->add($aggregateRoot);
            try {
                $this->pmProxyEnvironmentService->save($aggregateRoot);
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully created !"), $aggregateRoot->getName()));
            } catch (ExceptionInterface $e) {
                $this->environmentRepository->remove($aggregateRoot);
                $this->flashMessenger()->addErrorMessage(
                    sprintf($this->translate("Environment %s could no be created on the puppet master"), $aggregateRoot->getName()) .
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
        $aggregateRoot = $this->environmentRepository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $aggregateRoot->setDefault($this->params()->fromPost('default', 0) !== 0);
            try {
                $this->environmentRepository->update($aggregateRoot);
                $this->pmProxyEnvironmentService->save($aggregateRoot);
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully updated !"), $aggregateRoot->getName()));
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
        $aggregateRoot = $this->environmentRepository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if ($aggregateRoot->hasChildren()) {
            throw new UnauthorizedException();
        }

        try {
            try {
                $this->pmProxyEnvironmentService->remove($aggregateRoot);
            } catch (NotFoundException $e) {
            }
            $this->environmentRepository->remove($aggregateRoot);
            $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully removed !"), $aggregateRoot->getName()));
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                sprintf($this->translate("Environment %s could no be removed on the puppet master"), $aggregateRoot->getName()) .
                ' : ' . $e->getMessage()
            );
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'environments', 'action' => 'index'], [], true);
    }

    public function usersAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
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
        $aggregateRoot = $this->environmentRepository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
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
        $aggregateRoot = $this->environmentRepository->getById($this->params()->fromRoute('id'));
        $aggregateRoot->getParent(); // Load parent TODO: remove this ASAP

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        $users = [];
        foreach ($this->params()->fromPost('users', []) as $userId) {
            $user = $this->userRepository->getById($userId);
            if ($user !== null) {
                $users[] = $user;
            }
        }

        $aggregateRoot->addUsers($users);
        $this->environmentRepository->update($aggregateRoot);
        return new JsonModel();
    }

    public function removeUserAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->environmentRepository->getById($this->params()->fromRoute('id'));
        $aggregateRoot->getParent(); // Load parent TODO: remove this ASAP

        $aggregateRoot->removeUserById($this->params()->fromRoute('userId'));
        $this->environmentRepository->update($aggregateRoot);

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
}
