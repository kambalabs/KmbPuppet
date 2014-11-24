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
use KmbDomain\Model\RevisionRepositoryInterface;
use KmbPmProxy\Hydrator\RevisionHydratorInterface;
use KmbPmProxy\Service\PuppetModule as PuppetModuleService;
use KmbPuppet\Service;
use Symfony\Component\Yaml\Yaml;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Exception\UnauthorizedException;

class RevisionsController extends AbstractActionController implements AuthenticatedControllerInterface
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

        return new ViewModel([
            'environment' => $environment,
            'currentRevision' => $environment->getCurrentRevision(),
            'lastReleasedRevision' => $environment->getLastReleasedRevision(),
            'revisions' => $environment->getReleasedRevisions(),
        ]);
    }

    public function diffAction()
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

        /** @var RevisionRepositoryInterface $revisionRepository */
        $revisionRepository = $this->getServiceLocator()->get('RevisionRepository');

        /** @var RevisionHydratorInterface $revisionHydrator */
        $revisionHydrator = $this->serviceLocator->get('pmProxyRevisionHydrator');
        /** @var PuppetModuleService $puppetModuleService */
        $puppetModuleService = $this->serviceLocator->get('pmProxyPuppetModuleService');
        $modules = $puppetModuleService->getAllByEnvironment($environment);

        /** @var RevisionInterface $from */
        $from = $revisionRepository->getById($this->params()->fromQuery('from'));
        if ($from != null) {
            $revisionHydrator->hydrate($modules, $from);
        }
        /** @var RevisionInterface $to */
        $to = $revisionRepository->getById($this->params()->fromQuery('to'));
        if ($to != null) {
            $revisionHydrator->hydrate($modules, $to);
        }

        return new ViewModel([
            'groupsOrderingDiff' => $this->diffOrderingGroupsRevisions($from, $to),
            'groupsDiffs' => $this->diffsGroupsRevisions($from, $to),
        ]);
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
