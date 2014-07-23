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

use KmbPuppet\Model\EnvironmentInterface;
use KmbPuppet\Model\EnvironmentRepositoryInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class EnvironmentsController extends AbstractActionController
{
    public function indexAction()
    {
        $model = new ViewModel();
        /** @var EnvironmentRepositoryInterface $environmentRepository */
        $environmentRepository = $this->getServiceLocator()->get('EnvironmentRepository');
        $model->setVariable('roots', $environmentRepository->getAllRoots());
        $environments = $environmentRepository->getAll();
        $model->setVariable('environments', $environments);
        return $model;
    }

    public function removeAction()
    {
        /** @var EnvironmentRepositoryInterface $repository */
        $repository = $this->getServiceLocator()->get('EnvironmentRepository');
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = $repository->getById($this->params()->fromRoute('id'));

        if ($aggregateRoot === null) {
            return $this->notFoundAction();
        }

        if ($aggregateRoot->hasChildren()) {
            throw new UnauthorizedException();
        }

        $repository->remove($aggregateRoot);

        return $this->redirect()->toRoute('puppet/default', ['controller' => 'environments']);
    }
}
