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

use Fusio\Cli\Deploy\SchemeBuilder;
use Fusio\Cli\Deploy\TransformerAbstract;
use Fusio\Cli\Exception\TransformException;
use Fusio\Cli\Service\Import\Types;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * Agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Agent extends TransformerAbstract
{
    public function transform(array $data, stdClass $import, ?string $basePath): void
    {
        $action = $data[Types::TYPE_AGENT] ?? [];

        if (!empty($action) && is_array($action)) {
            $result = [];
            foreach ($action as $name => $entry) {
                $result[] = $this->transformAgent($name, $entry, $basePath);
            }
            $import->agent = $result;
        }
    }

    /**
     * @throws TransformException
     */
    protected function transformAgent(string $name, mixed $data, ?string $basePath): array
    {
        $data = $this->includeDirective->resolve($data, $basePath, Types::TYPE_AGENT);
        $data['name'] = $name;

        if (isset($data['connection']) && is_string($data['connection'])) {
            try {
                $agent = $this->client->get(Types::TYPE_AGENT, $data['connection']);
                $id = $agent->id ?? null;
                if (!is_int($id)) {
                    throw new RuntimeException('Could not determine agent id');
                }

                $data['connection'] = $id;
            } catch (Throwable $e) {
                throw new TransformException($name, 'Could not resolve provided agent connection "' . $data['connection'] . '", please check whether it actually exists', previous: $e);
            }
        }

        if (isset($data['introduction'])) {
            $data['introduction'] = $this->includeDirective->resolveTextFile($data['introduction'], $basePath, Types::TYPE_AGENT);
        }

        if (isset($data['outgoing'])) {
            $data['outgoing'] = SchemeBuilder::forSchema($data['outgoing']);
        } else {
            $data['outgoing'] = null;
        }

        return $data;
    }
}
