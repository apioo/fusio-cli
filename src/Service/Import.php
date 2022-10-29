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

namespace Fusio\Cli\Service;

use Fusio\Cli\Exception\TokenException;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Import\Result;
use PSX\Json\Parser;
use RuntimeException;
use stdClass;

/**
 * Import
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Import
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $data
     * @return \Generator
     * @throws TokenException
     */
    public function import(string $data): \Generator
    {
        $data = Parser::decode($data, false);
        if (!$data instanceof stdClass) {
            throw new RuntimeException('Data must be an object');
        }

        foreach (Types::getTypes() as $type => $config) {
            [$id, $modelClass] = $config;

            $entries = isset($data->{$type}) ? $data->{$type} : null;
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

    /**
     * @param string $type
     * @param string $id
     * @param string $modelClass
     * @param stdClass $data
     * @return \Generator
     * @throws TokenException
     */
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
            if (isset($existing['id'])) {
                $response = $this->client->update($type, $existing['id'], \json_encode($data), $modelClass . 'Update');
            } else {
                $response = $this->client->create($type, \json_encode($data), $modelClass . 'Create');
            }

            if (isset($response['success']) && $response['success'] === false) {
                yield new Result($type, Result::ACTION_FAILED, $name . ': ' . $response['message']);
            } elseif (isset($existing['id'])) {
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
