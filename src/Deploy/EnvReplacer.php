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

/**
 * EnvReplacer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class EnvReplacer implements EnvReplacerInterface
{
    /**
     * @var \Closure[] 
     */
    private array $properties;

    public function __construct(?array $env = null)
    {
        $this->addProperties('env', function() use ($env){
            return $env === null ? $_SERVER : $env;
        });
    }

    public function addProperties(string $category, \Closure $resolver): void
    {
        $this->properties[$category] = $resolver;
    }

    /**
     * @inheritDoc
     */
    public function replace(string $data): string
    {
        $vars = [];
        foreach ($this->properties as $category => $resolver) {
            $properties = $resolver();

            $vars[$category] = [];
            foreach ($properties as $key => $value) {
                if (is_scalar($value)) {
                    $vars[$category][strtolower($key)] = $value;
                }
            }
        }

        // replace
        $data = preg_replace_callback('/\$\{([0-9A-Za-z_]+).([0-9A-Za-z_]+)\}/', function(array $matches) use ($vars): string {
            $type = strtolower($matches[1]);
            $key  = strtolower($matches[2]);

            if (isset($vars[$type])) {
                if (isset($vars[$type][$key])) {
                    $value = $vars[$type][$key];

                    if (is_string($value)) {
                        $value = trim(json_encode($value), '"');
                    }

                    return (string) $value;
                } else {
                    throw new \RuntimeException('Usage of unknown variable key "' . $key . '", allowed is (' . implode(', ', array_keys($vars[$type])) . ')');
                }
            } else {
                throw new \RuntimeException('Usage of unknown variable type "' . $type . '", allowed is (' . implode(', ', array_keys($vars)) . ')');
            }
        }, $data);

        return $data;
    }
}
