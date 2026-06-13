<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Cli\Deploy\TransformerAbstract;
use Fusio\Cli\Service\Import\Types;
use stdClass;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Schema extends TransformerAbstract
{
    public function transform(array $data, stdClass $import, ?string $basePath): void
    {
        $schema = $data[Types::TYPE_SCHEMA] ?? [];

        if (!empty($schema) && is_array($schema)) {
            $result = [];
            foreach ($schema as $name => $entry) {
                $result[] = $this->transformSchema($name, $entry, $basePath);
            }
            $import->schema = $result;
        }
    }

    protected function transformSchema(string $name, mixed $schema, ?string $basePath): array
    {
        return [
            'name'   => $name,
            'source' => $this->includeDirective->resolveJsonFile($schema, $basePath, Types::TYPE_SCHEMA),
        ];
    }
}
