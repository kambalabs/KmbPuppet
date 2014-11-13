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

use GtnDataTables\Service\DataTable;
use KmbAuthentication\Controller\AuthenticatedControllerInterface;
use KmbDomain\Model\EnvironmentRepositoryInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ReportsController extends AbstractActionController implements AuthenticatedControllerInterface
{
    /** @var EnvironmentRepositoryInterface */
    protected $environmentRepository;

    public function indexAction()
    {
        $viewModel = $this->acceptableViewModelSelector(array(
            'Zend\View\Model\ViewModel' => array(
                'text/html',
            ),
            'Zend\View\Model\JsonModel' => array(
                'application/json',
            ),
        ));

        if ($viewModel instanceof JsonModel) {
            $params = $this->params()->fromQuery();
            $environment = $this->environmentRepository->getById($this->params()->fromRoute('envId'));
            if ($environment != null) {
                $params['environment'] = $environment;
            }
            /** @var DataTable $datatable */
            $datatable = $this->getServiceLocator()->get('reports_datatable');
            $result = $datatable->getResult($params);
            $viewModel->setVariable('draw', $result->getDraw());
            $viewModel->setVariable('recordsTotal', $result->getRecordsTotal());
            $viewModel->setVariable('recordsFiltered', $result->getRecordsFiltered());
            $viewModel->setVariable('data', $result->getData());
        }

        return $viewModel;
    }

    /**
     * Set EnvironmentRepository.
     *
     * @param \KmbDomain\Model\EnvironmentRepositoryInterface $environmentRepository
     * @return ReportsController
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return \KmbDomain\Model\EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }
}
