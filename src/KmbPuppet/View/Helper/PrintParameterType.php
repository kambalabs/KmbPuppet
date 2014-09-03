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

use KmbPmProxy\Model\ParameterType;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;

class PrintParameterType extends AbstractTranslatorHelper
{
    public function __invoke($type)
    {
        $translator = $this->getTranslator();

        switch ($type) {
            case ParameterType::BOOLEAN:
                return $translator->translate('boolean');
            case ParameterType::FREE_ENTRY:
                return $translator->translate('free entry');
            case ParameterType::STRING:
                return $translator->translate('string');
            case ParameterType::TEXT:
                return $translator->translate('text');
            case ParameterType::PREDEFINED_LIST:
                return $translator->translate('predefined list');
            case ParameterType::EDITABLE_LIST:
                return $translator->translate('editable list');
            case ParameterType::HASHTABLE:
                return $translator->translate('hashtable');
            case ParameterType::EDITABLE_HASHTABLE:
                return $translator->translate('editable hashtable');
        }

        return $type;
    }
}
