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

use KmbBase\Controller\Plugin\AbstractWidgetAction;
use KmbBase\Controller\Plugin\WidgetActionInterface;
use KmbPuppet\Service;
use KmbPuppetDb\Model;
use Symfony\Component\Yaml\Yaml;
use Zend\View\Model\ViewModel;

class ServerShowWidgetAction extends AbstractWidgetAction
{
    /** @var  Service\NodeInterface */
    protected $nodeService;

    /**
     * @param ViewModel $model
     * @return WidgetActionInterface
     */
    public function run(ViewModel $model = null)
    {
        /** @var Model\Node $node */
        $node = $model->getVariable('node');

        $currentConfiguration = Yaml::dump($this->nodeService->getCurrentPuppetConfiguration($node), 20, 4);
        $model->setVariable('currentConfiguration', $currentConfiguration);

        $activeConfiguration = Yaml::dump($this->nodeService->getActivePuppetConfiguration($node), 20, 4);
        $model->setVariable('activeConfiguration', $activeConfiguration);

        $diff = new \Diff(explode(PHP_EOL, $currentConfiguration), explode(PHP_EOL, $activeConfiguration));
        $model->setVariable('diff', $diff->render(new \Diff_Renderer_Html_SideBySide()));

        return $model;
    }

    /**
     * Set NodeService.
     *
     * @param \KmbPuppet\Service\NodeInterface $nodeService
     * @return ServerShowWidgetAction
     */
    public function setNodeService($nodeService)
    {
        $this->nodeService = $nodeService;
        return $this;
    }

    /**
     * Get NodeService.
     *
     * @return \KmbPuppet\Service\NodeInterface
     */
    public function getNodeService()
    {
        return $this->nodeService;
    }
}
