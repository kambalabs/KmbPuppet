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

use KmbPuppet\Service;
use KmbPuppetDb\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;

class ServerController extends AbstractActionController
{
    public function showAction()
    {
        $config = $this->serviceLocator->get('Config');
        /** @var \KmbPuppetDb\Service\NodeInterface $nodeService */
        $nodeService = $this->serviceLocator->get('KmbPuppetDb\Service\Node');

        /** @var Response $response */
        $response = $this->getResponse();

        try {
            /** @var \KmbPuppetDb\Model\NodeInterface $node */
            $node = $nodeService->getByName($this->params()->fromRoute('hostname'));
        } catch (InvalidArgumentException $exception) {
            $response->setContent($exception->getMessage() . PHP_EOL);
            return $response->setStatusCode(404);
        }

        /** @var Service\GroupInterface $groupService */
        $groupService = $this->serviceLocator->get('KmbPuppet\Service\Group');
        $groups = $groupService->getAllReleasedByNode($node);
        $dump = [];
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $dump = ArrayUtils::merge($dump, $group->dump());
            }
        }

        $content = Yaml::dump(
            [
                'classes' => $dump,
                'parameters' => [
                    'encId' => isset($config['puppet']['encId']) ? $config['puppet']['encId'] : 'production',
                ],
                'environment' => $node->getEnvironment(),
            ],
            20,
            4
        );
        $response->setContent($content);

        $filename = $node->getName() . '.yaml';
        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/x-yaml')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->addHeaderLine('Content-Length', strlen($content));

        return $response;
    }
}
