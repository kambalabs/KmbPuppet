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

use KmbPuppetDb\Model;
use Zend\View\Helper\AbstractHelper;

class FilterReportMessage extends AbstractHelper
{
    public function __invoke($message)
    {
        if ($message == null) {
            return 'null';
        }
        if (preg_match('/content changed/', $message)) {
            return 'content changed';
        }
        if (preg_match('/defined content/', $message)) {
            return 'defined content';
        }
        return $message;
    }
}
