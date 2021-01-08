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

use Fusio\Cli\Exception\InputException;
use Fusio\Cli\Transport\ResponseParser;
use Fusio\Cli\Transport\TransportInterface;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\ValidationException;
use PSX\Schema\Visitor\TypeVisitor;
use Symfony\Component\Yaml\Yaml;

/**
 * Client
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Client
{
    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var SchemaManagerInterface
     */
    private $schemaManager;

    /**
     * @var SchemaTraverser
     */
    private $schemaTraverser;

    public function __construct(Authenticator $authenticator, TransportInterface $transport)
    {
        $this->authenticator = $authenticator;
        $this->transport = $transport;
        $this->schemaManager = new SchemaManager();
        $this->schemaTraverser = new SchemaTraverser();
    }

    public function getAll(string $type, ?string $startIndex = null, ?string $count = null, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null): array
    {
        $query = array_filter([
            'startIndex' => $startIndex,
            'count'      => $count,
            'search'     => $search,
            'sortBy'     => $sortBy,
            'sortOrder'  => $sortOrder,
        ]);

        $response = $this->request('GET', $type, $query);

        return ResponseParser::parse($response);
    }

    public function get(string $type, string $id): array
    {
        $response = $this->request('GET', $type . '/' . $id);

        return ResponseParser::parse($response);
    }

    public function getByName(string $type, string $name): array
    {
        $response = $this->request('GET', $type . '/~' . urlencode($name));

        return ResponseParser::parse($response);
    }

    public function create(string $type, string $payload, string $modelClass): array
    {
        $body     = $this->parsePayload($payload, $modelClass);
        $response = $this->request('POST', $type, null, $body);

        return ResponseParser::parse($response);
    }

    public function update(string $type, string $id, string $payload, string $modelClass): array
    {
        $id = $this->checkIdExists($type, $id);

        $body     = $this->parsePayload($payload, $modelClass);
        $response = $this->request('PUT', $type . '/' . $id, null, $body);

        return ResponseParser::parse($response);
    }

    public function delete(string $type, string $id): array
    {
        $id = $this->checkIdExists($type, $id);

        $response = $this->request('DELETE', $type . '/' . $id);

        return ResponseParser::parse($response);
    }

    private function parsePayload(string $payload, string $modelClass): \JsonSerializable
    {
        if (is_file($payload)) {
            $payload = file_get_contents($payload);
        }

        $data = \json_decode($payload);
        if (empty($data)) {
            // try parse as yaml
            $data = \json_decode(\json_encode(Yaml::parse($payload)));
        }

        if (!$data instanceof \stdClass) {
            throw new InputException('Could not parse provided payload, must be either a YAML or JSON object');
        }

        try {
            return $this->schemaTraverser->traverse(
                $data,
                $this->schemaManager->getSchema($modelClass),
                new TypeVisitor()
            );
        } catch (ValidationException $e) {
            throw new InputException('Could not insert data into model ' . $modelClass, 0, $e);
        }
    }

    private function checkIdExists(string $type, string $rawId): int
    {
        $id = (int) $rawId;

        if (empty($id)) {
            throw new InputException('Provided id is not an integer ' . $rawId);
        }

        $data = $this->get($type, $id);
        return (int) $data['id'];
    }

    private function request(string $method, string $path, ?array $query = null, $body = null)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->authenticator->getAccessToken()
        ];

        return $this->transport->request($this->authenticator->getBaseUri(), $method, 'backend/' . $path, $query, $headers, $body);
    }
}
