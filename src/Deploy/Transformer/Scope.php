<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Cli\Deploy\Transformer;

use Fusio\Cli\Deploy\TransformerAbstract;
use Fusio\Cli\Service\Types;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Scope extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $scope = isset($data[Types::TYPE_SCOPE]) ? $data[Types::TYPE_SCOPE] : [];

        if (!empty($scope) && is_array($scope)) {
            $result = [];
            foreach ($scope as $name => $entry) {
                $result[] = $this->transformUser($name, $entry, $basePath);
            }
            $import->scope = $result;
        }
    }

    protected function transformUser($name, $data, $basePath)
    {
        $data = $this->includeDirective->resolve($data, $basePath, Types::TYPE_SCOPE);
        $data['name'] = $name;

        return $data;
    }
}
