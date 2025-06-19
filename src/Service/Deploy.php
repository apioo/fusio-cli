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
use Fusio\Cli\Service\Import\Result;
use Fusio\Cli\Service\Import\Types;
use PSX\Json\Parser;
use PSX\Schema\SchemaManagerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * The deploy service basically transforms a deploy yaml config into a json format which is then used by the import
 * service
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Deploy
{
    private Import $import;
    private SchemaManagerInterface $schemaManager;

    public function __construct(Import $import, SchemaManagerInterface $schemaManager)
    {
        $this->import = $import;
        $this->schemaManager = $schemaManager;
    }

    /**
     * @return \Generator<string, Result>
     */
    public function deploy(string $yaml, EnvReplacerInterface $envReplacer, ?string $basePath = null): \Generator
    {
        $includeDirective = new IncludeDirective($envReplacer);

        $data = Yaml::parse($envReplacer->replace($yaml), Yaml::PARSE_CUSTOM_TAGS);
        if (empty($data) || !is_array($data)) {
            return;
        }

        if (empty($basePath)) {
            $basePath = (string) getcwd();
        }

        $transformers = [
            Types::TYPE_ACTION     => $this->newTransformer(Transformer\Action::class, [$includeDirective]),
            Types::TYPE_CONFIG     => $this->newTransformer(Transformer\Config::class, [$includeDirective]),
            Types::TYPE_CONNECTION => $this->newTransformer(Transformer\Connection::class, [$includeDirective]),
            Types::TYPE_CRONJOB    => $this->newTransformer(Transformer\Cronjob::class, [$includeDirective]),
            Types::TYPE_EVENT      => $this->newTransformer(Transformer\Event::class, [$includeDirective]),
            Types::TYPE_PLAN       => $this->newTransformer(Transformer\Plan::class, [$includeDirective]),
            Types::TYPE_RATE       => $this->newTransformer(Transformer\Rate::class, [$includeDirective]),
            Types::TYPE_SCOPE      => $this->newTransformer(Transformer\Scope::class, [$includeDirective]),
            Types::TYPE_ROLE       => $this->newTransformer(Transformer\Role::class, [$includeDirective]),
            Types::TYPE_SCHEMA     => $this->newTransformer(Transformer\Schema::class, [$includeDirective, $this->schemaManager]),
            Types::TYPE_OPERATION  => $this->newTransformer(Transformer\Operation::class, [$includeDirective]),
        ];

        // resolve includes
        foreach ($transformers as $type => $transformer) {
            if (isset($data[$type])) {
                $data[$type] = $includeDirective->resolve($data[$type], $basePath, $type);
            }
        }

        // run transformer
        $import = new \stdClass();
        foreach ($transformers as $type => $transformer) {
            /** @var TransformerInterface $transformer */
            $transformer->transform($data, $import, $basePath);
        }

        // import definition
        yield from $this->import->import(Parser::encode($import));
    }

    private function newTransformer(string $class, array $arguments = []): TransformerInterface
    {
        $transformer = new $class(...$arguments);
        if (!$transformer instanceof TransformerInterface) {
            throw new \InvalidArgumentException('Transformer must be an instance of ' . TransformerInterface::class);
        }

        return $transformer;
    }
}
