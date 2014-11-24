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
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionServiceInterface;
use KmbPmProxy\Hydrator\RevisionHydratorInterface;
use KmbPmProxy\Service\PuppetModule as PuppetModuleService;
use KmbPuppet\Service;
use Symfony\Component\Yaml\Yaml;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class RevisionController extends AbstractActionController implements AuthenticatedControllerInterface
{
    public function releaseAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->serviceLocator->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return new ViewModel();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var RevisionInterface $revision */
        $revision = $this->serviceLocator->get('RevisionRepository')->getById($this->params()->fromRoute('id'));
        if ($revision == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
        }

        $comment = $this->params()->fromPost('comment');
        if (empty($comment)) {
            $this->flashMessenger()->addErrorMessage($this->translate('You must enter a comment'));
            return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
        }

        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');

        /** @var RevisionServiceInterface $revisionService */
        $revisionService = $this->serviceLocator->get('revisionService');
        $revisionService->release($revision, $authenticationService->getIdentity(), $comment);

        return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
    }

    public function removeAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->serviceLocator->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return new ViewModel();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var RevisionInterface $revision */
        $revision = $this->serviceLocator->get('RevisionRepository')->getById($this->params()->fromRoute('id'));
        if ($revision == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
        }

        /** @var RevisionServiceInterface $revisionService */
        $revisionService = $this->serviceLocator->get('revisionService');
        $revisionService->remove($revision);

        return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
    }

    public function exportAction()
    {
        /** @var EnvironmentInterface $environment */
        $environment = $this->serviceLocator->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));
        if ($environment == null) {
            $this->globalMessenger()->addDangerMessage($this->translate('<h4>Warning !</h4><p>You have to select an environment first !</p>'));
            return new ViewModel();
        }
        if (!$this->isGranted('manageEnv', $environment)) {
            throw new UnauthorizedException();
        }

        /** @var RevisionInterface $revision */
        $revision = $this->serviceLocator->get('RevisionRepository')->getById($this->params()->fromRoute('id'));
        if ($revision == null) {
            return $this->redirect()->toRoute('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true);
        }

        /** @var RevisionHydratorInterface $revisionHydrator */
        $revisionHydrator = $this->serviceLocator->get('pmProxyRevisionHydrator');
        /** @var PuppetModuleService $puppetModuleService */
        $puppetModuleService = $this->serviceLocator->get('pmProxyPuppetModuleService');
        $modules = $puppetModuleService->getAllByEnvironment($environment);
        $revisionHydrator->hydrate($modules, $revision);

        $groups = $revision->hasGroups() ? $revision->getGroups() : [];
        $data = [
            'released_at' => $revision->getReleasedAt() ? $revision->getReleasedAt()->format(\DateTime::W3C) : '',
            'released_by' => $revision->getReleasedBy() ? : '',
            'comment' => $revision->getComment() ? : '',
            'groups' => array_map(function (GroupInterface $group) {
                return [
                    'name' => $group->getName(),
                    'include_pattern' => $group->getIncludePattern(),
                    'exclude_pattern' => $group->getExcludePattern(),
                    'classes' => $group->dump(),
                ];
            }, $groups),
        ];
        $content = Yaml::dump($data, 20, 4);

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setContent($content);

        $filename = $environment->getNormalizedName() . '-' . date('Ymd-His') . '.yaml';

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/x-yaml')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->addHeaderLine('Content-Length', strlen($content));

        return $response;
    }
}
