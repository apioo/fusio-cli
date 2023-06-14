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

namespace Fusio\Cli\Deploy;

use RuntimeException;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * NameGenerator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class NameGenerator
{
    public static function getActionNameFromSource($source)
    {
        if (is_string($source)) {
            // remove scheme if uri format
            if (($pos = strpos($source, '://')) !== false) {
                $source = substr($source, $pos + 3);
            }

            if (is_file($source)) {
                $pos = (int) strpos($source, DIRECTORY_SEPARATOR . 'src');
                $source = realpath($source);
                $source = substr($source, $pos + 4);
            }

            return preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $source);
        } else {
            throw new RuntimeException('Invalid action source');
        }
    }

    public static function getSchemaNameFromSource(mixed $source): string
    {
        if ($source instanceof TaggedValue) {
            if ($source->getTag() === 'include') {
                $source = trim($source->getValue());
                $source = str_replace('\\', '/', $source);
                $source = str_replace('resources/schema/', '', $source);
                $source = str_replace('.json', '', $source);
                $source = str_replace(' ', '-', ucwords(str_replace('/', ' ', $source)));

                return $source;
            } else {
                throw new RuntimeException('Invalid tag provide: ' . $source->getTag());
            }
        } elseif (is_string($source)) {
            if (preg_match('/^[a-zA-Z0-9\-\_]{3,255}$/', $source)) {
                return $source;
            } else {
                return self::getNameFromSchema($source);
            }
        } elseif (is_array($source)) {
            return self::getNameFromSchema(json_encode($source));
        } else {
            throw new RuntimeException('Schema should be a string containing an "!include" directive pointing to a JsonSchema file');
        }
    }

    private static function getNameFromSchema(string $schema): string
    {
        if (preg_match('/^[a-zA-Z0-9_\\\]+$/', $schema)) {
            if (class_exists($schema)) {
                return preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $schema);
            } else {
                throw new RuntimeException('Provided class "' . $schema . '" does not exist');
            }
        } else {
            $data = json_decode($schema);
            if (!$data instanceof \stdClass) {
                throw new RuntimeException('Schema must be a valid json schema');
            }

            if (isset($data->{'$ref'}) && is_string($data->{'$ref'})) {
                return preg_replace('/[^A-z0-9\-\_]/', '_', $data->{'$ref'});
            } elseif (isset($data->title) && is_string($data->title)) {
                return preg_replace('/[^A-z0-9\-\_]/', '_', $data->title);
            }

            // at last fallback we can only use the md5 of the schema as name
            return 'Schema-' . substr(md5($schema), 0, 8);
        }
    }
}
