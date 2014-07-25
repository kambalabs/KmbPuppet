<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/multimediabs/kamba for the canonical source repository
 *
 * This file is part of KmbPuppet.
 *
 * KmbPuppet is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * KmbPuppet is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KmbPuppet.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppet\Service;

use KmbPuppet\Exception\RuntimeException;
use KmbPuppet\Model\EnvironmentInterface;
use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Log\Logger;
use Zend\Stdlib\Hydrator\HydratorInterface;

class PmProxy implements PmProxyInterface
{
    /** @var string */
    protected $baseUri;

    /** @var HydratorInterface */
    protected $environmentHydrator;

    /** @var Client */
    protected $httpClient;

    /** @var Logger */
    protected $logger;

    /**
     * Create or update an environment on the Puppet Master
     *
     * @param EnvironmentInterface $environment
     * @return PmProxy
     * @throws \KmbPuppet\Exception\RuntimeException
     */
    public function save(EnvironmentInterface $environment)
    {
        $content = Json::encode($this->getEnvironmentHydrator()->extract($environment));
        return $this->send(Request::METHOD_PUT, '/environments/' . $environment->getId(), $content);
    }

    /**
     * Remove an environment on the Puppet Master
     *
     * @param EnvironmentInterface $environment
     * @return PmProxy
     */
    public function remove(EnvironmentInterface $environment)
    {
        return $this->send(Request::METHOD_DELETE, '/environments/' . $environment->getId());
    }

    /**
     * Set BaseUri.
     *
     * @param string $baseUri
     * @return PmProxy
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    /**
     * Get BaseUri.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Set EnvironmentHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $environmentHydrator
     * @return PmProxy
     */
    public function setEnvironmentHydrator($environmentHydrator)
    {
        $this->environmentHydrator = $environmentHydrator;
        return $this;
    }

    /**
     * Get EnvironmentHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getEnvironmentHydrator()
    {
        return $this->environmentHydrator;
    }

    /**
     * Set HttpClient.
     *
     * @param \Zend\Http\Client $httpClient
     * @return PmProxy
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Get HttpClient.
     *
     * @return \Zend\Http\Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Set Logger.
     *
     * @param \Zend\Log\Logger $logger
     * @return PmProxy
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get Logger.
     *
     * @return \Zend\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param $uri
     * @return string
     */
    protected function getUri($uri)
    {
        return ltrim($this->getBaseUri(), '/') . $uri;
    }

    /**
     * @param $start
     * @param $statusLine
     * @param $uri
     */
    protected function logRequest($start, $statusLine, $uri)
    {
        $duration = intval((microtime(true) - $start) * 1000);
        $splittedUri = explode('?', $uri);
        $uriToLog = array_shift($splittedUri);
        $this->getLogger()->debug("[$duration ms] [" . $statusLine . "] $uriToLog");
//        $this->getLogger()->debug("[$duration ms] [" . $statusLine . "] $uri");
    }

    /**
     * @param $method
     * @param $uri
     * @param $content
     * @return PmProxy
     * @throws \KmbPuppet\Exception\RuntimeException
     */
    protected function send($method, $uri, $content = '')
    {
        $request = new Request();
        $request->setUri($this->getUri($uri));
        $request->setMethod($method);
        $headers = new Headers();
        $headers->addHeaders(['Accept' => 'application/json']);
        $request->setHeaders($headers);
        $request->setContent($content);

        $start = microtime(true);
        $httpResponse = $this->getHttpClient()->send($request);
        $this->logRequest($start, $httpResponse->renderStatusLine(), $uri);

        if (!$httpResponse->isSuccess()) {
            $this->getLogger()->err('[' . $httpResponse->renderStatusLine() . '] ' . $httpResponse->getBody());
            throw new RuntimeException('Unexpected PmProxy Response: ' . $httpResponse->renderStatusLine());
        }

        return $this;
    }
}
