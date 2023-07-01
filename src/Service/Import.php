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

namespace Fusio\Cli\Service;

use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Import\Result;
use Fusio\Cli\Service\Import\Types;
use PSX\Json\Parser;
use RuntimeException;
use stdClass;

/**
 * Import
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Import
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws \JsonException
     * @return \Generator<Result>
     */
    public function import(string $data): \Generator
    {
        $data = Parser::decode($data);
        if (!$data instanceof stdClass) {
            throw new RuntimeException('Data must be an object');
        }

        $version = $data->version ?? null;
        if (!empty($version)) {
            // @TODO in the future we could transform the data depending on the provided version
        }

        foreach (Types::getTypes() as $type => $config) {
            [$id, $modelClass] = $config;

            $entries = $data->{$type} ?? null;
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if (!$entry instanceof stdClass) {
                        continue;
                    }

                    yield from $this->importType($type, $id, $modelClass, $entry);
                }
            }
        }
    }

    private function importType(string $type, string $id, string $modelClass, stdClass $data): \Generator
    {
        $name = $data->{$id};

        $existing = null;
        try {
            $existing = $this->client->get($type, $name);
        } catch (TransportException $e) {
            // 404 not found that means we can create the resource
        }

        try {
            if (isset($existing->id)) {
                $response = $this->client->update($type, $existing->id, \json_encode($data), $modelClass . 'Update');
            } else {
                $response = $this->client->create($type, \json_encode($data), $modelClass . 'Create');
            }

            if (isset($response->success) && $response->success === false) {
                yield new Result($type, Result::ACTION_FAILED, $name . ': ' . $response->message);
            } elseif (isset($existing->id)) {
                yield new Result($type, Result::ACTION_UPDATED, $name);
            } else {
                yield new Result($type, Result::ACTION_CREATED, $name);
            }
        } catch (TransportException $e) {
            yield new Result($type, Result::ACTION_FAILED, $name . ': ' . $e->getMessage(), $e->getResponse());
        } catch (\Throwable $e) {
            yield new Result($type, Result::ACTION_FAILED, $name . ': ' . $e->getMessage());
        }
    }
}
