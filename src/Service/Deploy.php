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

namespace Fusio\Cli\Service;

use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Deploy\IncludeDirective;
use Fusio\Cli\Deploy\Transformer;
use Fusio\Cli\Deploy\TransformerInterface;
use Fusio\Cli\Exception\TransformException;
use Fusio\Cli\Service\Import\Result;
use Fusio\Cli\Service\Import\Types;
use Generator;
use InvalidArgumentException;
use JsonException;
use PSX\Json\Parser;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * The deploy service basically transforms a deploy yaml config into a json format which is then used by the import
 * service
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
readonly class Deploy
{
    public function __construct(
        private Import $import,
        private Client $client,
        private IncludeDirective $includeDirective,
        private EnvReplacerInterface $envReplacer,
    ) {
    }

    /**
     * @return Generator<Result>
     * @throws JsonException
     */
    public function deploy(string $yaml, ?string $basePath = null): Generator
    {
        $data = Yaml::parse($this->envReplacer->replace($yaml), Yaml::PARSE_CUSTOM_TAGS);
        if (empty($data) || !is_array($data)) {
            return;
        }

        if (empty($basePath)) {
            $basePath = (string) getcwd();
        }

        $transformers = [
            Types::TYPE_ACTION     => $this->newTransformer(Transformer\Action::class),
            Types::TYPE_CONFIG     => $this->newTransformer(Transformer\Config::class),
            Types::TYPE_CONNECTION => $this->newTransformer(Transformer\Connection::class),
            Types::TYPE_CRONJOB    => $this->newTransformer(Transformer\Cronjob::class),
            Types::TYPE_EVENT      => $this->newTransformer(Transformer\Event::class),
            Types::TYPE_PLAN       => $this->newTransformer(Transformer\Plan::class),
            Types::TYPE_RATE       => $this->newTransformer(Transformer\Rate::class),
            Types::TYPE_SCOPE      => $this->newTransformer(Transformer\Scope::class),
            Types::TYPE_ROLE       => $this->newTransformer(Transformer\Role::class),
            Types::TYPE_SCHEMA     => $this->newTransformer(Transformer\Schema::class),
            Types::TYPE_OPERATION  => $this->newTransformer(Transformer\Operation::class),
            Types::TYPE_AGENT      => $this->newTransformer(Transformer\Agent::class),
        ];

        // resolve includes
        foreach ($transformers as $type => $transformer) {
            if (isset($data[$type])) {
                $data[$type] = $this->includeDirective->resolve($data[$type], $basePath, $type);
            }
        }

        foreach (Types::getTypes() as $type => $config) {
            [$id, $modelClass] = $config;

            $entries = $data[$type] ?? [];
            if (empty($entries) || !is_array($entries)) {
                continue;
            }

            $transformer = $transformers[$type] ?? null;
            if ($transformer instanceof TransformerInterface) {
                try {
                    foreach ($transformer->transform($entries, $basePath) as $entry) {
                        yield from $this->import->importType($type, $id, $modelClass, $entry);
                    }
                } catch (TransformException $e) {
                    yield new Result($type, Result::ACTION_FAILED, $e->name . ': ' . $e->getMessage());
                }
            }
        }
    }

    private function newTransformer(string $class): TransformerInterface
    {
        $transformer = new $class($this->includeDirective, $this->client);

        if (!$transformer instanceof TransformerInterface) {
            throw new InvalidArgumentException('Transformer must be an instance of ' . TransformerInterface::class);
        }

        return $transformer;
    }
}
