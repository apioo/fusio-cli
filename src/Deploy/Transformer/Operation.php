<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Cli\Deploy\Transformer;

use Fusio\Cli\Deploy\SchemeBuilder;
use Fusio\Cli\Deploy\TransformerAbstract;
use Fusio\Cli\Service\Import\Types;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Operation extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, ?string $basePath): void
    {
        $operation = $data[Types::TYPE_OPERATION] ?? [];

        if (!empty($operation) && is_array($operation)) {
            $result = [];
            foreach ($operation as $name => $entry) {
                $result[] = $this->transformOperation($name, $entry, $basePath);
            }
            $import->operation = $result;
        }
    }

    protected function transformOperation(string $name, mixed $data, ?string $basePath): array
    {
        $data = $this->includeDirective->resolve($data, $basePath, Types::TYPE_OPERATION);
        $data['name'] = $name;

        if (isset($data['incoming'])) {
            $data['incoming'] = SchemeBuilder::forSchema($data['incoming']);
        } elseif (!in_array($data['httpMethod'], ['GET', 'DELETE'])) {
            $data['incoming'] = 'schema://Passthru';
        }

        if (isset($data['outgoing'])) {
            $data['outgoing'] = SchemeBuilder::forSchema($data['outgoing']);
        } else {
            $data['outgoing'] = 'schema://Passthru';
        }

        if (isset($data['throws']) && is_array($data['throws'])) {
            $throws = [];
            foreach ($data['throws'] as $code => $schema) {
                $throws[$code] = SchemeBuilder::forSchema($schema);
            }
            $data['throws'] = $throws;
        }

        if (isset($data['action'])) {
            $data['action'] = SchemeBuilder::forAction($data['action']);
        }

        return $data;
    }
}
