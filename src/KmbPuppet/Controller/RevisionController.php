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
use KmbPuppet\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class RevisionController extends AbstractActionController
{
    public function releaseAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->getServiceLocator()->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            return new ViewModel(['error' => $this->translate('You have to select an environment first !')]);
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        $comment = $this->params()->fromPost('comment');
        if (empty($comment)) {
            $this->flashMessenger()->addErrorMessage($this->translate('You must enter a comment'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
        }

        return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
    }
}
