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
namespace KmbPuppet\Listener;

use KmbBase\Controller\Plugin\GlobalMessenger;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Service\EnvironmentRepositoryInterface;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\I18n\View\Helper\Translate;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Url;

class RevisionLogsListener extends AbstractListenerAggregate
{
    /** @var int */
    protected $priority = 100;

    /** @var  ServiceManager */
    protected $serviceManager;

    /** @var  Translate */
    protected $translate;

    /** @var  Url */
    protected $url;

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(
            MvcEvent::EVENT_DISPATCH,
            function (MvcEvent $event) {
                if ($this->serviceManager == null) {
                    $this->serviceManager = $event->getApplication()->getServiceManager();
                    $this->translate = $this->serviceManager->get('ViewHelperManager')->get('translate');
                    $this->url = $this->serviceManager->get('ViewHelperManager')->get('url');
                }
                /** @var EnvironmentRepositoryInterface $environmentRepository */
                $environmentRepository = $this->serviceManager->get('EnvironmentRepository');
                /** @var EnvironmentInterface $environment */
                $environment = $environmentRepository->getById($event->getRouteMatch()->getParam('envId', 0));
                if ($environment != null) {
                    $revision = $environment->getCurrentRevision();
                    if ($revision != null && $revision->hasLogs()) {
                        $log = $revision->getLastLog();
                        /** @var GlobalMessenger $globalMessenger */
                        $globalMessenger = $this->serviceManager->get('ControllerPluginManager')->get('globalMessenger');
                        /** @var AuthenticationService $authService */
                        $authService = $this->serviceManager->get('kmbAuthService');
                        $title = '<h4>' . $this->translate('Warning !') . '</h4>';
                        $interval = $log->getCreatedAt()->diff(new \DateTime());
                        if ($log->getCreatedBy() === $authService->getIdentity()->getName()) {
                            $message =
                                '<p>' .
                                sprintf($this->translate("You haven't saved the changes you made %s !"), $this->translateInterval($interval)) . ' ' .
                                sprintf($this->translate('<a href="%s">Click here</a> for more details.'), $this->url('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true)) .
                                '</p>';
                        } else {
                            $message =
                                '<p>' .
                                sprintf($this->translate("I haven't saved the changes I made %s !"), $this->translateInterval($interval)) . ' ' .
                                sprintf($this->translate('<a href="%s">Click here</a> for more details.'), $this->url('puppet', ['controller' => 'revisions', 'action' => 'index'], [], true)) .
                                '</p>' .
                                '<p class="signature">' . $log->getCreatedBy() . '</p>';

                        }
                        $globalMessenger->addWarningMessage($title . $message);
                    }
                }
            },
            $this->priority
        );
    }

    protected function translateInterval(\DateInterval $interval)
    {
        if ($interval->days > 1) {
            return sprintf($this->translate('%s days ago'), $interval->format('%a'));
        }
        if ($interval->days > 0) {
            return $this->translate('yersterday');
        }
        if ($interval->h > 1) {
            return sprintf($this->translate('%s hours ago'), $interval->format('%h'));
        }
        if ($interval->h > 0) {
            return $this->translate('one hour ago');
        }
        if ($interval->i > 1) {
            return sprintf($this->translate('%s minutes ago'), $interval->format('%i'));
        }
        if ($interval->i > 0) {
            return $this->translate('one minute ago');
        }
        if ($interval->s > 1) {
            return sprintf($this->translate('%s seconds ago'), $interval->format('%s'));
        }
        return $this->translate('one second ago');
    }

    /**
     * @param $message
     * @return string
     */
    protected function translate($message)
    {
        return call_user_func($this->translate, $message);
    }

    /**
     * @param string $name
     * @param array  $params
     * @param array  $options
     * @param bool   $reuseMatchedParams
     * @return string
     */
    protected function url($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        return call_user_func($this->url, $name, $params, $options, $reuseMatchedParams);
    }
}
