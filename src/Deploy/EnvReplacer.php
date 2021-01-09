<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Cli\Deploy;

/**
 * EnvReplacer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EnvReplacer implements EnvReplacerInterface
{
    /**
     * @var \Closure[] 
     */
    private $properties;

    public function __construct(?array $env = null)
    {
        $this->addProperties('env', function() use ($env){
            return $env === null ? $_SERVER : $env;
        });
    }

    public function addProperties(string $category, \Closure $resolver)
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
