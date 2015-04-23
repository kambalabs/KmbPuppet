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
namespace KmbPuppet\View\Helper;

use KmbBase\Widget\DefaultWidgetAction;
use KmbBase\Widget\WidgetActionInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\DispatchableInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class CustomGroups extends AbstractHelper
{
    /** @var  array */
    protected $config;

    /** @var  WidgetActionInterface */
    protected $action;

    /** @var  string */
    protected $label;

    /** @var  ServiceLocatorInterface */
    protected $serviceLocator;

    /** @var  DispatchableInterface */
    protected $controller;

    /**
     * @return CustomGroups
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param string $type
     * @param array $model
     * @return string
     */
    public function render($type, $model = [])
    {
        $config = $this->getCustomGroupsConfig($type);
        $this->label = isset($config['label']) ? $config['label'] : '';
        if (isset($config['widget'])) {
            $this->action = isset($config['widget']['action']) ? $this->serviceLocator->get($config['widget']['action']) : new DefaultWidgetAction();
            $this->action->setTemplate($config['widget']['template']);
            $this->action->setServiceLocator($this->serviceLocator);
            $this->action->setController($this->controller);
        }
        if (is_null($this->action)) {
            return '';
        }
        $viewModel = $this->createModel($this->action->getTemplate(), $model);
        return $this->view->partial($this->action->call($viewModel));
    }

    /**
     * @param string $type
     * @return array
     */
    public function getCustomGroupsConfig($type)
    {
        return isset($this->config[$type]) ? $this->config[$type] : [];
    }

    /**
     * @return boolean
     */
    public function hasOnlyDefault()
    {
        return count($this->config) <= 1;
    }

    /**
     * @param string $template
     * @param array $model
     * @return ViewModel
     */
    protected function createModel($template, $model)
    {
        $viewModel = new ViewModel($model);
        foreach ($this->view->viewModel()->getCurrent()->getVariables() as $key => $value) {
            $viewModel->setVariable($key, $viewModel->getVariable($key, $value));
        }
        return $viewModel->setTemplate($template);
    }

    /**
     * Set Config.
     *
     * @param array $config
     * @return CustomGroups
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get Config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set ServiceLocator.
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return CustomGroups
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get ServiceLocator.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set Controller.
     *
     * @param \Zend\Stdlib\DispatchableInterface $controller
     * @return CustomGroups
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Get Controller.
     *
     * @return \Zend\Stdlib\DispatchableInterface
     */
    public function getController()
    {
        return $this->controller;
    }
}
