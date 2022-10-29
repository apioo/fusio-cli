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
use Fusio\Cli\Exception\TokenException;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Transport\ResponseParser;
use Fusio\Cli\Transport\TransportInterface;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\Exception\ValidationException;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaTraverser;
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
    private Authenticator $authenticator;
    private TransportInterface $transport;
    private SchemaManagerInterface $schemaManager;
    private SchemaTraverser $schemaTraverser;

    public function __construct(Authenticator $authenticator, TransportInterface $transport)
    {
        $this->authenticator = $authenticator;
        $this->transport = $transport;
        $this->schemaManager = new SchemaManager();
        $this->schemaTraverser = new SchemaTraverser();
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function getAll(string $type, ?int $startIndex = null, ?int $count = null, ?string $search = null, ?string $sortBy = null, ?int $sortOrder = null): array
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

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function get(string $type, string $id): array
    {
        $actualId = (int) $id;
        if ($actualId === 0) {
            return $this->getByName($type, $id);
        } else {
            return $this->getById($type, $actualId);
        }
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function getById(string $type, int $id): array
    {
        $response = $this->request('GET', $type . '/' . $id);

        return ResponseParser::parse($response);
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function getByName(string $type, string $name): array
    {
        $response = $this->request('GET', $type . '/~' . urlencode($name));

        return ResponseParser::parse($response);
    }

    /**
     * @throws InputException
     * @throws TokenException
     * @throws TransportException
     */
    public function create(string $type, string $payload, string $modelClass): array
    {
        $body     = $this->parsePayload($payload, $modelClass);
        $response = $this->request('POST', $type, null, $body);

        return ResponseParser::parse($response);
    }

    /**
     * @throws InputException
     * @throws TokenException
     * @throws TransportException
     */
    public function update(string $type, int $id, string $payload, string $modelClass): array
    {
        $body     = $this->parsePayload($payload, $modelClass);
        $response = $this->request('PUT', $type . '/' . $id, null, $body);

        return ResponseParser::parse($response);
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function delete(string $type, int $id): array
    {
        $response = $this->request('DELETE', $type . '/' . $id);

        return ResponseParser::parse($response);
    }

    /**
     * @throws InputException
     */
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
        } catch (InvalidSchemaException $e) {
            throw new InputException('Provided an invalid schema ' . $modelClass . ', got: ' . $e->getMessage(), 0, $e);
        } catch (ValidationException $e) {
            throw new InputException('Could not insert data into model ' . $modelClass . ', got: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws TokenException
     */
    private function request(string $method, string $path, ?array $query = null, mixed $body = null): HttpResponseInterface
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->authenticator->getAccessToken()
        ];

        return $this->transport->request($this->authenticator->getBaseUri(), $method, 'backend/' . $path, $query, $headers, $body);
    }
}
