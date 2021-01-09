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
use PSX\Json\Parser;
use stdClass;

/**
 * Export
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Export
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
     * @return string
     * @throws TokenException
     * @throws TransportException
     */
    public function export()
    {
        $data = new stdClass();

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
     * @param string $type
     * @param int $index
     * @param array $result
     * @throws TokenException
     * @throws TransportException
     */
    private function exportType(string $type, int $index, array &$result)
    {
        $data    = $this->client->getAll($type, $index, Types::COLLECTION_SIZE, null, 'id', 0);
        $count   = $data['totalResults'] ?? 0;
        $entries = $data['entry'] ?? [];

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

    private function transform(string $type, array $entity): array
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
        } elseif ($type === Types::TYPE_ROUTE) {
            return $this->transformRoute($entity);
        } elseif ($type === Types::TYPE_SCHEMA) {
            return $this->transformSchema($entity);
        } elseif ($type === Types::TYPE_SCOPE) {
            return $this->transformScope($entity);
        } else {
            return $entity;
        }
    }

    private function transformAction(array $entity)
    {
        unset($entity['id']);
        unset($entity['status']);

        return $entity;
    }

    private function transformConfig(array $entity)
    {
        unset($entity['id']);

        return $entity;
    }

    private function transformConnection(array $entity)
    {
        unset($entity['id']);

        return $entity;
    }

    private function transformCronjob(array $entity)
    {
        unset($entity['id']);
        unset($entity['status']);
        unset($entity['executeDate']);
        unset($entity['exitCode']);
        unset($entity['errors']);

        return $entity;
    }

    private function transformEvent(array $entity)
    {
        unset($entity['id']);

        return $entity;
    }

    private function transformPlan(array $entity)
    {
        unset($entity['id']);

        return $entity;
    }

    private function transformRate(array $entity)
    {
        unset($entity['id']);
        unset($entity['status']);

        return $entity;
    }

    private function transformRole(array $entity)
    {
        unset($entity['id']);

        return $entity;
    }

    private function transformRoute(array $entity)
    {
        unset($entity['id']);

        return $entity;
    }

    private function transformSchema(array $entity)
    {
        unset($entity['id']);
        unset($entity['status']);

        return $entity;
    }

    private function transformScope(array $entity)
    {
        unset($entity['id']);
        unset($entity['routes']);

        return $entity;
    }
}
