<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/multimediabs/kamba for the canonical source repository
 *
 * This file is part of kamba.
 *
 * kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppet\Controller;

use KmbPuppet\Model\Environment;
use KmbPuppet\Model\EnvironmentInterface;
use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\I18n\Validator\Alnum;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class EnvironmentsController extends AbstractActionController
{
    /** @var EnvironmentRepositoryInterface */
    protected $repository;

    public function indexAction()
    {
        return new ViewModel(['environments' => $this->repository->getAllRoots()]);
    }

    public function createAction()
    {
        /** @var EnvironmentInterface $parent */
        $parent = $this->repository->getById($this->params()->fromPost('parent'));
        $aggregateRoot = new Environment();

        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $this->repository->add($aggregateRoot);
            $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully created !"), $aggregateRoot->getName()));
        }

        return $this->redirect()->toRoute('puppet/default', ['controller' => 'environments']);
    }

    public function updateAction()
    {
        /** @var EnvironmentInterface $parent */
        $parent = $this->repository->getById($this->params()->fromPost('parent'));
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->repository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if ($this->validate($aggregateRoot, $parent)) {
            $aggregateRoot->setName($this->params()->fromPost('name'));
            $aggregateRoot->setParent($parent);
            $this->repository->update($aggregateRoot);
            $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully updated !"), $aggregateRoot->getName()));
        }

        return $this->redirect()->toRoute('puppet/default', ['controller' => 'environments']);
    }

    public function removeAction()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $this->repository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if ($aggregateRoot->hasChildren()) {
            throw new UnauthorizedException();
        }

        $this->repository->remove($aggregateRoot);
        $this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Environment %s has been successfully removed !"), $aggregateRoot->getName()));

        return $this->redirect()->toRoute('puppet/default', ['controller' => 'environments']);
    }

    /**
     * Set Repository.
     *
     * @param \KmbPuppet\Model\EnvironmentRepositoryInterface $repository
     * @return EnvironmentsController
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Get Repository.
     *
     * @return \KmbPuppet\Model\EnvironmentRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param EnvironmentInterface $aggregateRoot
     * @param EnvironmentInterface $parent
     * @return bool
     */
    protected function validate($aggregateRoot, $parent)
    {
        $validator = new Alnum();
        if (!$validator->isValid($this->params()->fromPost('name'))) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("'%s' is not a valid name (alphanumeric only) !"), $this->params()->fromPost('name')));
        }

        if ($aggregateRoot->isAncestorOf($parent) || ($parent !== null && $aggregateRoot->getId() == $parent->getId())) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("For obvious reasons, environment %s can't be the parent of %s !"), $parent->getNormalizedName(), $aggregateRoot->getNormalizedName()));
        }

        if ($parent != null) {
            $parentHasChildWithSameName = $parent->hasChildWithName($this->params()->fromPost('name'));
            if (
                ($aggregateRoot->hasParent() && $aggregateRoot->getParent()->getId() != $parent->getId() && $parentHasChildWithSameName) ||
                (!$aggregateRoot->hasParent() && $parentHasChildWithSameName)
            ) {
                $this->flashMessenger()->addErrorMessage(sprintf($this->translate("Environment %s has already a child named %s !"), $parent->getName(), $this->params()->fromPost('name')));
            }
        } elseif ($this->repository->getRootByName($this->params()->fromPost('name')) !== null) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate("Root environment %s already exists !"), $this->params()->fromPost('name')));
        }

        return !$this->flashMessenger()->hasCurrentErrorMessages();
    }
}
