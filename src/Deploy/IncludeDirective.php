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

namespace Fusio\Cli\Deploy;

use PSX\Json\Pointer;
use PSX\Uri\Uri;
use RuntimeException;
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
class IncludeDirective
{
    private EnvReplacerInterface $envReplacer;
    private Parser $parser;

    public function __construct(EnvReplacerInterface $envReplacer)
    {
        $this->envReplacer = $envReplacer;
        $this->parser      = new Parser();
    }

    public function resolve(mixed $data, ?string $basePath, string $type): array
    {
        if ($data instanceof TaggedValue) {
            if ($data->getTag() === 'include') {
                $file = Uri::parse($data->getValue());
                $path = $basePath . '/' . $file->getPath();

                if (is_file($path)) {
                    $fragment = $file->getFragment();
                    $data     = $this->parser->parse($this->envReplacer->replace(file_get_contents($path)), Yaml::PARSE_CUSTOM_TAGS);

                    if (!empty($fragment)) {
                        $pointer = new Pointer($fragment);
                        return $pointer->evaluate($data);
                    } else {
                        return $data;
                    }
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $path);
                }
            } else {
                throw new RuntimeException('Invalid tag provide: ' . $data->getTag());
            }
        } elseif (is_array($data)) {
            return $data;
        } else {
            throw new RuntimeException(ucfirst($type) . ' must be either an array or a string containing a "!include" directive');
        }
    }
}
