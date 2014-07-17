<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/multimediabs/kamba for the canonical source repository
 *
 * This file is part of kamba.
 *
 * kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppet\View\Helper;

use KmbPuppetDb\Model;
use Zend\View\Helper\AbstractHelper;

class ReportLabelClass extends AbstractHelper
{
    public function __invoke(Model\Report $report)
    {
        switch ($report->getStatus()) {
            case Model\ReportInterface::SUCCESS:
                return 'label-success';
            case Model\ReportInterface::SKIPPED:
                return 'label-warning';
            case Model\ReportInterface::FAILURE:
                return 'label-danger';
            default:
                return 'label-default';
        }
    }
}
