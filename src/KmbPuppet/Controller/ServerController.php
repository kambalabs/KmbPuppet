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
use KmbPuppetDb\Model\NodeInterface;
use Symfony\Component\Yaml\Yaml;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class ServerController extends AbstractRestfulController
{
    public function getList()
    {
        /** @var \KmbPuppetDb\Service\NodeInterface $nodeService */
        $nodeService = $this->serviceLocator->get('KmbPuppetDb\Service\Node');
        /** @var \KmbPuppet\Service\NodeInterface $puppetNodeService */
        $puppetNodeService = $this->serviceLocator->get('KmbPuppet\Service\Node');

        /** @var Response $response */
        $response = $this->getResponse();

        $dump = [];
        $nodes = $nodeService->getAll();
        foreach ($nodes as $node) {
            /** @var NodeInterface $node */
            $dump[$node->getName()] = $puppetNodeService->getActivePuppetConfiguration($node);
        }
        ksort($dump);
        $filename = 'kamba-active-puppet-configuration-' . date('Ymd-His') . '.yaml';

        $content = Yaml::dump(
            $dump,
            20,
            2
        );

        $response->setContent($content);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/x-yaml')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->addHeaderLine('Content-Length', strlen($content));

        return $response;
    }

    public function get($id)
    {
        /** @var \KmbPuppetDb\Service\NodeInterface $nodeService */
        $nodeService = $this->serviceLocator->get('KmbPuppetDb\Service\Node');
        /** @var \KmbPuppet\Service\NodeInterface $puppetNodeService */
        $puppetNodeService = $this->serviceLocator->get('KmbPuppet\Service\Node');

        /** @var Response $response */
        $response = $this->getResponse();

        try {
            $node = $nodeService->getByName($id);
        } catch (InvalidArgumentException $exception) {
            $response->setContent($exception->getMessage() . PHP_EOL);
            return $response->setStatusCode(404);
        }
        $dump = $puppetNodeService->getActivePuppetConfiguration($node);
        $filename = $node->getName() . '.yaml';

        $content = Yaml::dump(
            $dump,
            20,
            2
        );

        $response->setContent($content);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/x-yaml')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->addHeaderLine('Content-Length', strlen($content));

        return $response;
    }

    public function update($id, $data)
    {
        return new JsonModel();
    }
}
