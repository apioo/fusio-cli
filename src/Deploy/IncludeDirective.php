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

namespace Fusio\Cli\Deploy;

use Closure;
use Fusio\Cli\Builder;
use Fusio\Cli\Service\Import\Types;
use JsonException;
use PSX\Json\Pointer;
use PSX\Json\Parser as JsonParser;
use PSX\Uri\Uri;
use RuntimeException;
use stdClass;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

/**
 * IncludeDirective
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
readonly class IncludeDirective
{
    private Parser $parser;

    public function __construct(private EnvReplacerInterface $envReplacer)
    {
        $this->parser = new Parser();
    }

    public function resolve(mixed $data, ?string $basePath, string $type): array
    {
        if ($data instanceof TaggedValue) {
            if ($data->getTag() !== 'include') {
                throw new RuntimeException('Invalid tag provide: ' . $data->getTag());
            }

            $file = Uri::parse($data->getValue());
            $path = $basePath . '/' . $file->getPath();

            if (!is_file($path)) {
                throw new RuntimeException('Could not resolve file: ' . $path);
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension === 'php') {
                $data = $this->resolvePHPFile($path, $type);
            } else {
                $data = $this->parser->parse($this->envReplacer->replace((string) file_get_contents($path)), Yaml::PARSE_CUSTOM_TAGS);

                $fragment = $file->getFragment();
                if (!empty($fragment)) {
                    $data = new Pointer($fragment)->evaluate($data);
                }
            }

            return $data;
        } elseif (is_array($data)) {
            return $data;
        } else {
            throw new RuntimeException(ucfirst($type) . ' must be either an array or a string containing a "!include" directive');
        }
    }

    private function resolvePHPFile(string $path, string $type): array
    {
        $resolver = include $path;
        if (!$resolver instanceof Closure) {
            throw new RuntimeException('File ' . $path . ' must return a closure');
        }

        $builder = $this->newBuilderForType($type);
        $env = new Builder\Context($this->envReplacer->getVars());

        call_user_func_array($resolver, [$builder, $env]);

        return $builder->toArray();
    }

    public function resolveTextFile(mixed $data, ?string $basePath, string $type): ?string
    {
        if ($data instanceof TaggedValue) {
            if ($data->getTag() !== 'include') {
                throw new RuntimeException('Invalid tag provide: ' . $data->getTag());
            }

            $file = Uri::parse($data->getValue());
            $path = $basePath . '/' . $file->getPath();

            if (!is_file($path)) {
                throw new RuntimeException('Could not resolve file: ' . $path);
            }

            return (string) file_get_contents($path);
        } elseif (is_string($data)) {
            return $data;
        } else {
            throw new RuntimeException(ucfirst($type) . ' must be either a string or a string containing a "!include" directive pointing to a text file');
        }
    }

    /**
     * @throws JsonException
     */
    public function resolveJsonFile(mixed $data, ?string $basePath, string $type): mixed
    {
        if ($data instanceof TaggedValue) {
            if ($data->getTag() === 'include') {
                $file = $basePath . '/' . $data->getValue();

                if (is_file($file)) {
                    return JsonParser::decode((string) file_get_contents($file));
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $file);
                }
            } else {
                throw new RuntimeException('Invalid tag provide: ' . $data->getTag());
            }
        } elseif (is_string($data)) {
            return JsonParser::decode($data);
        } elseif (is_array($data) || $data instanceof stdClass) {
            return $data;
        } else {
            throw new RuntimeException(ucfirst($type) . ' must be a string or array');
        }
    }

    private function newBuilderForType(string $type): Builder\BuilderInterface
    {
        return match($type) {
            Types::TYPE_OPERATION => new Builder\Operation(),
            default => throw new RuntimeException('Builder are not supported for type ' . $type),
        };
    }
}
