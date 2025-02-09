<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Action extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, ?string $basePath): void
    {
        $action = $data[Types::TYPE_ACTION] ?? [];

        if (!empty($action) && is_array($action)) {
            $result = [];
            foreach ($action as $name => $entry) {
                $result[] = $this->transformAction($name, $entry, $basePath);
            }
            $import->action = $result;
        }
    }

    protected function transformAction(string $name, mixed $data, ?string $basePath): array
    {
        $data = $this->includeDirective->resolve($data, $basePath, Types::TYPE_ACTION);
        $data['name'] = $name;

        // resolve include tags inside the action config
        if (isset($data['config']) && is_array($data['config'])) {
            $config = [];
            foreach ($data['config'] as $key => $value) {
                if ($value instanceof TaggedValue) {
                    $file = $basePath . '/' . $value->getValue();
                    if (is_file($file)) {
                        $config[$key] = file_get_contents($file);
                    } else {
                        throw new \RuntimeException('Provided file ' . $file . ' does not exist');
                    }
                } else {
                    $config[$key] = $value;
                }
            }
            $data['config'] = $config;
        }

        return $data;
    }
}
