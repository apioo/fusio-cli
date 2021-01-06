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
     * @return Import\Result
     */
    public function import(string $data)
    {
        $data   = Parser::decode($data, false);
        $result = new Result();

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

                    $this->importType($type, $id, $modelClass, $entry, $result);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @param string $id
     * @param string $modelClass
     * @param \stdClass $data
     * @param Import\Result $result
     */
    private function importType(string $type, string $id, string $modelClass, stdClass $data, Result $result)
    {
        $name = $data->{$id};

        $existing = null;
        try {
            $actualId = (int) $name;
            if ($actualId === 0) {
                $actualId = $this->resolveId($type, $name);
            }

            $existing = $this->client->get($type, $actualId);
        } catch (TransportException $e) {
            // 404 not found that means we can create the resource
        }

        if (isset($existing['id'])) {
            $response = $this->client->update($type, $existing['id'], \json_encode($data), $modelClass . '_Update');
        } else {
            $response = $this->client->create($type, \json_encode($data), $modelClass . '_Create');
        }

        if (isset($response['success']) && $response['success'] === false) {
            $result->add($type, Result::ACTION_FAILED, $name . ': ' . $response['message']);
        } elseif (isset($existing['id'])) {
            $result->add($type, Result::ACTION_UPDATED, $name);
        } else {
            $result->add($type, Result::ACTION_CREATED, $name);
        }
    }

    /**
     * In case we have received a name we need to resolve the actual id
     * 
     * @param string $type
     * @param string $name
     */
    private function resolveId(string $type, string $name): int
    {
        $data = $this->client->getAll($type, 0, 1, $name);

        return $data['entry'][0]['id'] ?? 0;
    }
}
