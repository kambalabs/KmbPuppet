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

use KmbPmProxy\Model\PuppetModule;
use KmbPmProxy\Validator\PuppetClassValidator;
use KmbPuppetDb\Model;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;

class PuppetModuleLabelClass extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;

    public function __invoke(PuppetModule $module)
    {
        return $this->hasError($module) ? 'label-danger' : 'label-success';
    }
    /**
     * @param PuppetModule $module
     * @return bool
     */
    protected function hasError($module)
    {
        if ($module->hasClasses()) {
            foreach ($module->getClasses() as $class) {
                /** @var PuppetClassValidator $validator */
                $validator = $this->getServiceLocator()->get('KmbPmProxy\Validator\PuppetClassValidator');
                if (!$validator->isValid($class)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator->getServiceLocator();
    }
}
