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

namespace Fusio\Cli\Service;

use Fusio\Cli\Exception\InputException;
use Fusio\Cli\Exception\TokenException;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Transport\ResponseParser;
use Fusio\Cli\Transport\TransportInterface;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Json\Parser;
use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\Exception\MappingException;
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use Symfony\Component\Yaml\Yaml;

/**
 * Client
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Client
{
    private AuthenticatorInterface $authenticator;
    private TransportInterface $transport;
    private ObjectMapper $objectMapper;

    public function __construct(AuthenticatorInterface $authenticator, TransportInterface $transport)
    {
        $this->authenticator = $authenticator;
        $this->transport = $transport;
        $this->objectMapper = new ObjectMapper(new SchemaManager());
    }

    public function setAuthenticator(AuthenticatorInterface $authenticator): void
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function getAll(string $type, ?int $startIndex = null, ?int $count = null, ?string $search = null, ?string $sortBy = null, ?int $sortOrder = null): object
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
    public function get(string $type, string $id): object
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
    public function getById(string $type, int $id): object
    {
        $response = $this->request('GET', $type . '/' . $id);

        return ResponseParser::parse($response);
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function getByName(string $type, string $name): object
    {
        $response = $this->request('GET', $type . '/~' . urlencode($name));

        return ResponseParser::parse($response);
    }

    /**
     * @throws TransportException
     * @throws TokenException
     */
    public function getVersion(): ?string
    {
        $response = $this->transport->request($this->authenticator->getBaseUri(), 'GET', 'system/about');
        $data = ResponseParser::parse($response);
        $version = $data->apiVersion ?? null;

        return is_string($version) ? $version : null;
    }

    /**
     * @throws TokenException
     * @throws TransportException
     * @throws InputException
     */
    public function create(string $type, string $payload, string $modelClass): object
    {
        $body     = $this->parsePayload($payload, $modelClass);
        $response = $this->request('POST', $type, null, $body);

        return ResponseParser::parse($response);
    }

    /**
     * @throws TokenException
     * @throws TransportException
     * @throws InputException
     */
    public function update(string $type, int $id, string $payload, string $modelClass): object
    {
        $body     = $this->parsePayload($payload, $modelClass);
        $response = $this->request('PUT', $type . '/' . $id, null, $body);

        return ResponseParser::parse($response);
    }

    /**
     * @throws TransportException
     * @throws TokenException
     */
    public function delete(string $type, int $id): object
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
            $payload = (string) file_get_contents($payload);
        } elseif ($payload === '-') {
            $payload = $this->readStdin() ?? throw new InputException('Could not read from stdin');
        }

        try {
            $data = Parser::decode($payload);
        } catch (\JsonException) {
            try {
                // try parse as yaml
                $data = Parser::decode(Parser::encode(Yaml::parse($payload)));
            } catch (\JsonException $e) {
                throw new InputException('Could not parse provided payload, got: ' . $e->getMessage(), previous: $e);
            }
        }

        if (!$data instanceof \stdClass) {
            throw new InputException('Could not parse provided payload, must be either a YAML or JSON object');
        }

        try {
            return $this->objectMapper->read($data, SchemaSource::fromClass($modelClass));
        } catch (MappingException|InvalidSchemaException $e) {
            throw new InputException('Provided an invalid payload for schema ' . $modelClass . ', got: ' . $e->getMessage(), 0, $e);
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

    private function readStdin(): ?string
    {
        $read = [STDIN];
        $write = $except = null;
        $changed = stream_select($read, $write, $except, 8);
        if ($changed === false || $changed === 0) {
            return null;
        }

        $return = stream_get_contents(STDIN);
        if ($return === false) {
            return null;
        }

        return $return;
    }
}
