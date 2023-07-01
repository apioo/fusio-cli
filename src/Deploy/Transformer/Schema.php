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

use Fusio\Cli\Deploy\TransformerAbstract;
use Fusio\Cli\Service\Import\Types;
use RuntimeException;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Schema extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, ?string $basePath): void
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
            'source' => $this->resolveSchema($schema, $basePath),
        ];
    }

    private function resolveSchema(mixed $data, ?string $basePath)
    {
        if ($data instanceof TaggedValue) {
            if ($data->getTag() === 'include') {
                $file = $basePath . '/' . $data->getValue();

                if (is_file($file)) {
                    return \json_decode(file_get_contents($file));
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $file);
                }
            } else {
                throw new RuntimeException('Invalid tag provide: ' . $data->getTag());
            }
        } elseif (is_string($data)) {
            return \json_decode($data);
        } elseif (is_array($data) || $data instanceof \stdClass) {
            return $data;
        } else {
            throw new RuntimeException('Schema must be a string or array');
        }
    }
}
