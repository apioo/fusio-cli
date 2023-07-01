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

use Fusio\Cli\Exception\TokenException;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Import\Types;
use PSX\Json\Parser;
use stdClass;

/**
 * Export
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Export
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws TransportException
     * @throws TokenException
     * @throws \JsonException
     */
    public function export(): string
    {
        $data = new stdClass();
        $data->version = $this->client->getVersion();

        foreach (Types::getTypes() as $type => $config) {
            $result = [];

            $this->exportType($type, 0, $result);

            if (count($result) > 0) {
                $data->$type = $result;
            }
        }

        return Parser::encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    private function exportType(string $type, int $index, array &$result): void
    {
        $data    = $this->client->getAll($type, $index, Types::COLLECTION_SIZE, null, 'id', 0);
        $count   = $data->totalResults ?? 0;
        $entries = $data->entry ?? [];

        if (!is_array($entries)) {
            return;
        }

        foreach ($entries as $entry) {
            $entity = $this->client->get($type, $entry['id']);

            $result[] = $this->transform($type, $entity);
        }

        if ($count > count($result)) {
            $this->exportType($type, $index + Types::COLLECTION_SIZE, $result);
        }
    }

    private function transform(string $type, object $entity): object
    {
        if ($type === Types::TYPE_ACTION) {
            return $this->transformAction($entity);
        } elseif ($type === Types::TYPE_CONFIG) {
            return $this->transformConfig($entity);
        } elseif ($type === Types::TYPE_CONNECTION) {
            return $this->transformConnection($entity);
        } elseif ($type === Types::TYPE_CRONJOB) {
            return $this->transformCronjob($entity);
        } elseif ($type === Types::TYPE_EVENT) {
            return $this->transformEvent($entity);
        } elseif ($type === Types::TYPE_PLAN) {
            return $this->transformPlan($entity);
        } elseif ($type === Types::TYPE_RATE) {
            return $this->transformRate($entity);
        } elseif ($type === Types::TYPE_ROLE) {
            return $this->transformRole($entity);
        } elseif ($type === Types::TYPE_OPERATION) {
            return $this->transformOperation($entity);
        } elseif ($type === Types::TYPE_SCHEMA) {
            return $this->transformSchema($entity);
        } elseif ($type === Types::TYPE_SCOPE) {
            return $this->transformScope($entity);
        } else {
            return $entity;
        }
    }

    private function transformAction(object $entity): object
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    private function transformConfig(object $entity): object
    {
        unset($entity->id);

        return $entity;
    }

    private function transformConnection(object $entity): object
    {
        unset($entity->id);

        return $entity;
    }

    private function transformCronjob(object $entity): object
    {
        unset($entity->id);
        unset($entity->status);
        unset($entity->executeDate);
        unset($entity->exitCode);
        unset($entity->errors);

        return $entity;
    }

    private function transformEvent(object $entity): object
    {
        unset($entity->id);

        return $entity;
    }

    private function transformPlan(object $entity): object
    {
        unset($entity->id);

        return $entity;
    }

    private function transformRate(object $entity): object
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    private function transformRole(object $entity): object
    {
        unset($entity->id);

        return $entity;
    }

    private function transformOperation(object $entity): object
    {
        unset($entity->id);

        return $entity;
    }

    private function transformSchema(object $entity): object
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    private function transformScope(object $entity): object
    {
        unset($entity->id);
        unset($entity->operations);

        return $entity;
    }
}
