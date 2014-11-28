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
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;

class ServerController extends AbstractActionController
{
    /** @var  Service\GroupClass */
    protected $groupClassService;

    /** @var  array */
    protected $config;

    public function showAction()
    {
        /** @var \KmbPuppetDb\Service\NodeInterface $nodeService */
        $nodeService = $this->serviceLocator->get('KmbPuppetDb\Service\Node');

        /** @var Response $response */
        $response = $this->getResponse();

        $dump = [];
        $hostname = $this->params()->fromRoute('hostname');
        if (isset($hostname)) {
            try {
                $node = $nodeService->getByName($hostname);
            } catch (InvalidArgumentException $exception) {
                $response->setContent($exception->getMessage() . PHP_EOL);
                return $response->setStatusCode(404);
            }
            $dump = $this->getNodeDump($node);
            $filename = $node->getName() . '.yaml';
        } else {
            $nodes = $nodeService->getAll();
            foreach ($nodes as $node) {
                /** @var NodeInterface $node */
                $dump[$node->getName()] = $this->getNodeDump($node);
            }
            ksort($dump);
            $filename = 'kamba-active-puppet-configuration-' . date('Ymd-His') . '.yaml';
        }

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

    /**
     * @return Service\GroupClass
     */
    public function getGroupClassService()
    {
        if ($this->groupClassService == null) {
            $this->groupClassService = $this->serviceLocator->get('KmbPuppet\Service\GroupClass');
        }
        return $this->groupClassService;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = $this->serviceLocator->get('Config');
        }
        return $this->config;
    }

    /**
     * @param $node
     * @return array
     */
    protected function getNodeDump($node)
    {
        $config = $this->getConfig();
        /** @var NodeInterface $node */
        $classes = $this->getGroupClassService()->getAllReleasedByNode($node);
        $dump = [];
        if (!empty($classes)) {
            foreach ($classes as $class) {
                $dump = ArrayUtils::merge($dump, [$class->getName() => $class->dump()]);
            }
        }
        ksort($dump, SORT_STRING);
        $dump = [
            'classes' => $dump,
            'parameters' => [
                'enc_id' => isset($config['puppet']['enc_id']) ? $config['puppet']['enc_id'] : 'production',
            ],
            'environment' => $node->getEnvironment(),
        ];
        return $dump;
    }
}
